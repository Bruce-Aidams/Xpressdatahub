<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_login_logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_status',
        'login_at',
        'logout_at',
        'session_duration',
    ];

    protected function casts(): array
    {
        return [
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'user_id');
    }
}
