<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSectionItem extends Model
{
    protected $fillable = [
        'site_section_id',
        'name',
        'type',
        'data',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(SiteSection::class, 'site_section_id');
    }
}
