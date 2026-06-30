<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopWithdrawal extends Model
{
    use HasFactory;

    protected $table = 'shop_withdrawals';

    protected $fillable = [
        'shop_id',
        'user_id',
        'amount',
        'payment_method',
        'payment_details',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'user_id');
    }
}
