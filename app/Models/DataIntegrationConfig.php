<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataIntegrationConfig extends Model
{
    use HasFactory;

    protected $table = 'data_integration_config';

    protected $fillable = [
        'config_key',
        'config_value',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}
