<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('recipient_type', ['admin', 'user', 'all'])->default('all');
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->json('data')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('type');
            $table->index('recipient_type');
            $table->index('recipient_id');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
