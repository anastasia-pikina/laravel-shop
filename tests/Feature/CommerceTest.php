<?php

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\ReservationStatus;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\OrderService;
use App\Support\EnumCatalog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
    $response = $this->postJson('/shop/orders', courierOrderData($product), ['Idempotency-Key' => (string) Str::uuid()]);

    $response->assertCreated()->assertJsonPath('total', '350.00')->assertJsonPath('status', 'confirmed')
        ->assertJsonPath('payment_method', 'on_delivery');
    $order = Order::firstOrFail();
    expect($order->user_id)->toBeNull()
        ->and($order->status)->toBe(OrderStatus::Confirmed)
        ->and($order->payment_method)->toBe(PaymentMethod::OnDelivery)
        ->and($order->payments->first()->method)->toBe(PaymentMethod::OnDelivery)
        ->and($order->items->first()->reservation->status)->toBe(ReservationStatus::Active)
        ->and($order->items->first()->unit_price)->toBe('125.50')
        ->and($order->items->first()->quantity)->toBe(2)
        ->and($order->delivery->fresh()->method)->toBe(DeliveryMethod::Courier)
        ->and($order->delivery->city)->toBe('Москва');
    expect(InventoryBalance::find($product->id)->reserved)->toBe(2);

    $product->update(['name' => 'Новое название', 'price' => '999.00']);
    expect($order->items->first()->fresh()->name)->toBe('Товар');
    expect($order->items->first()->fresh()->unit_price)->toBe('125.50');
});

it('rejects quantities above available stock without a partial order', function () {
    $product = commerceProduct(1);
    $this->postJson('/shop/orders', courierOrderData($product, 2), ['Idempotency-Key' => (string) Str::uuid()])->assertUnprocessable();
    expect(Order::count())->toBe(0)
        ->and(InventoryBalance::find($product->id)->reserved)->toBe(0);
});

it('rejects an unknown delivery method', function () {
    $product = commerceProduct();
    $payload = courierOrderData($product);
    $payload['delivery']['method'] = 'unknown';

    $this->postJson('/shop/orders', $payload, ['Idempotency-Key' => (string) Str::uuid()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('delivery.method');
    expect(Order::count())->toBe(0);
});

it('stores status IDs with foreign keys and keeps enum values for application logic', function () {
    $order = app(OrderService::class)->place(courierOrderData(commerceProduct(), 1));

    foreach ([
        'orders' => ['order_statuses', $order->id],
        'order_deliveries' => ['delivery_statuses', $order->delivery->id],
        'payments' => ['payment_statuses', $order->payments->first()->id],
        'stock_reservations' => ['reservation_statuses', $order->items->first()->reservation->id],
    ] as $table => [$catalog, $id]) {
        expect(Schema::hasColumn($table, 'status_id'))->toBeTrue()
            ->and(Schema::hasColumn($table, 'status'))->toBeFalse();
        $statusId = DB::table($table)->where('id', $id)->value('status_id');
        expect(DB::table($catalog)->where('id', $statusId)->exists())->toBeTrue();
    }
    expect(Schema::hasColumn('payment_refunds', 'status_id'))->toBeTrue()
        ->and(Schema::hasColumn('payment_refunds', 'status'))->toBeFalse()
        ->and($order->fresh()->status)->toBe(OrderStatus::Confirmed);

    expect(fn () => DB::table('orders')->where('id', $order->id)->update(['status_id' => 999999]))
        ->toThrow(QueryException::class);
    expect(Schema::hasColumn('order_deliveries', 'method_id'))->toBeTrue()
        ->and(Schema::hasColumn('order_deliveries', 'method'))->toBeFalse()
        ->and(DB::table('delivery_methods')->where('id', $order->delivery->method_id)->value('code'))->toBe('courier');
    expect(fn () => DB::table('order_deliveries')->where('id', $order->delivery->id)->update(['method_id' => 999999]))
        ->toThrow(QueryException::class);
    expect(Schema::hasColumn('orders', 'payment_method_id'))->toBeTrue()
        ->and(Schema::hasColumn('orders', 'payment_method'))->toBeFalse()
        ->and(Schema::hasColumn('payments', 'method_id'))->toBeTrue()
        ->and(Schema::hasColumn('payments', 'method'))->toBeFalse()
        ->and(DB::table('payment_methods')->where('id', $order->payment_method_id)->value('code'))->toBe('on_delivery')
        ->and(DB::table('payment_methods')->where('id', $order->payments->first()->method_id)->value('code'))->toBe('on_delivery');
    expect(fn () => DB::table('payments')->where('id', $order->payments->first()->id)->update(['method_id' => 999999]))
        ->toThrow(QueryException::class);
});

it('detects a catalog that differs from the application enum', function () {
    DB::table('order_statuses')->insert([
        'code' => 'unknown', 'name' => 'unknown', 'created_at' => now(), 'updated_at' => now(),
    ]);

    expect(fn () => (new EnumCatalog)->id(OrderStatus::Confirmed))->toThrow(LogicException::class);
});

it('provides Russian names for every commerce status and method', function () {
    foreach (['order_statuses', 'delivery_statuses', 'payment_statuses', 'refund_statuses', 'reservation_statuses', 'delivery_methods', 'payment_methods'] as $table) {
        foreach (DB::table($table)->get(['code', 'name']) as $status) {
            expect($status->name)->not->toBe($status->code)
                ->and(preg_match('/[А-Яа-яЁё]/u', $status->name))->toBe(1);
        }
    }
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
    $this->actingAs($user)->postJson('/shop/orders', $payload, ['Idempotency-Key' => (string) Str::uuid()])->assertCreated();
    $order = Order::firstOrFail();
    $point->update(['address' => 'Новый адрес']);
    expect($order->user_id)->toBe($user->id)
        ->and($order->delivery->fresh()->method)->toBe(DeliveryMethod::Pickup)
        ->and($order->delivery->methodName())->toBe('Самовывоз')
        ->and($order->delivery->address_snapshot)->toBe('Москва, Центр, 1');

    $orders = app(OrderService::class);
    expect(fn () => $orders->updateDelivery($order, DeliveryStatus::Delivered))
        ->toThrow(ValidationException::class);
    expect($order->delivery->fresh()->status)->toBe(DeliveryStatus::Pending);

    $orders->updateDelivery($order, DeliveryStatus::Preparing);
    $orders->updateDelivery($order, DeliveryStatus::ReadyForPickup);
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(3);
    $orders->updateDelivery($order, DeliveryStatus::Delivered);
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(2);
});

it('releases an expired online reservation and ignores a repeated webhook', function () {
    config()->set('commerce.online_enabled', true);
    $product = commerceProduct(1);
    $payload = courierOrderData($product, 1, 'online');
    $this->postJson('/shop/orders', $payload, ['Idempotency-Key' => (string) Str::uuid()])->assertCreated();
    $order = Order::firstOrFail();
    $payment = $order->payments()->firstOrFail();
    expect($order->payment_method)->toBe(PaymentMethod::Online)
        ->and($payment->method)->toBe(PaymentMethod::Online);
    $order->update(['expires_at' => now()->subMinute()]);

    expect(app(OrderService::class)->expireUnpaid())->toBe(1);
    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($order->items->first()->reservation->fresh()->status)->toBe(ReservationStatus::Released)
        ->and(InventoryBalance::find($product->id)->reserved)->toBe(0);

    app(OrderService::class)->recordOnlinePayment($payment, 'provider', 'payment-1', 'event-1', 'paid', true, $payment->amount, 'RUB');
    app(OrderService::class)->recordOnlinePayment($payment, 'provider', 'payment-1', 'event-1', 'paid', true, $payment->amount, 'RUB');
    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($payment->fresh()->refunds()->count())->toBe(1)
        ->and($payment->refunds()->firstOrFail()->status)->toBe(RefundStatus::Pending);
});

it('completes a courier order only after delivery and payment', function () {
    $product = commerceProduct();
    $order = app(OrderService::class)->place(courierOrderData($product, 1));
    $service = app(OrderService::class);
    $service->updateDelivery($order, DeliveryStatus::Preparing);
    $service->updateDelivery($order, DeliveryStatus::InTransit);
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(2);
    expect($order->items->first()->reservation->fresh()->status)->toBe(ReservationStatus::Consumed);
    $service->updateDelivery($order, DeliveryStatus::Delivered);
    expect($order->fresh()->status)->toBe(OrderStatus::Processing);
    $service->markPaymentAtDelivery($order);
    expect($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and($order->payments()->firstOrFail()->status)->toBe(PaymentStatus::Succeeded);
});

it('does not duplicate an order when checkout is retried with the same key', function () {
    $product = commerceProduct(2);
    $payload = courierOrderData($product, 1);
    $headers = ['Idempotency-Key' => (string) Str::uuid()];
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
    $orders->updateDelivery($order, DeliveryStatus::Preparing);
    $orders->updateDelivery($order, DeliveryStatus::InTransit);
    $orders->updateDelivery($order, DeliveryStatus::Failed);
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(0);

    $orders->returnFailedDelivery($order);
    expect(InventoryBalance::find($product->id)->on_hand)->toBe(1)
        ->and($order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($order->delivery->fresh()->status)->toBe(DeliveryStatus::Returned);
});

it('requires an admin account for dashboard orders', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)->get('/dashboard/orders')->assertForbidden();
    $customer->forceFill(['is_admin' => true])->save();
    $this->actingAs($customer)->get('/dashboard/orders')->assertOk();
    $this->actingAs($customer)->get('/dashboard/pickup-points')->assertOk();

    $product = commerceProduct();
    $order = app(OrderService::class)->place(courierOrderData($product, 1));
    $this->actingAs($customer)->get('/dashboard/orders/'.$order->id)
        ->assertOk()
        ->assertSee('Курьерская доставка')
        ->assertSee('Оплата при получении')
        ->assertSee('value="preparing"', false)
        ->assertDontSee('value="in_transit"', false);
    $this->actingAs($customer)->put('/dashboard/orders/'.$order->id.'/delivery', ['status' => 'preparing'])
        ->assertRedirect();
    $this->actingAs($customer)->get('/dashboard/orders/'.$order->id)
        ->assertOk()
        ->assertSee('value="in_transit"', false)
        ->assertSee('Готовится')
        ->assertDontSee('value="ready_for_pickup"', false);
    DB::table('delivery_statuses')->where('code', 'in_transit')->update(['name' => 'Передан курьеру']);
    $this->actingAs($customer)->get('/dashboard/orders/'.$order->id)
        ->assertOk()
        ->assertSee('value="in_transit"', false)
        ->assertSee('Передан курьеру');
    $this->actingAs($customer)->get('/dashboard/products/'.$product->id.'/edit')->assertOk();
});

it('rejects an online callback with a mismatched amount', function () {
    config()->set('commerce.online_enabled', true);
    $product = commerceProduct(1);
    $order = app(OrderService::class)->place(courierOrderData($product, 1, 'online'));
    $payment = $order->payments()->firstOrFail();

    expect(fn () => app(OrderService::class)->recordOnlinePayment(
        $payment, 'provider', 'payment-2', 'event-2', 'paid', true, '1.00', 'RUB'
    ))->toThrow(ValidationException::class);
    expect($order->fresh()->status)->toBe(OrderStatus::AwaitingPayment)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Pending);
});

it('does not allow courier checkout before a delivery price is configured', function () {
    config()->set('commerce.courier_delivery_amount', null);
    $product = commerceProduct();
    $this->postJson('/shop/orders', courierOrderData($product), [
        'Idempotency-Key' => (string) Str::uuid(),
    ])->assertUnprocessable();
    expect(Order::count())->toBe(0);
});
