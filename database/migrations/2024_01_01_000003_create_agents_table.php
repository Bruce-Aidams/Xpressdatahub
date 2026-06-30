<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password_hash');
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('role', ['agent', 'super_agent', 'dealers'])->default('agent');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('registration_ip')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->string('device_id')->nullable();
            $table->string('referral_code')->unique()->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamps();

            $table->foreign('referred_by')->references('id')->on('agents')->onDelete('set null');
            $table->index('email');
            $table->index('phone');
            $table->index('role');
            $table->index('status');
            $table->index('referral_code');
            $table->index('referred_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
