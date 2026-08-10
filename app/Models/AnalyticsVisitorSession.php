<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsVisitorSession extends Model
{
    protected $fillable = [
        'visitor_token',
        'session_token',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'country',
        'city',
        'current_path',
        'current_title',
        'referrer',
        'is_online',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
