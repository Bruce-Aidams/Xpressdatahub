<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPricing extends Model
{
    use HasFactory;

    protected $table = 'custom_pricing';

    protected $fillable = [
        'package_size',
        'package_size_gb',
        'network_type',
        'cost',
        'selling_price',
        'user_role',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'package_size_gb' => 'decimal:4',
            'cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
