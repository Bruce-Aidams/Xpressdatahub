<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_pricing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('package_size');
            $table->decimal('package_size_gb', 12, 4);
            $table->string('network_type')->default('all');
            $table->decimal('base_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->decimal('profit', 12, 2)->default(0);
            $table->timestamp('created_at')->nullable();

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->unique(['shop_id', 'package_size', 'network_type']);
            $table->index('shop_id');
            $table->index('network_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_pricing');
    }
};
