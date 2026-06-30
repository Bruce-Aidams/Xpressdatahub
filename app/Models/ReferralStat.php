<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralStat extends Model
{
    use HasFactory;

    protected $table = 'referral_stats';

    protected $fillable = [
        'user_id',
        'total_referrals',
        'total_commission_earned',
        'last_referral_at',
    ];

    protected function casts(): array
    {
        return [
            'total_commission_earned' => 'decimal:2',
            'last_referral_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'user_id');
    }
}
