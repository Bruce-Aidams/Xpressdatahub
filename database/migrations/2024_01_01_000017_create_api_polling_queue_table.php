<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_polling_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('status')->default('pending'); // allowed: pending, processing, completed, failed
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(10);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->index('order_id');
            $table->index('status');
            $table->index('next_attempt_at');
            $table->index('attempts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_polling_queue');
    }
};
