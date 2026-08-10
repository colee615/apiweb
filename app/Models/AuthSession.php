<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthSession extends Model
{
    protected $fillable = [
        'user_id',
        'guard_name',
        'session_key',
        'ip_address',
        'user_agent',
        'last_path',
        'is_active',
        'logged_in_at',
        'last_seen_at',
        'logged_out_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'logged_in_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
