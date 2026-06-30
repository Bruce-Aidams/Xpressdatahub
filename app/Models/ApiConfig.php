<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiConfig extends Model
{
    use HasFactory;

    protected $table = 'api_config';

    protected $fillable = [
        'network_type',
        'api_name',
        'endpoint_url',
        'status_endpoint',
        'api_key',
        'api_secret',
        'request_method',
        'request_headers',
        'request_body_template',
        'response_success_field',
        'response_data_field',
        'response_error_field',
        'is_active',
        'timeout_seconds',
        'retry_attempts',
        'config_data',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config_data' => 'array',
        ];
    }
}
