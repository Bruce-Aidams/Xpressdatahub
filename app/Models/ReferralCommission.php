<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'referral_commissions';

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'order_id',
        'order_amount',
        'commission_percentage',
        'commission_amount',
        'commission_date',
        'status',
        'credited_at',
    ];

    protected function casts(): array
    {
        return [
            'order_amount' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'credited_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'referred_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
