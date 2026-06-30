<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'webhook_logs';

    protected $fillable = [
        'order_id',
        'webhook_type',
        'external_transaction_id',
        'webhook_url',
        'payload',
        'request_data',
        'response_data',
        'response_status',
        'status_code',
        'signature',
        'processed',
        'error_message',
        'created_at',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
