<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteSection extends Model
{
    protected $fillable = [
        'site_page_id',
        'key',
        'name',
        'type',
        'settings',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SiteSectionItem::class)->orderBy('sort_order');
    }
}
