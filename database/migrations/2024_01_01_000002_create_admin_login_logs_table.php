<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('username')->nullable();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('login_status');
            $table->string('failure_reason')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('admin_id')->references('id')->on('admin_users')->onDelete('cascade');
            $table->index('admin_id');
            $table->index('login_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_logs');
    }
};
