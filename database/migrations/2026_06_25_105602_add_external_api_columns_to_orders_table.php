<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('external_transaction_id')->nullable()->after('transaction_id');
            $table->string('external_reference')->nullable()->after('external_transaction_id');
            $table->text('api_response_data')->nullable()->after('external_reference');
            $table->timestamp('status_updated_at')->nullable()->after('api_response_data');
            $table->timestamp('last_status_check')->nullable()->after('status_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'external_transaction_id',
                'external_reference',
                'api_response_data',
                'status_updated_at',
                'last_status_check',
            ]);
        });
    }
};
