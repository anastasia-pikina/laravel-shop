<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\PaymentWebhookEvent;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function place(array $data, ?int $userId = null): Order
    {
        $checkoutKey = $data['checkout_key'] ?? (string) Str::uuid();
        $fingerprintData = $data;
        unset($fingerprintData['checkout_key']);
        ksort($fingerprintData);
        ksort($fingerprintData['delivery']);
        foreach ($fingerprintData['items'] as &$item) {
            ksort($item);
        }
        unset($item);
        usort($fingerprintData['items'], fn ($a, $b) => $a['product_id'] <=> $b['product_id']);
        $fingerprint = hash('sha256', json_encode([$userId, $fingerprintData], JSON_THROW_ON_ERROR));
        if ($existing = Order::query()->where('checkout_key', $checkoutKey)->first()) {
            return $this->matchingCheckout($existing, $fingerprint);
        }

        try {
            return DB::transaction(function () use ($data, $userId, $checkoutKey, $fingerprint) {
            $requested = collect($data['items'])->keyBy('product_id')->sortKeys();
            $ids = $requested->keys()->all();
            $products = Product::query()->whereIn('id', $ids)->get()->keyBy('id');
            $balances = InventoryBalance::query()->whereIn('product_id', $ids)
                ->orderBy('product_id')->lockForUpdate()->get()->keyBy('product_id');
            $subtotal = 0;
            foreach ($requested as $id => $line) {
                $product = $products->get($id);
                $balance = $balances->get($id);
                if (! $product || ! $product->is_active || ! $balance || $balance->on_hand - $balance->reserved < $line['quantity']) {
                    throw ValidationException::withMessages(['items' => 'Товар недоступен в указанном количестве.']);
                }
                $lineAmount = Money::toKopeks($product->price) * $line['quantity'];
                if ($lineAmount > Money::MAX_KOPEKS || $subtotal + $lineAmount > Money::MAX_KOPEKS) {
                    throw ValidationException::withMessages(['items' => 'Сумма заказа слишком велика.']);
                }
                $subtotal += $lineAmount;
            }

            $delivery = $data['delivery'];
            $pickupPoint = null;
            if ($delivery['method'] === 'pickup') {
                $pickupPoint = PickupPoint::query()->whereKey($delivery['pickup_point_id'])->where('is_active', true)->first();
                if (! $pickupPoint) {
                    throw ValidationException::withMessages(['delivery.pickup_point_id' => 'Точка самовывоза недоступна.']);
                }
            }
            $configuredAmount = config('commerce.'.$delivery['method'].'_delivery_amount');
            if ($configuredAmount === null || $configuredAmount === '') {
                throw ValidationException::withMessages(['delivery.method' => 'Стоимость доставки не настроена.']);
            }
            $deliveryAmount = Money::toKopeks((string) $configuredAmount);
            if ($subtotal + $deliveryAmount > Money::MAX_KOPEKS) {
                throw ValidationException::withMessages(['delivery.method' => 'Сумма заказа слишком велика.']);
            }
            $online = $data['payment_method'] === 'online';
            $expiresAt = $online ? now()->addMinutes(config('commerce.online_reservation_minutes')) : null;
            $order = Order::create([
                'number' => 'ORD-'.Str::ulid(),
                'checkout_key' => $checkoutKey,
                'checkout_fingerprint' => $fingerprint,
                'user_id' => $userId,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'status' => $online ? 'awaiting_payment' : 'confirmed',
                'payment_method' => $data['payment_method'],
                'currency' => 'RUB',
                'subtotal' => Money::fromKopeks($subtotal),
                'delivery_amount' => Money::fromKopeks($deliveryAmount),
                'total' => Money::fromKopeks($subtotal + $deliveryAmount),
                'placed_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            foreach ($requested as $id => $line) {
                $product = $products->get($id);
                $item = $order->items()->create([
                    'product_id' => $id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $line['quantity'],
                    'total' => Money::fromKopeks(Money::toKopeks($product->price) * $line['quantity']),
                ]);
                $balances->get($id)->increment('reserved', $line['quantity']);
                $item->reservation()->create([
                    'quantity' => $line['quantity'],
                    'status' => 'active',
                    'expires_at' => $expiresAt,
                ]);
            }

            $order->delivery()->create([
                'method' => $delivery['method'],
                'status' => 'pending',
                'recipient_name' => $delivery['recipient_name'],
                'recipient_phone' => $delivery['recipient_phone'],
                'pickup_point_id' => $pickupPoint?->id,
                'address_snapshot' => $pickupPoint?->address,
                'postal_code' => $delivery['method'] === 'courier' ? ($delivery['postal_code'] ?? null) : null,
                'region' => $delivery['method'] === 'courier' ? ($delivery['region'] ?? null) : null,
                'city' => $delivery['method'] === 'courier' ? $delivery['city'] : null,
                'street' => $delivery['method'] === 'courier' ? $delivery['street'] : null,
                'house' => $delivery['method'] === 'courier' ? $delivery['house'] : null,
                'apartment' => $delivery['method'] === 'courier' ? ($delivery['apartment'] ?? null) : null,
                'amount' => Money::fromKopeks($deliveryAmount),
            ]);
            $order->payments()->create([
                'method' => $data['payment_method'],
                'idempotency_key' => (string) Str::uuid(),
                'status' => 'pending',
                'amount' => $order->total,
                'currency' => 'RUB',
            ]);

            return $order->load('items', 'delivery', 'payments');
            }, 3);
        } catch (QueryException $exception) {
            if ($existing = Order::query()->where('checkout_key', $checkoutKey)->first()) {
                return $this->matchingCheckout($existing, $fingerprint);
            }
            throw $exception;
        }
    }

    public function retryOnlinePayment(Order $order): Payment
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->payment_method !== 'online' || $order->status !== 'awaiting_payment' || $order->expires_at->isPast()) {
                throw ValidationException::withMessages(['order' => 'Повторная оплата недоступна.']);
            }
            if ($order->payments()->where('status', 'succeeded')->exists()) {
                throw ValidationException::withMessages(['order' => 'Заказ уже оплачен.']);
            }
            return $order->payments()->create([
                'method' => 'online', 'idempotency_key' => (string) Str::uuid(),
                'status' => 'pending', 'amount' => $order->total, 'currency' => 'RUB',
            ]);
        });
    }

    // The payment adapter must authenticate and verify the provider's webhook before calling this method.
    public function recordOnlinePayment(
        Payment $payment,
        string $provider,
        string $providerPaymentId,
        string $eventId,
        string $eventType,
        bool $succeeded,
        string $amount,
        string $currency,
    ): void
    {
        DB::transaction(function () use ($payment, $provider, $providerPaymentId, $eventId, $eventType, $succeeded, $amount, $currency) {
            $inserted = PaymentWebhookEvent::query()->insertOrIgnore([
                'payment_id' => $payment->id, 'provider' => $provider,
                'provider_event_id' => $eventId, 'event_type' => $eventType,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            if (! $inserted) {
                return;
            }
            $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->method !== 'online' || $payment->order_id !== $order->id) {
                throw ValidationException::withMessages(['payment' => 'Некорректный платеж.']);
            }
            if ($currency !== $payment->currency || Money::toKopeks($amount) !== Money::toKopeks($payment->amount)
                || ($payment->provider_payment_id && $payment->provider_payment_id !== $providerPaymentId)) {
                throw ValidationException::withMessages(['payment' => 'Сумма, валюта или ID платежа не совпадают.']);
            }
            $payment->update(['provider' => $provider, 'provider_payment_id' => $providerPaymentId]);
            if ($succeeded && $payment->status !== 'succeeded') {
                if ($order->payments()->where('status', 'succeeded')->exists()) {
                    // A second successful provider charge must be refunded, not applied twice.
                    $payment->update(['status' => 'succeeded', 'paid_at' => now()]);
                    $this->queueRefund($payment, 'duplicate_payment');
                } else {
                    $payment->update(['status' => 'succeeded', 'paid_at' => now()]);
                    if ($order->status === 'awaiting_payment' && $order->expires_at->isFuture()) {
                        $order->update(['status' => 'confirmed']);
                    } else {
                        if ($order->status === 'awaiting_payment') {
                            $this->cancelLocked($order);
                        }
                        $this->queueRefund($payment, 'late_payment');
                    }
                }
            } elseif (! $succeeded && $payment->status === 'pending') {
                $payment->update(['status' => 'failed']);
            }
            PaymentWebhookEvent::query()->where('provider', $provider)->where('provider_event_id', $eventId)
                ->update(['processed_at' => now()]);
        }, 3);
    }

    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! in_array($locked->status, ['awaiting_payment', 'confirmed', 'processing'], true)
                || $locked->delivery->handed_over_at) {
                throw ValidationException::withMessages(['order' => 'Отмена заказа недоступна.']);
            }
            $this->cancelLocked($locked);
        }, 3);
    }

    public function expireUnpaid(): int
    {
        $count = 0;
        foreach (Order::query()->where('status', 'awaiting_payment')->where('expires_at', '<=', now())->pluck('id') as $id) {
            DB::transaction(function () use ($id, &$count) {
                $order = Order::query()->whereKey($id)->lockForUpdate()->first();
                if ($order && $order->status === 'awaiting_payment' && $order->expires_at->isPast()) {
                    $this->cancelLocked($order);
                    $count++;
                }
            }, 3);
        }
        return $count;
    }

    public function updateDelivery(Order $order, string $status): void
    {
        DB::transaction(function () use ($order, $status) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $delivery = $order->delivery;
            if (! in_array($order->status, ['confirmed', 'processing'], true)) {
                throw ValidationException::withMessages(['order' => 'Заказ не готов к доставке.']);
            }
            $allowed = $delivery->method === 'pickup'
                ? ['pending' => ['preparing'], 'preparing' => ['ready_for_pickup'], 'ready_for_pickup' => ['delivered']]
                : ['pending' => ['preparing'], 'preparing' => ['in_transit'], 'in_transit' => ['delivered', 'failed']];
            if (! in_array($status, $allowed[$delivery->status] ?? [], true)) {
                throw ValidationException::withMessages(['delivery' => 'Недопустимый переход статуса доставки.']);
            }
            if ($status === 'in_transit' || ($delivery->method === 'pickup' && $status === 'delivered')) {
                $this->consumeReservations($order);
            }
            $delivery->update([
                'status' => $status,
                'handed_over_at' => in_array($status, ['in_transit', 'delivered'], true) && ! $delivery->handed_over_at ? now() : $delivery->handed_over_at,
                'delivered_at' => $status === 'delivered' ? now() : null,
            ]);
            if ($order->status === 'confirmed') {
                $order->update(['status' => 'processing']);
            }
            $this->completeIfReady($order);
        }, 3);
    }

    public function markPaymentAtDelivery(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->payment_method !== 'on_delivery' || $order->delivery->status !== 'delivered'
                || ! in_array($order->status, ['confirmed', 'processing'], true)) {
                throw ValidationException::withMessages(['payment' => 'Оплата при получении недоступна.']);
            }
            $payment = $order->payments()->lockForUpdate()->firstOrFail();
            if ($payment->status === 'pending') {
                $payment->update(['status' => 'succeeded', 'paid_at' => now()]);
            }
            $this->completeIfReady($order);
        }, 3);
    }

    public function returnFailedDelivery(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->delivery->method !== 'courier' || $order->delivery->status !== 'failed') {
                throw ValidationException::withMessages(['delivery' => 'Возврат недоступен.']);
            }
            foreach ($order->items()->with('reservation')->orderBy('product_id')->get() as $item) {
                $this->inventory->returnSale($item->reservation);
            }
            $order->delivery->update(['status' => 'returned']);
            $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            foreach ($order->payments()->where('status', 'succeeded')->get() as $payment) {
                $this->queueRefund($payment, 'returned_delivery');
            }
        }, 3);
    }

    private function cancelLocked(Order $order): void
    {
        foreach ($order->items()->with('reservation')->orderBy('product_id')->get() as $item) {
            $this->inventory->release($item->reservation);
        }
        $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        foreach ($order->payments()->where('status', 'succeeded')->get() as $payment) {
            $this->queueRefund($payment, 'order_cancelled');
        }
        $order->delivery->update(['status' => 'cancelled']);
    }

    private function consumeReservations(Order $order): void
    {
        foreach ($order->items()->with('reservation')->orderBy('product_id')->get() as $item) {
            $this->inventory->consume($item->reservation);
        }
    }

    private function completeIfReady(Order $order): void
    {
        if ($order->delivery->fresh()->status === 'delivered' && $order->payments()->where('status', 'succeeded')->exists()) {
            $order->update(['status' => 'completed', 'completed_at' => now()]);
        }
    }

    private function queueRefund(Payment $payment, string $reason): void
    {
        if (! $payment->refunds()->exists()) {
            $payment->refunds()->create([
                'provider' => $payment->provider,
                'status' => 'pending',
                'amount' => $payment->amount,
                'reason' => $reason,
            ]);
        }
    }

    private function matchingCheckout(Order $order, string $fingerprint): Order
    {
        if (! hash_equals($order->checkout_fingerprint, $fingerprint)) {
            throw ValidationException::withMessages(['checkout_key' => 'Ключ оформления уже использован для другого заказа.']);
        }

        return $order->load('items', 'delivery', 'payments');
    }
}
