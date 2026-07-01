<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('balance_history')
            ->whereNull('created_at')
            ->update(['created_at' => Carbon::now()->toDateTimeString()]);
    }

    public function down(): void
    {
        // No reverse — dates are preserved
    }
};
