<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_user_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('order_amount', 12, 2);
            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->enum('status', ['pending', 'credited', 'failed'])->default('pending');
            $table->timestamp('credited_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('referrer_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('referrer_id');
            $table->index('referred_user_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
