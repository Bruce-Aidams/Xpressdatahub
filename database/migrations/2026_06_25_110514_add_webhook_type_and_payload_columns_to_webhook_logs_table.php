<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->string('webhook_type')->nullable()->after('order_id');
            $table->string('external_transaction_id')->nullable()->after('webhook_type');
            $table->text('payload')->nullable()->after('external_transaction_id');
            $table->string('response_status')->nullable()->after('payload');
            $table->boolean('processed')->default(false)->after('response_status');
            $table->text('error_message')->nullable()->after('processed');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropColumn([
                'webhook_type',
                'external_transaction_id',
                'payload',
                'response_status',
                'processed',
                'error_message',
            ]);
        });
    }
};
