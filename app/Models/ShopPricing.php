<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPricing extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'shop_pricing';

    protected $fillable = [
        'shop_id',
        'package_size',
        'package_size_gb',
        'network_type',
        'base_price',
        'selling_price',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'package_size_gb' => 'decimal:4',
            'base_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'profit' => 'decimal:2',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
