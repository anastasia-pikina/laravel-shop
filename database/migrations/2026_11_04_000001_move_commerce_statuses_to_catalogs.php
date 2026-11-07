<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Existing codes are a migration snapshot; later enum changes must use a new migration.
    private const CATALOGS = [
        'orders' => ['order_statuses', ['awaiting_payment', 'confirmed', 'processing', 'completed', 'cancelled'], ['status', 'expires_at']],
        'order_deliveries' => ['delivery_statuses', ['pending', 'preparing', 'ready_for_pickup', 'in_transit', 'delivered', 'failed', 'returned', 'cancelled'], ['status', 'updated_at']],
        'payments' => ['payment_statuses', ['pending', 'succeeded', 'failed'], ['order_id', 'status']],
        'payment_refunds' => ['refund_statuses', ['pending'], null],
        'stock_reservations' => ['reservation_statuses', ['active', 'released', 'consumed'], ['status', 'expires_at']],
    ];

    /**
     * @return void
     * @throws RuntimeException
     */
    public function up(): void
    {
        foreach (self::CATALOGS as $table => [, $codes]) {
            $unknown = DB::table($table)->whereNotIn('status', $codes)->distinct()->pluck('status')->all();
            if ($unknown !== []) {
                throw new RuntimeException("Unknown statuses in {$table}: ".implode(', ', $unknown));
            }
        }

        foreach (self::CATALOGS as [$catalog, $codes]) {
            Schema::create($catalog, function (Blueprint $table) {
                $table->id();
                $table->string('code', 32)->unique();
                $table->string('name');
                $table->timestamps();
            });

            $now = now();
            DB::table($catalog)->insert(array_map(fn (string $code) => [
                'code' => $code,
                'name' => $code,
                'created_at' => $now,
                'updated_at' => $now,
            ], $codes));
        }

        foreach (self::CATALOGS as $table => [$catalog, , $oldIndex]) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('status_id')->nullable();
            });

            foreach (DB::table($catalog)->pluck('id', 'code') as $code => $id) {
                DB::table($table)->where('status', $code)->update(['status_id' => $id]);
            }
            if (DB::table($table)->whereNull('status_id')->exists()) {
                throw new RuntimeException("Could not backfill {$table}.status_id");
            }

            if ($oldIndex !== null) {
                Schema::table($table, function (Blueprint $blueprint) use ($oldIndex) {
                    $blueprint->index(array_map(fn (string $column) => $column === 'status' ? 'status_id' : $column, $oldIndex));
                });
            }
            Schema::table($table, function (Blueprint $blueprint) use ($oldIndex) {
                if ($oldIndex !== null) {
                    $blueprint->dropIndex($oldIndex);
                }
                $blueprint->unsignedBigInteger('status_id')->nullable(false)->change();
            });
            Schema::table($table, function (Blueprint $blueprint) use ($catalog) {
                $blueprint->foreign('status_id')->references('id')->on($catalog)->restrictOnDelete();
                $blueprint->dropColumn('status');
            });
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    public function down(): void
    {
        foreach (self::CATALOGS as $table => [$catalog, , $oldIndex]) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('status', 32)->nullable();
            });

            foreach (DB::table($catalog)->get(['id', 'code']) as $row) {
                DB::table($table)->where('status_id', $row->id)->update(['status' => $row->code]);
            }
            if (DB::table($table)->whereNull('status')->exists()) {
                throw new RuntimeException("Could not restore {$table}.status");
            }

            if ($oldIndex !== null) {
                Schema::table($table, function (Blueprint $blueprint) use ($oldIndex) {
                    $blueprint->index($oldIndex);
                });
            }
            Schema::table($table, function (Blueprint $blueprint) use ($oldIndex) {
                $blueprint->dropForeign(['status_id']);
                if ($oldIndex !== null) {
                    $blueprint->dropIndex(array_map(fn (string $column) => $column === 'status' ? 'status_id' : $column, $oldIndex));
                }
                $blueprint->string('status', 32)->nullable(false)->change();
            });
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('status_id');
            });
        }

        foreach (array_reverse(self::CATALOGS) as [$catalog]) {
            Schema::dropIfExists($catalog);
        }
    }
};
