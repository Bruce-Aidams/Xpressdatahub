<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('transaction_id');
            $table->string('paystack_reference')->nullable();
            $table->string('status')->default('pending'); // allowed: pending, verified, failed
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->index('agent_id');
            $table->index('transaction_id');
            $table->index('paystack_reference');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders_payments');
    }
};
