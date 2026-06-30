<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinimumTopupConfig extends Model
{
    use HasFactory;

    protected $table = 'minimum_topup_config';

    protected $fillable = [
        'minimum_amount',
        'is_enabled',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'minimum_amount' => 'decimal:2',
            'is_enabled' => 'boolean',
        ];
    }
}
