<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $orders = DB::table('orders')
            ->where('order_source', 'guest')
            ->where(function ($q) {
                $q->whereNull('guest_id')->orWhere('guest_id', '');
            })
            ->get();

        foreach ($orders as $order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['guest_id' => 'GST-'.strtoupper(Str::random(6))]);
        }
    }

    public function down(): void {}
};
