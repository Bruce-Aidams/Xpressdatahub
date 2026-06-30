<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_key_id');
            $table->string('endpoint');
            $table->string('method');
            $table->string('ip_address');
            $table->integer('status_code');
            $table->integer('response_time_ms')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('api_key_id')->references('id')->on('api_keys')->onDelete('cascade');
            $table->index('api_key_id');
            $table->index('endpoint');
            $table->index('status_code');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
