<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
