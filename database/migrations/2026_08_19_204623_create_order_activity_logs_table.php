<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'order_activity_logs',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('order_item_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string(
                    'actor_type',
                    30
                );

                $table->unsignedBigInteger(
                    'actor_id'
                );

                $table->string(
                    'action',
                    60
                );

                $table->string(
                    'from_value'
                )->nullable();

                $table->string(
                    'to_value'
                )->nullable();

                $table->text(
                    'note'
                )->nullable();

                $table->timestamps();

                $table->index([
                    'actor_type',
                    'actor_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'order_activity_logs'
        );
    }
};