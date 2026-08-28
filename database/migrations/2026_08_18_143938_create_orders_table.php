<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_number')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')
                ->default('pending')
                ->index();

            $table->string('payment_method')
                ->default('cod');

            $table->string('payment_status')
                ->default('unpaid')
                ->index();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);

            $table->string('shipping_name');
            $table->string('shipping_email');
            $table->string('shipping_phone', 30);
            $table->text('shipping_address');
            $table->string('shipping_city', 100);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};