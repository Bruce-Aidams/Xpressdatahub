<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentOrder extends Model
{
    use HasFactory;

    protected $table = 'agent_orders';

    protected $fillable = [
        'order_id',
        'agent_id',
        'phone_number',
        'network',
        'package_size_mb',
        'amount',
        'status',
        'external_transaction_id',
        'webhook_url',
        'api_key_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }
}
