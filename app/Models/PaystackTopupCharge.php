<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaystackTopupCharge extends Model
{
    use HasFactory;

    protected $table = 'paystack_topup_charge';

    protected $fillable = [
        'charge_amount',
        'charge_type',
        'is_active',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'charge_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
