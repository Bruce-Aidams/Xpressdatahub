<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationConfig extends Model
{
    use HasFactory;

    protected $table = 'notification_config';

    protected $fillable = [
        'config_key',
        'config_value',
        'description',
    ];
}
