<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_config', function (Blueprint $table) {
            $table->id();
            $table->string('network_type');
            $table->string('endpoint_url');
            $table->string('api_key');
            $table->string('api_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('config_data')->nullable();
            $table->timestamps();

            $table->index('network_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_config');
    }
};
