<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const METHODS = [
        'online' => 'Оплата онлайн',
        'on_delivery' => 'Оплата при получении',
    ];

    private const COLUMNS = [
        'orders' => ['payment_method', 'payment_method_id'],
        'payments' => ['method', 'method_id'],
    ];

    /**
     * @return void
     * @throws RuntimeException
     */
    public function up(): void
    {
        foreach (self::COLUMNS as $table => [$oldColumn]) {
            $unknown = DB::table($table)->whereNotIn($oldColumn, array_keys(self::METHODS))
                ->distinct()->pluck($oldColumn)->all();
            if ($unknown !== []) {
                throw new RuntimeException("Unknown payment methods in {$table}: ".implode(', ', $unknown));
            }
        }

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();
        foreach (self::METHODS as $code => $name) {
            DB::table('payment_methods')->insert([
                'code' => $code, 'name' => $name, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (self::COLUMNS as $table => [$oldColumn, $newColumn]) {
            Schema::table($table, function (Blueprint $blueprint) use ($newColumn) {
                $blueprint->unsignedBigInteger($newColumn)->nullable();
            });
            foreach (DB::table('payment_methods')->pluck('id', 'code') as $code => $id) {
                DB::table($table)->where($oldColumn, $code)->update([$newColumn => $id]);
            }
            if (DB::table($table)->whereNull($newColumn)->exists()) {
                throw new RuntimeException("Could not backfill {$table}.{$newColumn}");
            }

            Schema::table($table, function (Blueprint $blueprint) use ($newColumn) {
                $blueprint->unsignedBigInteger($newColumn)->nullable(false)->change();
            });
            Schema::table($table, function (Blueprint $blueprint) use ($oldColumn, $newColumn) {
                $blueprint->foreign($newColumn)->references('id')->on('payment_methods')->restrictOnDelete();
                $blueprint->dropColumn($oldColumn);
            });
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    public function down(): void
    {
        foreach (array_reverse(self::COLUMNS, true) as $table => [$oldColumn, $newColumn]) {
            Schema::table($table, function (Blueprint $blueprint) use ($oldColumn) {
                $blueprint->string($oldColumn, 32)->nullable();
            });
            foreach (DB::table('payment_methods')->get(['id', 'code']) as $row) {
                DB::table($table)->where($newColumn, $row->id)->update([$oldColumn => $row->code]);
            }
            if (DB::table($table)->whereNull($oldColumn)->exists()) {
                throw new RuntimeException("Could not restore {$table}.{$oldColumn}");
            }

            Schema::table($table, function (Blueprint $blueprint) use ($oldColumn, $newColumn) {
                $blueprint->dropForeign([$newColumn]);
                $blueprint->string($oldColumn, 32)->nullable(false)->change();
            });
            Schema::table($table, function (Blueprint $blueprint) use ($newColumn) {
                $blueprint->dropColumn($newColumn);
            });
        }

        Schema::dropIfExists('payment_methods');
    }
};
