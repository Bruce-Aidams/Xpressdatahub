<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopEarning extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'shop_earnings';

    protected $fillable = [
        'shop_id',
        'order_id',
        'order_reference',
        'package_size',
        'selling_price',
        'base_price',
        'profit',
        'status',
        'credited_at',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'base_price' => 'decimal:2',
            'profit' => 'decimal:2',
            'credited_at' => 'datetime',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
