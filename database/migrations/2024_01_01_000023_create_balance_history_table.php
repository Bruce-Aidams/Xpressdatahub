<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->decimal('change_amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reason');
            $table->string('reference_id')->nullable();
            $table->string('beneficiary_number')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->index('agent_id');
            $table->index('reason');
            $table->index('reference_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_history');
    }
};
