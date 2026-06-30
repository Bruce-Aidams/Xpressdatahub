<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'balance_history';

    protected $fillable = [
        'agent_id',
        'change_amount',
        'balance_after',
        'reason',
        'reference_id',
        'beneficiary_number',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'change_amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
