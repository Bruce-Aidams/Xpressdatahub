<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('shop_slug')->unique();
            $table->boolean('is_active')->default(false);
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')->references('id')->on('agents')->onDelete('cascade');
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
