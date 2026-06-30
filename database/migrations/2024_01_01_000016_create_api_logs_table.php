<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('endpoint');
            $table->text('request_data')->nullable();
            $table->text('response_data')->nullable();
            $table->integer('status_code')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->index('agent_id');
            $table->index('endpoint');
            $table->index('status_code');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
