<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->string('api_token', 64)->nullable()->unique()->after('is_active');
        });

        $admin = DB::table('admin_users')->where('username', 'admin')->first();
        if ($admin) {
            DB::table('admin_users')
                ->where('id', $admin->id)
                ->update(['api_token' => Str::random(64)]);
        }
    }

    public function down(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropColumn('api_token');
        });
    }
};
