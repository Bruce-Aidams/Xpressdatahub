<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'message',
        'type',
        'sender_id',
        'sender_type',
        'recipient_type',
        'recipient_id',
        'recipient_ids',
        'priority',
        'is_read',
        'data',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'data' => 'array',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'sender_id');
    }
}
