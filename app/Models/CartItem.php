<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'agent_id',
        'network_type',
        'package_size',
        'amount',
        'cost',
        'phone_number',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cost' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function getSubtotalAttribute(): float
    {
        return floatval($this->amount) * $this->quantity;
    }
}
