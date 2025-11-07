<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';
    
    protected $fillable = [
        'user_id',
        'email_opt_in',
        'sms_opt_in',
        'sms_number',
        'sms_opted_in_at',
        'push_opt_in',
        'push_token',
        'push_platform',
        'push_metadata',
        'push_opted_in_at',
    ];

    protected $casts = [
        'email_opt_in' => 'boolean',
        'sms_opt_in' => 'boolean',
        'push_opt_in' => 'boolean',
        'sms_opted_in_at' => 'datetime',
        'push_opted_in_at' => 'datetime',
        'push_metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}