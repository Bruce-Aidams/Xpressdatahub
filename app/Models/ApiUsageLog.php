<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiUsageLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'api_usage_logs';

    protected $fillable = [
        'api_key_id',
        'endpoint',
        'method',
        'ip_address',
        'response_code',
        'response_time_ms',
    ];

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
