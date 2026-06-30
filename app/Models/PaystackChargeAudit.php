<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaystackChargeAudit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'paystack_charge_audit';

    protected $fillable = [
        'order_id',
        'shop_id',
        'base_amount',
        'charge_amount',
        'total_amount',
        'admin_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'charge_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }
}
