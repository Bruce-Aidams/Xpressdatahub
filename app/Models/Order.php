<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'agent_id',
        'guest_id',
        'phone_number',
        'network_type',
        'package_size',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'external_transaction_id',
        'external_reference',
        'api_response_data',
        'status_updated_at',
        'last_status_check',
        'shop_id',
        'order_source',
        'order_reference',
        'customer_email',
        'base_amount',
        'paystack_total',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'paystack_total' => 'decimal:2',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function agentOrders(): HasMany
    {
        return $this->hasMany(AgentOrder::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
