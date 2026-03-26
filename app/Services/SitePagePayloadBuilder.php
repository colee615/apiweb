<?php

namespace App\Services;

use App\Models\SitePage;
use App\Models\SiteSection;
use App\Models\SiteSectionItem;

class SitePagePayloadBuilder
{
    public function build(SitePage $page): array
    {
        $page->loadMissing([
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.items' => function ($query) {
                $query->orderBy('sort_order');
            },
        ]);

        $sections = $page->sections->map(function (SiteSection $section) {
            return [
                'id' => $section->id,
                'key' => $section->key,
                'name' => $section->name,
                'type' => $section->type,
                'sort_order' => $section->sort_order,
                'is_active' => $section->is_active,
                'settings' => $this->normalizeAssetFields($section->settings ?? []),
                'items' => $section->items->map(function (SiteSectionItem $item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'type' => $item->type,
                        'sort_order' => $item->sort_order,
                        'is_active' => $item->is_active,
                        'data' => $this->normalizeAssetFields($item->data ?? []),
                    ];
                })->values()->all(),
            ];
        })->values();

        return [
            'id' => $page->id,
            'slug' => $page->slug,
            'name' => $page->name,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'theme' => $this->normalizeAssetFields($page->theme ?? []),
            'is_active' => $page->is_active,
            'sections' => $sections->all(),
            'section_map' => $sections->keyBy('key')->all(),
        ];
    }

    public function normalizeAssetFields(array $data): array
    {
        $assetKeys = ['logo_url', 'background_image', 'iconImage', 'image', 'src', 'poster', 'poster_image'];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalizeAssetFields($value);
                continue;
            }

            if (in_array($key, $assetKeys, true)) {
                $data[$key] = $this->normalizeAssetUrl($value);
            }
        }

        return $data;
    }

    public function normalizeAssetUrl(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '/storage/') || str_starts_with($value, 'storage/')) {
            return url(ltrim($value, '/'));
        }

        return $value;
    }
}
