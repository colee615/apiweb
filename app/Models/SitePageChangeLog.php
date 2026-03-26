<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageChangeLog extends Model
{
    protected $fillable = [
        'site_page_id',
        'site_page_version_id',
        'site_section_id',
        'site_section_item_id',
        'section_key',
        'item_name',
        'entity_type',
        'action',
        'field_name',
        'summary',
        'before_state',
        'after_state',
        'created_by_user_id',
        'created_by_name',
        'created_by_email',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'site_page_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(SitePageVersion::class, 'site_page_version_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SiteSection::class, 'site_section_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(SiteSectionItem::class, 'site_section_item_id');
    }
}
