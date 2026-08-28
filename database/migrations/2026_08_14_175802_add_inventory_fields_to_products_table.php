<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku', 100)
                ->nullable()
                ->unique()
                ->after('slug');

            $table->unsignedInteger('stock_quantity')
                ->default(0)
                ->after('compare_price');

            $table->unsignedInteger('low_stock_threshold')
                ->default(5)
                ->after('stock_quantity');

            $table->boolean('manage_stock')
                ->default(true)
                ->after('low_stock_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);

            $table->dropColumn([
                'sku',
                'stock_quantity',
                'low_stock_threshold',
                'manage_stock',
            ]);
        });
    }
};