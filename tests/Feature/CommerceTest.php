<?php

use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\OrderService;

beforeEach(function () {
    config()->set('commerce.courier_delivery_amount', '0.00');
});

function commerceProduct(int $stock = 3): Product
{
    $category = ProductCategory::create(['name' => 'Тест', 'code' => 'test-'.uniqid()]);
    $product = Product::create(['name' => 'Товар', 'price' => '125.50', 'category_id' => $category->id]);
    app(InventoryService::class)->adjust($product->id, $stock);

    return $product;
}

function courierOrderData(Product $product, int $quantity = 2, string $paymentMethod = 'on_delivery'): array
{
    return [
        'customer_name' => 'Иван Иванов',
        'customer_email' => 'ivan@example.test',
        'customer_phone' => '+79990000000',
        'items' => [['product_id' => $product->id, 'quantity' => $quantity]],
        'payment_method' => $paymentMethod,
        'delivery' => [
            'method' => 'courier', 'recipient_name' => 'Иван Иванов',
            'recipient_phone' => '+79990000000', 'city' => 'Москва',
            'street' => 'Лесная', 'house' => '10',
        ],
    ];
}

it('creates a guest order from server prices and reserves stock', function () {
    config()->set('commerce.courier_delivery_amount', '99.00');
    $product = commerceProduct();
    $response = $this->postJson('/shop/orders', courierOrderData($product), ['Idempotency-Key' => (string) \Illuminate\Support\Str::uuid()]);

    $response->assertCreated()->assertJsonPath('total', '350.00');
    $order = Order::firstOrFail();
    expect($order->user_id)->toBeNull()
        ->and($order->status)->toBe('confirmed')
        ->and($order->items->first()->unit_price)->toBe('125.50')
        ->and($order->items->first()->quantity)->toBe(2)
        ->and($order->delivery->city)->toBe('Москва');
    expect(InventoryBalance::find($product->id)->reserved)->toBe(2);

    $product->update(['name' => 'Новое название', 'price' => '999.00']);
    expect($order->items->first()->fresh()->name)->toBe('Товар');
    expect($order->items->first()->fresh()->unit_price)->toBe('125.50');
});

it('rejects quantities above available stock without a partial order', function () {
    $product = commerceProduct(1);
    $this->postJson('/shop/orders', courierOrderData($product, 2), ['Idempotency-Key' => (string) \Illuminate\Support\Str::uuid()])->assertUnprocessable();
    expect(Order::count())->toBe(0)
        ->and(InventoryBalance::find($product->id)->reserved)->toBe(0);
});

it('supports pickup for an authenticated buyer and preserves the point address', function () {
    $product = commerceProduct();
    $point = PickupPoint::create(['code' => 'center', 'name' => 'Центр', 'address' => 'Москва, Центр, 1']);
    $user = User::factory()->create();
    $payload = courierOrderData($product, 1);
    $payload['delivery'] = [
        'method' => 'pickup', 'recipient_name' => 'Иван Иванов',
        'recipient_phone' => '+79990000000', 'pickup_point_id' => $point->id,
    ];
    $this->actingAs($user)->postJson('/shop/orders', $payload, ['Idempotency-Key' => (string) \Illuminate\Support\Str::uuid()])->assertCreated();
    $order = Order::firstOrFail();
    $point->update(['address' => 'Новый адрес']);
    expect($order->user_id)->toBe($user->id)
        ->and($order->delivery->address_snapshot)->toBe('Москва, Центр, 1');
});

it('releases an expired online reservation and ignores a repeated webhook', function () {
    config()->set('commerce.online_enabled', true);
    $product = commerceProduct(1);
    $payload = courierOrderData($product, 1, 'online');
    $this->postJson('/shop/orders', $payload, ['Idempotency-Key' => (string) \Illuminate\Support\Str::uuid()])->assertCreated();
    $order = Order::firstOrFail();
    $payment = $order->payments()->firstOrFail();
    $order->update(['expires_at' => now()->subMinute()]);

    expect(app(OrderService::class)->expireUnpaid())->toBe(1);
    expect($order->fresh()->status)->toBe('cancelled')
        ->and(InventoryBalance::find($product->id)->reserved)->toBe(0);

    app(OrderService::class)->recordOnlinePayment($payment, 'provider', 'payment-1', 'event-1', 'paid', true, $payment->amount, 'RUB');
    app(OrderService::class)->recordOnlinePayment($payment, 'provider', 'payment-1', 'event-1', 'paid', true, $payment->amount, 'RUB');
    expect($order->fresh()->status)->toBe('cancelled')
        ->and($payment->fresh()->refunds()->count())->toBe(1);
});

it('completes a courier order only after delivery and payment', function () {
    $product = commerceProduct();
    $order = app(OrderService::class)->place(courierOrderData($product, 1));
    $service = app(OrderService::class);
    $service->updateDelivery($order, 'preparing');
    $service->updateDelivery($order, 'in_transit');
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(2);
    $service->updateDelivery($order, 'delivered');
    expect($order->fresh()->status)->toBe('processing');
    $service->markPaymentAtDelivery($order);
    expect($order->fresh()->status)->toBe('completed');
});

it('does not duplicate an order when checkout is retried with the same key', function () {
    $product = commerceProduct(2);
    $payload = courierOrderData($product, 1);
    $headers = ['Idempotency-Key' => (string) \Illuminate\Support\Str::uuid()];
    $first = $this->postJson('/shop/orders', $payload, $headers)->assertCreated();
    $second = $this->postJson('/shop/orders', $payload, $headers)->assertCreated();
    expect($second->json('number'))->toBe($first->json('number'))
        ->and(Order::count())->toBe(1)
        ->and(InventoryBalance::find($product->id)->reserved)->toBe(1);

    $this->postJson('/shop/orders', courierOrderData($product, 2), $headers)->assertUnprocessable();
});

it('restocks only after a failed courier delivery is physically returned', function () {
    $product = commerceProduct(1);
    $order = app(OrderService::class)->place(courierOrderData($product, 1));
    $orders = app(OrderService::class);
    $orders->updateDelivery($order, 'preparing');
    $orders->updateDelivery($order, 'in_transit');
    $orders->updateDelivery($order, 'failed');
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(0);

    $orders->returnFailedDelivery($order);
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(1)
        ->and($order->fresh()->status)->toBe('cancelled')
        ->and($order->delivery->fresh()->status)->toBe('returned');
});

it('requires an admin account for dashboard orders', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)->get('/dashboard/orders')->assertForbidden();
    $customer->forceFill(['is_admin' => true])->save();
    $this->actingAs($customer)->get('/dashboard/orders')->assertOk();
    $this->actingAs($customer)->get('/dashboard/pickup-points')->assertOk();

    $product = commerceProduct();
    $order = app(OrderService::class)->place(courierOrderData($product, 1));
    $this->actingAs($customer)->get('/dashboard/orders/'.$order->id)->assertOk();
    $this->actingAs($customer)->get('/dashboard/products/'.$product->id.'/edit')->assertOk();
});

it('rejects an online callback with a mismatched amount', function () {
    config()->set('commerce.online_enabled', true);
    $product = commerceProduct(1);
    $order = app(OrderService::class)->place(courierOrderData($product, 1, 'online'));
    $payment = $order->payments()->firstOrFail();

    expect(fn () => app(OrderService::class)->recordOnlinePayment(
        $payment, 'provider', 'payment-2', 'event-2', 'paid', true, '1.00', 'RUB'
    ))->toThrow(\Illuminate\Validation\ValidationException::class);
    expect($order->fresh()->status)->toBe('awaiting_payment')
        ->and($payment->fresh()->status)->toBe('pending');
});

it('does not allow courier checkout before a delivery price is configured', function () {
    config()->set('commerce.courier_delivery_amount', null);
    $product = commerceProduct();
    $this->postJson('/shop/orders', courierOrderData($product), [
        'Idempotency-Key' => (string) \Illuminate\Support\Str::uuid(),
    ])->assertUnprocessable();
    expect(Order::count())->toBe(0);
});
