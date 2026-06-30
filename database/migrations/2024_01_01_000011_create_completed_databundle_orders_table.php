<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('completed_databundle_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('agent_id');
            $table->string('phone_number');
            $table->string('network');
            $table->integer('package_size_mb');
            $table->decimal('amount', 12, 2);
            $table->string('paystack_reference')->nullable();
            $table->string('paystack_transaction_id')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->index('order_id');
            $table->index('agent_id');
            $table->index('status');
            $table->index('paystack_reference');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('completed_databundle_orders');
    }
};
