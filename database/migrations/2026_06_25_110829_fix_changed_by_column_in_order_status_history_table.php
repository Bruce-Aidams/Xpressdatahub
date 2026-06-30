<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropColumn('changed_by');
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->string('changed_by')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropColumn('changed_by');
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->unsignedBigInteger('changed_by')->nullable()->after('notes');
        });
    }
};
