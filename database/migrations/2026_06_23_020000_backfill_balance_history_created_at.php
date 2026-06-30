<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('balance_history')
            ->whereNull('created_at')
            ->update(['created_at' => DB::raw("datetime('now')")]);
    }

    public function down(): void
    {
        // No reverse — dates are preserved
    }
};
