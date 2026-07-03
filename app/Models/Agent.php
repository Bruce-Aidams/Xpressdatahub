<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agent extends Model
{
    use HasFactory;

    protected $table = 'agents';

    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'phone',
        'password_hash',
        'balance',
        'role',
        'status',
        'is_approved',
        'registration_ip',
        'last_login_ip',
        'device_id',
        'referral_code',
        'referred_by',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_approved' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function agentOrders(): HasMany
    {
        return $this->hasMany(AgentOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'user_id');
    }

    public function referrerCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function referredCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referred_user_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'referred_by');
    }

    public function balanceHistory(): HasMany
    {
        return $this->hasMany(BalanceHistory::class);
    }

    public function userLoginLogs(): HasMany
    {
        return $this->hasMany(UserLoginLog::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }
}
