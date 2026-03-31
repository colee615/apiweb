<?php

namespace App\Services;

use App\Models\SitePage;
use App\Models\SitePageVersion;
use App\Models\SiteSection;
use App\Models\SiteSectionItem;
use App\Models\User;
use App\Support\ContentSecurity;
use Illuminate\Support\Facades\DB;

class SitePageEditor
{
    public function __construct(
        protected SitePageVersioningService $versioning
    ) {
    }

    public function updatePage(SitePage $page, array $data, ?User $actor = null, array $options = []): SitePage
    {
        $data = ContentSecurity::sanitizePageData($data);
        $beforePage = $this->loadEditablePage($page);
        $updatedPage = null;

        DB::transaction(function () use ($page, $data, $actor, $options, $beforePage, &$updatedPage) {
            $page->fill([
                'slug' => $data['slug'] ?? $page->slug,
                'name' => $data['name'] ?? $page->name,
                'meta_title' => array_key_exists('meta_title', $data) ? $data['meta_title'] : $page->meta_title,
                'meta_description' => array_key_exists('meta_description', $data) ? $data['meta_description'] : $page->meta_description,
                'theme' => array_key_exists('theme', $data) ? $data['theme'] : $page->theme,
                'is_active' => $data['is_active'] ?? $page->is_active,
            ]);
            $page->save();

            if (array_key_exists('sections', $data)) {
                $this->syncSections($page, $data['sections'] ?? []);
            }

            $updatedPage = $this->loadEditablePage($page->fresh());

            $this->versioning->recordUpdate(
                beforePage: $beforePage,
                afterPage: $updatedPage,
                actor: $actor,
                options: $options
            );
        });

        return $updatedPage;
    }

    public function createInitialVersion(SitePage $page, ?User $actor = null, ?string $summary = null): SitePageVersion
    {
        return DB::transaction(function () use ($page, $actor, $summary) {
            return $this->versioning->createInitialVersion(
                $this->loadEditablePage($page),
                $actor,
                $summary
            );
        });
    }

    public function restoreVersion(
        SitePage $page,
        SitePageVersion $version,
        ?User $actor = null,
        ?string $summary = null
    ): SitePage {
        $snapshot = $version->snapshot ?? [];

        return $this->updatePage($page, [
            'slug' => $snapshot['slug'] ?? $page->slug,
            'name' => $snapshot['name'] ?? $page->name,
            'meta_title' => $snapshot['meta_title'] ?? null,
            'meta_description' => $snapshot['meta_description'] ?? null,
            'theme' => $snapshot['theme'] ?? [],
            'is_active' => $snapshot['is_active'] ?? true,
            'sections' => $snapshot['sections'] ?? [],
        ], $actor, [
            'action' => 'restored',
            'change_summary' => $summary ?: 'Se restauró una versión anterior de la página.',
            'restored_from_version_id' => $version->id,
        ]);
    }

    protected function syncSections(SitePage $page, array $sections): void
    {
        $existingSectionIds = $page->sections()->pluck('id')->all();
        $keptSectionIds = [];

        foreach ($sections as $sectionIndex => $sectionPayload) {
            $sectionId = $sectionPayload['id'] ?? null;

            $section = $sectionId
                ? $page->sections()->where('id', $sectionId)->first()
                : new SiteSection(['site_page_id' => $page->id]);

            if (! $section) {
                continue;
            }

            $section->fill([
                'key' => $sectionPayload['key'] ?? ('section_' . $sectionIndex),
                'name' => $sectionPayload['name'] ?? ('Sección ' . ($sectionIndex + 1)),
                'type' => $sectionPayload['type'] ?? 'generic',
                'settings' => $sectionPayload['settings'] ?? [],
                'sort_order' => $sectionPayload['sort_order'] ?? $sectionIndex,
                'is_active' => $sectionPayload['is_active'] ?? true,
            ]);
            $section->site_page_id = $page->id;
            $section->save();

            $keptSectionIds[] = $section->id;

            $this->syncItems($section, $sectionPayload['items'] ?? []);
        }

        $sectionIdsToDelete = array_diff($existingSectionIds, $keptSectionIds);

        if (! empty($sectionIdsToDelete)) {
            SiteSection::whereIn('id', $sectionIdsToDelete)->delete();
        }
    }

    protected function syncItems(SiteSection $section, array $items): void
    {
        $existingItemIds = $section->items()->pluck('id')->all();
        $keptItemIds = [];

        foreach ($items as $itemIndex => $itemPayload) {
            $itemId = $itemPayload['id'] ?? null;

            $item = $itemId
                ? $section->items()->where('id', $itemId)->first()
                : new SiteSectionItem(['site_section_id' => $section->id]);

            if (! $item) {
                continue;
            }

            $item->fill([
                'name' => $itemPayload['name'] ?? null,
                'type' => $itemPayload['type'] ?? 'item',
                'data' => $itemPayload['data'] ?? [],
                'sort_order' => $itemPayload['sort_order'] ?? $itemIndex,
                'is_active' => $itemPayload['is_active'] ?? true,
            ]);
            $item->site_section_id = $section->id;
            $item->save();

            $keptItemIds[] = $item->id;
        }

        $itemIdsToDelete = array_diff($existingItemIds, $keptItemIds);

        if (! empty($itemIdsToDelete)) {
            SiteSectionItem::whereIn('id', $itemIdsToDelete)->delete();
        }
    }

    protected function loadEditablePage(SitePage $page): SitePage
    {
        return $page->fresh([
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.items' => function ($query) {
                $query->orderBy('sort_order');
            },
        ]);
    }
}
