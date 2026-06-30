<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowBalanceAlert extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'low_balance_alerts';

    protected $fillable = [
        'user_id',
        'threshold_amount',
        'alert_sent_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'threshold_amount' => 'decimal:2',
            'alert_sent_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'user_id');
    }
}
