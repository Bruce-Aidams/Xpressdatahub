<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_earnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('order_id')->unique();
            $table->string('order_reference');
            $table->string('package_size');
            $table->decimal('selling_price', 12, 2);
            $table->decimal('base_price', 12, 2);
            $table->decimal('profit', 12, 2);
            $table->string('status')->default('pending');
            $table->timestamp('credited_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('shop_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_earnings');
    }
};
