<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_pricing', function (Blueprint $table) {
            $table->id();
            $table->string('package_size');
            $table->decimal('package_size_gb', 12, 4);
            $table->string('network_type')->default('all');
            $table->decimal('cost', 12, 2);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->enum('user_role', ['agent', 'super_agent', 'dealers', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('package_size');
            $table->index('network_type');
            $table->index('user_role');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_pricing');
    }
};
