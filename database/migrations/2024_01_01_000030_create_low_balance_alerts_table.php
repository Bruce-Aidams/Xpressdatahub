<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('low_balance_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('threshold_amount', 12, 2);
            $table->timestamp('alert_sent_at')->nullable();
            $table->string('status')->default('pending'); // allowed: pending, sent, failed
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')->references('id')->on('agents')->onDelete('cascade');
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('low_balance_alerts');
    }
};
