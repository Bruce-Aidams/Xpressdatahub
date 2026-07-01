<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('sender_type')->nullable()->after('sender_id');
            $table->json('recipient_ids')->nullable()->after('recipient_id');
            $table->string('priority')->default('normal')->after('recipient_ids');
            $table->timestamp('expires_at')->nullable()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['sender_type', 'recipient_ids', 'priority', 'expires_at']);
        });
    }
};
