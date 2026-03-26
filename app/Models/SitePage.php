<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SitePage extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'meta_title',
        'meta_description',
        'theme',
        'is_active',
    ];

    protected $casts = [
        'theme' => 'array',
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(SiteSection::class)->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SitePageVersion::class)->orderByDesc('version_number');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(SitePageVersion::class)->latestOfMany('version_number');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(SitePageChangeLog::class)->orderByDesc('created_at');
    }
}
