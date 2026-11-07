<?php

namespace App\Services;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\ReservationStatus;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Support\Money;
use App\Support\EnumCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use ValueError;

/**
 * Manage order placement, payment, delivery, and cancellation.
 */
class OrderService
{
    /**
     * @param InventoryService $inventory
     */
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @param array $data
     * @param ?int $userId
     *
     * @return Order
     * @throws QueryException
     * @throws ValidationException
     * @throws ValueError
     */
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
            $method = DeliveryMethod::from($delivery['method']);
            $pickupPoint = null;
            if ($method->isPickup()) {
                $pickupPoint = PickupPoint::query()->whereKey($delivery['pickup_point_id'])->where('is_active', true)->first();
                if (! $pickupPoint) {
                    throw ValidationException::withMessages(['delivery.pickup_point_id' => 'Точка самовывоза недоступна.']);
                }
            }
            $configuredAmount = config('commerce.'.$method->value.'_delivery_amount');
            if ($configuredAmount === null || $configuredAmount === '') {
                throw ValidationException::withMessages(['delivery.method' => 'Стоимость доставки не настроена.']);
            }
            $deliveryAmount = Money::toKopeks((string) $configuredAmount);
            if ($subtotal + $deliveryAmount > Money::MAX_KOPEKS) {
                throw ValidationException::withMessages(['delivery.method' => 'Сумма заказа слишком велика.']);
            }
            $paymentMethod = PaymentMethod::from($data['payment_method']);
            $online = $paymentMethod === PaymentMethod::Online;
            $expiresAt = $online ? now()->addMinutes(config('commerce.online_reservation_minutes')) : null;
            $order = Order::create([
                'number' => 'ORD-'.Str::ulid(),
                'checkout_key' => $checkoutKey,
                'checkout_fingerprint' => $fingerprint,
                'user_id' => $userId,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'status' => $online ? OrderStatus::AwaitingPayment : OrderStatus::Confirmed,
                'payment_method' => $paymentMethod,
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
                    'status' => ReservationStatus::Active,
                    'expires_at' => $expiresAt,
                ]);
            }

            $order->delivery()->create([
                'method' => $method,
                'status' => DeliveryStatus::Pending,
                'recipient_name' => $delivery['recipient_name'],
                'recipient_phone' => $delivery['recipient_phone'],
                'pickup_point_id' => $pickupPoint?->id,
                'address_snapshot' => $pickupPoint?->address,
                'postal_code' => $method === DeliveryMethod::Courier ? ($delivery['postal_code'] ?? null) : null,
                'region' => $method === DeliveryMethod::Courier ? ($delivery['region'] ?? null) : null,
                'city' => $method === DeliveryMethod::Courier ? $delivery['city'] : null,
                'street' => $method === DeliveryMethod::Courier ? $delivery['street'] : null,
                'house' => $method === DeliveryMethod::Courier ? $delivery['house'] : null,
                'apartment' => $method === DeliveryMethod::Courier ? ($delivery['apartment'] ?? null) : null,
                'amount' => Money::fromKopeks($deliveryAmount),
            ]);
            $order->payments()->create([
                'method' => $paymentMethod,
                'idempotency_key' => (string) Str::uuid(),
                'status' => PaymentStatus::Pending,
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

    /**
     * @param Order $order
     *
     * @return Payment
     * @throws ValidationException
     */
    public function retryOnlinePayment(Order $order): Payment
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->payment_method !== PaymentMethod::Online || $order->status !== OrderStatus::AwaitingPayment || $order->expires_at->isPast()) {
                throw ValidationException::withMessages(['order' => 'Повторная оплата недоступна.']);
            }
            if ($order->payments()->where('status_id', app(EnumCatalog::class)->id(PaymentStatus::Succeeded))->exists()) {
                throw ValidationException::withMessages(['order' => 'Заказ уже оплачен.']);
            }
            return $order->payments()->create([
                'method' => PaymentMethod::Online, 'idempotency_key' => (string) Str::uuid(),
                'status' => PaymentStatus::Pending, 'amount' => $order->total, 'currency' => 'RUB',
            ]);
        });
    }

    /**
     * The payment adapter must authenticate and verify the provider's webhook before calling this method.
     *
     * @param Payment $payment
     * @param string $provider
     * @param string $providerPaymentId
     * @param string $eventId
     * @param string $eventType
     * @param bool $succeeded
     * @param string $amount
     * @param string $currency
     *
     * @return void
     * @throws ValidationException
     */
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
            if ($payment->method !== PaymentMethod::Online || $payment->order_id !== $order->id) {
                throw ValidationException::withMessages(['payment' => 'Некорректный платеж.']);
            }
            if ($currency !== $payment->currency || Money::toKopeks($amount) !== Money::toKopeks($payment->amount)
                || ($payment->provider_payment_id && $payment->provider_payment_id !== $providerPaymentId)) {
                throw ValidationException::withMessages(['payment' => 'Сумма, валюта или ID платежа не совпадают.']);
            }
            $payment->update(['provider' => $provider, 'provider_payment_id' => $providerPaymentId]);
            if ($succeeded && $payment->status !== PaymentStatus::Succeeded) {
                if ($order->payments()->where('status_id', app(EnumCatalog::class)->id(PaymentStatus::Succeeded))->exists()) {
                    // A second successful provider charge must be refunded, not applied twice.
                    $payment->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);
                    $this->queueRefund($payment, 'duplicate_payment');
                } else {
                    $payment->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);
                    if ($order->status === OrderStatus::AwaitingPayment && $order->expires_at->isFuture()) {
                        $order->update(['status' => OrderStatus::Confirmed]);
                    } else {
                        if ($order->status === OrderStatus::AwaitingPayment) {
                            $this->cancelLocked($order);
                        }
                        $this->queueRefund($payment, 'late_payment');
                    }
                }
            } elseif (! $succeeded && $payment->status === PaymentStatus::Pending) {
                $payment->update(['status' => PaymentStatus::Failed]);
            }
            PaymentWebhookEvent::query()->where('provider', $provider)->where('provider_event_id', $eventId)
                ->update(['processed_at' => now()]);
        }, 3);
    }

    /**
     * @param Order $order
     *
     * @return void
     * @throws ValidationException
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! $locked->status->allowsCancellation()
                || $locked->delivery->handed_over_at) {
                throw ValidationException::withMessages(['order' => 'Отмена заказа недоступна.']);
            }
            $this->cancelLocked($locked);
        }, 3);
    }

    /**
     * @return int
     */
    public function expireUnpaid(): int
    {
        $count = 0;
        foreach (Order::query()->where('status_id', app(EnumCatalog::class)->id(OrderStatus::AwaitingPayment))->where('expires_at', '<=', now())->pluck('id') as $id) {
            DB::transaction(function () use ($id, &$count) {
                $order = Order::query()->whereKey($id)->lockForUpdate()->first();
                if ($order && $order->status === OrderStatus::AwaitingPayment && $order->expires_at->isPast()) {
                    $this->cancelLocked($order);
                    $count++;
                }
            }, 3);
        }
        return $count;
    }

    /**
     * @param Order $order
     * @param DeliveryStatus $status
     *
     * @return void
     * @throws ValidationException
     */
    public function updateDelivery(Order $order, DeliveryStatus $status): void
    {
        DB::transaction(function () use ($order, $status) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $delivery = $order->delivery;

            if (!$order->status->allowsDeliveryUpdate()) {
                throw ValidationException::withMessages(['order' => 'Заказ не готов к доставке.']);
            }

            if (! $delivery->status->canTransitionTo($status, $delivery->method)) {
                throw ValidationException::withMessages(['delivery' => 'Недопустимый переход статуса доставки.']);
            }

            if (
                $status === DeliveryStatus::InTransit
                || ($delivery->method->isPickup() && $status === DeliveryStatus::Delivered)
            ) {
                $this->consumeReservations($order);
            }

            $delivery->update([
                'status' => $status,
                'handed_over_at' => in_array(
                    $status,
                    [DeliveryStatus::InTransit, DeliveryStatus::Delivered],
                    true
                ) && !$delivery->handed_over_at ? now() : $delivery->handed_over_at,
                'delivered_at' => $status === DeliveryStatus::Delivered ? now() : null,
            ]);

            if ($order->status === OrderStatus::Confirmed) {
                $order->update(['status' => OrderStatus::Processing]);
            }

            $this->completeIfReady($order);
        }, 3);
    }

    /**
     * @param Order $order
     *
     * @return void
     * @throws ValidationException
     */
    public function markPaymentAtDelivery(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->payment_method !== PaymentMethod::OnDelivery || $order->delivery->status !== DeliveryStatus::Delivered
                || ! $order->status->allowsDeliveryUpdate()) {
                throw ValidationException::withMessages(['payment' => 'Оплата при получении недоступна.']);
            }
            $payment = $order->payments()->lockForUpdate()->firstOrFail();
            if ($payment->status === PaymentStatus::Pending) {
                $payment->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);
            }
            $this->completeIfReady($order);
        }, 3);
    }

    /**
     * @param Order $order
     *
     * @return void
     * @throws ValidationException
     */
    public function returnFailedDelivery(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->delivery->method !== DeliveryMethod::Courier || $order->delivery->status !== DeliveryStatus::Failed) {
                throw ValidationException::withMessages(['delivery' => 'Возврат недоступен.']);
            }
            foreach ($order->items()->with('reservation')->orderBy('product_id')->get() as $item) {
                $this->inventory->returnSale($item->reservation);
            }
            $order->delivery->update(['status' => DeliveryStatus::Returned]);
            $order->update(['status' => OrderStatus::Cancelled, 'cancelled_at' => now()]);
            foreach ($order->payments()->where('status_id', app(EnumCatalog::class)->id(PaymentStatus::Succeeded))->get() as $payment) {
                $this->queueRefund($payment, 'returned_delivery');
            }
        }, 3);
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    private function cancelLocked(Order $order): void
    {
        foreach ($order->items()->with('reservation')->orderBy('product_id')->get() as $item) {
            $this->inventory->release($item->reservation);
        }
        $order->update(['status' => OrderStatus::Cancelled, 'cancelled_at' => now()]);
        foreach ($order->payments()->where('status_id', app(EnumCatalog::class)->id(PaymentStatus::Succeeded))->get() as $payment) {
            $this->queueRefund($payment, 'order_cancelled');
        }
        $order->delivery->update(['status' => DeliveryStatus::Cancelled]);
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    private function consumeReservations(Order $order): void
    {
        foreach ($order->items()->with('reservation')->orderBy('product_id')->get() as $item) {
            $this->inventory->consume($item->reservation);
        }
    }

    /**
     * @param Order $order
     *
     * @return void
     */
    private function completeIfReady(Order $order): void
    {
        if (
            $order->delivery->fresh()->status === DeliveryStatus::Delivered
            && $order->payments()->where('status_id', app(EnumCatalog::class)->id(PaymentStatus::Succeeded))->exists()
        ) {
            $order->update(['status' => OrderStatus::Completed, 'completed_at' => now()]);
        }
    }

    /**
     * @param Payment $payment
     * @param string $reason
     *
     * @return void
     */
    private function queueRefund(Payment $payment, string $reason): void
    {
        if (! $payment->refunds()->exists()) {
            $payment->refunds()->create([
                'provider' => $payment->provider,
                'status' => RefundStatus::Pending,
                'amount' => $payment->amount,
                'reason' => $reason,
            ]);
        }
    }

    /**
     * @param Order $order
     * @param string $fingerprint
     *
     * @return Order
     * @throws ValidationException
     */
    private function matchingCheckout(Order $order, string $fingerprint): Order
    {
        if (! hash_equals($order->checkout_fingerprint, $fingerprint)) {
            throw ValidationException::withMessages(['checkout_key' => 'Ключ оформления уже использован для другого заказа.']);
        }

        return $order->load('items', 'delivery', 'payments');
    }
}
