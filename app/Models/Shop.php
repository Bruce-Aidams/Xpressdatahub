<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    use HasFactory;

    protected $table = 'shops';

    protected $fillable = [
        'user_id',
        'shop_slug',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'user_id');
    }

    public function setting(): HasOne
    {
        return $this->hasOne(ShopSetting::class);
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(ShopPricing::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(ShopEarning::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(ShopWithdrawal::class);
    }
}
