<?php

namespace App\Services;

use App\Models\SitePage;
use App\Models\SitePageChangeLog;
use App\Models\SitePageVersion;
use App\Models\User;
use Illuminate\Support\Arr;

class SitePageVersioningService
{
    public function __construct(
        protected SitePagePayloadBuilder $payloadBuilder
    ) {
    }

    public function createInitialVersion(SitePage $page, ?User $actor = null, ?string $summary = null): SitePageVersion
    {
        return $this->storeVersion(
            page: $page,
            action: 'created',
            snapshot: $this->payloadBuilder->build($page),
            changeSummary: $summary ?: 'Versión inicial de la página.',
            actor: $actor,
            changes: [[
                'section_key' => null,
                'item_name' => null,
                'site_section_id' => null,
                'site_section_item_id' => null,
                'entity_type' => 'page',
                'action' => 'created',
                'field_name' => null,
                'summary' => 'Se creó la página y se registraron sus contenidos iniciales.',
                'before_state' => null,
                'after_state' => $this->payloadBuilder->build($page),
            ]]
        );
    }

    public function recordUpdate(
        SitePage $beforePage,
        SitePage $afterPage,
        ?User $actor = null,
        array $options = []
    ): SitePageVersion {
        $beforeSnapshot = $this->payloadBuilder->build($beforePage);
        $afterSnapshot = $this->payloadBuilder->build($afterPage);
        $changes = $this->buildChanges($beforeSnapshot, $afterSnapshot);

        if (empty($changes)) {
            $changes[] = [
                'section_key' => null,
                'item_name' => null,
                'site_section_id' => null,
                'site_section_item_id' => null,
                'entity_type' => 'page',
                'action' => $options['action'] ?? 'updated',
                'field_name' => null,
                'summary' => 'Se guardó la página sin diferencias detectables a nivel estructural.',
                'before_state' => null,
                'after_state' => null,
            ];
        }

        return $this->storeVersion(
            page: $afterPage,
            action: $options['action'] ?? 'updated',
            snapshot: $afterSnapshot,
            changeSummary: $options['change_summary'] ?? null,
            actor: $actor,
            changes: $changes,
            restoredFromVersionId: $options['restored_from_version_id'] ?? null
        );
    }

    protected function storeVersion(
        SitePage $page,
        string $action,
        array $snapshot,
        ?string $changeSummary,
        ?User $actor,
        array $changes,
        ?int $restoredFromVersionId = null
    ): SitePageVersion {
        $latestVersion = SitePageVersion::query()
            ->where('site_page_id', $page->id)
            ->lockForUpdate()
            ->orderByDesc('version_number')
            ->first();

        $nextVersionNumber = ((int) ($latestVersion?->version_number ?? 0)) + 1;

        $version = SitePageVersion::create([
            'site_page_id' => $page->id,
            'version_number' => $nextVersionNumber,
            'action' => $action,
            'change_summary' => $changeSummary,
            'snapshot' => $snapshot,
            'created_by_user_id' => $actor?->id,
            'created_by_name' => $actor?->name,
            'created_by_email' => $actor?->email,
            'restored_from_version_id' => $restoredFromVersionId,
        ]);

        foreach ($changes as $change) {
            SitePageChangeLog::create([
                'site_page_id' => $page->id,
                'site_page_version_id' => $version->id,
                'site_section_id' => $change['site_section_id'] ?? null,
                'site_section_item_id' => $change['site_section_item_id'] ?? null,
                'section_key' => $change['section_key'] ?? null,
                'item_name' => $change['item_name'] ?? null,
                'entity_type' => $change['entity_type'],
                'action' => $change['action'],
                'field_name' => $change['field_name'] ?? null,
                'summary' => $change['summary'] ?? null,
                'before_state' => $change['before_state'] ?? null,
                'after_state' => $change['after_state'] ?? null,
                'created_by_user_id' => $actor?->id,
                'created_by_name' => $actor?->name,
                'created_by_email' => $actor?->email,
            ]);
        }

        return $version->fresh(['actor', 'restoredFrom']);
    }

    protected function buildChanges(array $beforeSnapshot, array $afterSnapshot): array
    {
        $changes = [];

        $beforePageFields = Arr::only($beforeSnapshot, ['slug', 'name', 'meta_title', 'meta_description', 'theme', 'is_active']);
        $afterPageFields = Arr::only($afterSnapshot, ['slug', 'name', 'meta_title', 'meta_description', 'theme', 'is_active']);

        if ($beforePageFields !== $afterPageFields) {
            $changes[] = [
                'section_key' => null,
                'item_name' => null,
                'site_section_id' => null,
                'site_section_item_id' => null,
                'entity_type' => 'page',
                'action' => 'updated',
                'field_name' => 'page_meta',
                'summary' => 'Se actualizó la configuración general de la página.',
                'before_state' => $beforePageFields,
                'after_state' => $afterPageFields,
            ];
        }

        $beforeSections = collect($beforeSnapshot['sections'] ?? [])->keyBy('key');
        $afterSections = collect($afterSnapshot['sections'] ?? [])->keyBy('key');

        foreach ($beforeSections->keys()->merge($afterSections->keys())->unique()->values() as $sectionKey) {
            $beforeSection = $beforeSections->get($sectionKey);
            $afterSection = $afterSections->get($sectionKey);

            if (! $beforeSection && $afterSection) {
                $changes[] = [
                    'section_key' => $sectionKey,
                    'item_name' => null,
                    'site_section_id' => $afterSection['id'] ?? null,
                    'site_section_item_id' => null,
                    'entity_type' => 'section',
                    'action' => 'created',
                    'field_name' => null,
                    'summary' => "Se creó la sección {$sectionKey}.",
                    'before_state' => null,
                    'after_state' => $afterSection,
                ];
                continue;
            }

            if ($beforeSection && ! $afterSection) {
                $changes[] = [
                    'section_key' => $sectionKey,
                    'item_name' => null,
                    'site_section_id' => $beforeSection['id'] ?? null,
                    'site_section_item_id' => null,
                    'entity_type' => 'section',
                    'action' => 'deleted',
                    'field_name' => null,
                    'summary' => "Se eliminó la sección {$sectionKey}.",
                    'before_state' => $beforeSection,
                    'after_state' => null,
                ];
                continue;
            }

            $beforeSectionData = Arr::except($beforeSection, ['items']);
            $afterSectionData = Arr::except($afterSection, ['items']);

            if ($beforeSectionData !== $afterSectionData) {
                $changes[] = [
                    'section_key' => $sectionKey,
                    'item_name' => null,
                    'site_section_id' => $afterSection['id'] ?? $beforeSection['id'] ?? null,
                    'site_section_item_id' => null,
                    'entity_type' => 'section',
                    'action' => 'updated',
                    'field_name' => 'settings',
                    'summary' => "Se actualizó la configuración de la sección {$sectionKey}.",
                    'before_state' => $beforeSectionData,
                    'after_state' => $afterSectionData,
                ];
            }

            $changes = array_merge($changes, $this->buildItemChanges($sectionKey, $beforeSection, $afterSection));
        }

        return $changes;
    }

    protected function buildItemChanges(string $sectionKey, array $beforeSection, array $afterSection): array
    {
        $changes = [];
        $beforeItems = $this->mapItemsByIdentity($beforeSection['items'] ?? []);
        $afterItems = $this->mapItemsByIdentity($afterSection['items'] ?? []);

        foreach (collect(array_keys($beforeItems))->merge(array_keys($afterItems))->unique()->values() as $identity) {
            $beforeItem = $beforeItems[$identity] ?? null;
            $afterItem = $afterItems[$identity] ?? null;

            if (! $beforeItem && $afterItem) {
                $changes[] = [
                    'section_key' => $sectionKey,
                    'item_name' => $afterItem['data']['title'] ?? $afterItem['data']['label'] ?? $afterItem['name'] ?? null,
                    'site_section_id' => $afterSection['id'] ?? null,
                    'site_section_item_id' => $afterItem['id'] ?? null,
                    'entity_type' => 'item',
                    'action' => 'created',
                    'field_name' => null,
                    'summary' => 'Se agregó un elemento en la sección ' . $sectionKey . '.',
                    'before_state' => null,
                    'after_state' => $afterItem,
                ];
                continue;
            }

            if ($beforeItem && ! $afterItem) {
                $changes[] = [
                    'section_key' => $sectionKey,
                    'item_name' => $beforeItem['data']['title'] ?? $beforeItem['data']['label'] ?? $beforeItem['name'] ?? null,
                    'site_section_id' => $beforeSection['id'] ?? null,
                    'site_section_item_id' => $beforeItem['id'] ?? null,
                    'entity_type' => 'item',
                    'action' => 'deleted',
                    'field_name' => null,
                    'summary' => 'Se eliminó un elemento de la sección ' . $sectionKey . '.',
                    'before_state' => $beforeItem,
                    'after_state' => null,
                ];
                continue;
            }

            if ($beforeItem !== $afterItem) {
                $changes[] = [
                    'section_key' => $sectionKey,
                    'item_name' => $afterItem['data']['title'] ?? $afterItem['data']['label'] ?? $afterItem['name'] ?? null,
                    'site_section_id' => $afterSection['id'] ?? $beforeSection['id'] ?? null,
                    'site_section_item_id' => $afterItem['id'] ?? $beforeItem['id'] ?? null,
                    'entity_type' => 'item',
                    'action' => 'updated',
                    'field_name' => 'data',
                    'summary' => 'Se actualizó un elemento de la sección ' . $sectionKey . '.',
                    'before_state' => $beforeItem,
                    'after_state' => $afterItem,
                ];
            }
        }

        return $changes;
    }

    protected function mapItemsByIdentity(array $items): array
    {
        $mapped = [];

        foreach ($items as $index => $item) {
            $identity = isset($item['id']) && $item['id']
                ? 'id:' . $item['id']
                : 'fallback:' . md5(json_encode([
                    $item['name'] ?? null,
                    $item['type'] ?? null,
                    $item['sort_order'] ?? $index,
                    $item['data'] ?? [],
                ]));

            $mapped[$identity] = $item;
        }

        return $mapped;
    }
}
