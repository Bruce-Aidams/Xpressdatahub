<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_topup_charge', function (Blueprint $table) {
            $table->id();
            $table->decimal('charge_amount', 12, 2);
            $table->string('charge_type')->default('fixed'); // allowed: fixed, percentage
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('charge_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_topup_charge');
    }
};
