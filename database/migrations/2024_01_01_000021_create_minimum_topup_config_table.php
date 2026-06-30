<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minimum_topup_config', function (Blueprint $table) {
            $table->id();
            $table->decimal('minimum_amount', 12, 2)->default(1);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();

            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minimum_topup_config');
    }
};
