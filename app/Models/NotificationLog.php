<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'notification_logs';

    protected $fillable = [
        'user_id',
        'type',
        'recipient',
        'subject',
        'body',
        'status',
        'error_message',
    ];
}
