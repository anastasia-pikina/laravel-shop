<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const METHODS = [
        'courier' => 'Курьерская доставка',
        'pickup' => 'Самовывоз',
    ];

    /**
     * @return void
     * @throws RuntimeException
     */
    public function up(): void
    {
        $unknown = DB::table('order_deliveries')->whereNotIn('method', array_keys(self::METHODS))
            ->distinct()->pluck('method')->all();
        if ($unknown !== []) {
            throw new RuntimeException('Unknown delivery methods: '.implode(', ', $unknown));
        }

        Schema::create('delivery_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();
        foreach (self::METHODS as $code => $name) {
            DB::table('delivery_methods')->insert([
                'code' => $code, 'name' => $name, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('method_id')->nullable();
        });
        foreach (DB::table('delivery_methods')->pluck('id', 'code') as $code => $id) {
            DB::table('order_deliveries')->where('method', $code)->update(['method_id' => $id]);
        }
        if (DB::table('order_deliveries')->whereNull('method_id')->exists()) {
            throw new RuntimeException('Could not backfill order_deliveries.method_id');
        }

        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->unsignedBigInteger('method_id')->nullable(false)->change();
        });
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->foreign('method_id')->references('id')->on('delivery_methods')->restrictOnDelete();
            $table->dropColumn('method');
        });
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    public function down(): void
    {
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->string('method', 32)->nullable();
        });
        foreach (DB::table('delivery_methods')->get(['id', 'code']) as $row) {
            DB::table('order_deliveries')->where('method_id', $row->id)->update(['method' => $row->code]);
        }
        if (DB::table('order_deliveries')->whereNull('method')->exists()) {
            throw new RuntimeException('Could not restore order_deliveries.method');
        }

        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropForeign(['method_id']);
            $table->string('method', 32)->nullable(false)->change();
        });
        Schema::table('order_deliveries', function (Blueprint $table) {
            $table->dropColumn('method_id');
        });
        Schema::dropIfExists('delivery_methods');
    }
};
