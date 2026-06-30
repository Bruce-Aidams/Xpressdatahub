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
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('phone_number');
            $table->string('network_type');
            $table->string('package_size');
            $table->decimal('amount', 12, 2);
            $table->enum('status', [
                'pending', 'payment_pending', 'processing', 'verified', 'delivered',
                'failed', 'cancelled', 'paid', 'owner_insufficient_balance', 'delivery_failed'
            ])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->string('order_source')->default('web');
            $table->string('order_reference')->nullable();
            $table->string('customer_email')->nullable();
            $table->decimal('base_amount', 12, 2)->nullable();
            $table->decimal('paystack_total', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->index('agent_id');
            $table->index('phone_number');
            $table->index('network_type');
            $table->index('status');
            $table->index('transaction_id');
            $table->index('shop_id');
            $table->index('order_source');
            $table->index('order_reference');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
