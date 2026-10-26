<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 64)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
        });

        DB::table('products')->orderBy('id')->select('id')->chunkById(500, function ($products) {
            foreach ($products as $product) {
                DB::table('products')->where('id', $product->id)->update(['sku' => 'SKU-'.$product->id]);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 64)->nullable(false)->change();
            $table->decimal('price', 12, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->change();
            $table->dropSoftDeletes();
            $table->dropColumn(['sku', 'is_active']);
        });
    }
};
