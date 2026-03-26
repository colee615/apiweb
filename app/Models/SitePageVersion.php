<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePageVersion extends Model
{
    protected $fillable = [
        'site_page_id',
        'version_number',
        'action',
        'change_summary',
        'snapshot',
        'created_by_user_id',
        'created_by_name',
        'created_by_email',
        'restored_from_version_id',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function restoredFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'restored_from_version_id');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(SitePageChangeLog::class)->orderBy('created_at', 'desc');
    }
}
