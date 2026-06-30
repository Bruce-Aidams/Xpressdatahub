<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_config', function (Blueprint $table) {
            $table->id();
            $table->decimal('commission_percentage', 5, 2);
            $table->integer('min_orders_required')->default(1);
            $table->boolean('is_enabled')->default(true);
            $table->decimal('max_commission_per_order', 12, 2)->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();

            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_config');
    }
};
