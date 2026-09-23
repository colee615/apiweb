<?php

namespace App\Services;

use App\Models\SitePage;
use App\Models\SiteSection;
use App\Models\SiteSectionItem;
use App\Support\ContentSecurity;

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

    /** Build a public page using the brand and navigation managed from Home. */
    public function buildPublic(SitePage $page): array
    {
        $payload = $this->build($page);
        $home = $page->slug === 'home'
            ? $page
            : SitePage::query()->where('slug', 'home')->with([
                'sections' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'sections.items' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            ])->first();

        if (! $home) {
            return $payload;
        }

        $shared = $this->build($home);
        $payload['theme'] = $shared['theme'];

        $sections = collect($payload['sections'])
            ->reject(fn (array $section) => $section['key'] === 'header')
            ->values();
        $header = $shared['section_map']['header'] ?? null;

        if ($header && $header['is_active']) {
            $sections->prepend($header);
        }

        $payload['sections'] = $sections->values()->all();
        $payload['section_map'] = $sections->keyBy('key')->all();

        return $payload;
    }

    public function normalizeAssetFields(array $data): array
    {
        $data = ContentSecurity::sanitizeArray($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalizeAssetFields($value);
                continue;
            }

            if (in_array($key, ContentSecurity::ASSET_KEYS, true)) {
                $data[$key] = ContentSecurity::normalizeAssetUrl($value);
            }
        }

        return $data;
    }

    public function normalizeAssetUrl(?string $value): ?string
    {
        return ContentSecurity::normalizeAssetUrl($value);
    }
}
