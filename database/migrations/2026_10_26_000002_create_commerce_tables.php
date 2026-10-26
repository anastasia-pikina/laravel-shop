<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_points', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('address');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number', 32)->unique();
            $table->uuid('checkout_key')->unique();
            $table->char('checkout_fingerprint', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 32);
            $table->string('status', 32);
            $table->string('payment_method', 32);
            $table->char('currency', 3)->default('RUB');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_amount', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamp('placed_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'expires_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku', 64);
            $table->string('name');
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total', 12, 2);
            $table->timestamps();
            $table->unique(['order_id', 'product_id']);
        });

        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained();
            $table->string('method', 32);
            $table->string('status', 32);
            $table->string('recipient_name');
            $table->string('recipient_phone', 32);
            $table->foreignId('pickup_point_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address_snapshot')->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('house', 64)->nullable();
            $table->string('apartment', 64)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('handed_over_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'updated_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->string('method', 32);
            $table->string('provider')->nullable();
            $table->string('provider_payment_id')->nullable();
            $table->string('idempotency_key', 64)->unique();
            $table->string('status', 32);
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3);
            $table->string('failure_code')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_payment_id']);
            $table->index(['order_id', 'status']);
        });

        Schema::create('payment_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained();
            $table->string('provider');
            $table->string('provider_event_id');
            $table->string('event_type');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_event_id']);
        });

        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained();
            $table->string('provider')->nullable();
            $table->string('provider_refund_id')->nullable();
            $table->string('status', 32);
            $table->decimal('amount', 12, 2);
            $table->string('reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_refund_id']);
        });

        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->foreignId('product_id')->primary()->constrained();
            $table->unsignedInteger('on_hand')->default(0);
            $table->unsignedInteger('reserved')->default(0);
            $table->timestamps();
        });

        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->unique()->constrained();
            $table->unsignedInteger('quantity');
            $table->string('status', 32);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'expires_at']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('order_item_id')->nullable()->constrained();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('delta');
            $table->string('reason', 32);
            $table->string('idempotency_key', 80)->unique();
            $table->timestamps();
        });

        DB::table('products')->orderBy('id')->select('id')->chunkById(500, function ($products) {
            $now = now();
            DB::table('inventory_balances')->insert($products->map(fn ($product) => [
                'product_id' => $product->id,
                'on_hand' => 0,
                'reserved' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE inventory_balances ADD CONSTRAINT inventory_reserved_check CHECK (reserved <= on_hand)');
            DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_amount_check CHECK (subtotal >= 0 AND delivery_amount >= 0 AND total = subtotal + delivery_amount)');
            DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_amount_check CHECK (quantity > 0 AND unit_price >= 0 AND total = unit_price * quantity)');
            DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount >= 0)');
            DB::statement('ALTER TABLE stock_reservations ADD CONSTRAINT stock_reservations_quantity_check CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        foreach (['inventory_movements', 'stock_reservations', 'inventory_balances', 'payment_refunds', 'payment_webhook_events', 'payments', 'order_deliveries', 'order_items', 'orders', 'pickup_points'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
