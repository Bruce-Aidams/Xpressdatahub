<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralConfig extends Model
{
    use HasFactory;

    protected $table = 'referral_config';

    protected $fillable = [
        'commission_percentage',
        'min_orders_required',
        'is_enabled',
        'max_commission_per_order',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
            'is_enabled' => 'boolean',
            'max_commission_per_order' => 'decimal:2',
        ];
    }
}
