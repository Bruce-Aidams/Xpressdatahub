<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->integer('total_referrals')->default(0);
            $table->decimal('total_commission_earned', 12, 2)->default(0);
            $table->timestamp('last_referral_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('agents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_stats');
    }
};
