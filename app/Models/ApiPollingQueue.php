<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiPollingQueue extends Model
{
    use HasFactory;

    protected $table = 'api_polling_queue';

    protected $fillable = [
        'order_id',
        'status',
        'attempts',
        'max_attempts',
        'last_attempt_at',
        'next_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'last_attempt_at' => 'datetime',
            'next_attempt_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
