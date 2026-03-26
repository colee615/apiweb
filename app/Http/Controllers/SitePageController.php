<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Models\SitePageChangeLog;
use App\Models\SiteSection;
use App\Models\SiteSectionItem;
use App\Models\SitePageVersion;
use App\Services\SitePageEditor;
use App\Services\SitePagePayloadBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SitePageController extends Controller
{
    public function __construct(
        protected SitePageEditor $editor,
        protected SitePagePayloadBuilder $payloadBuilder
    ) {
    }

    public function index(): JsonResponse
    {
        $pages = SitePage::withCount('sections')
            ->orderBy('name')
            ->get();

        return response()->json($pages);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:120', 'unique:site_pages,slug'],
            'name' => ['required', 'string', 'max:160'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'theme' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        $page = SitePage::create([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'theme' => $data['theme'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $actor = $this->resolveActor($request);

        $this->editor->createInitialVersion($page, $actor, $data['change_summary'] ?? null);

        return response()->json($this->buildPagePayload($page->fresh('sections.items')), 201);
    }

    public function show(SitePage $page): JsonResponse
    {
        $page->load([
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.items' => function ($query) {
                $query->orderBy('sort_order');
            },
        ]);

        return response()->json($this->buildPagePayload($page));
    }

    public function publicShow(string $slug): JsonResponse
    {
        $page = SitePage::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'sections' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order');
                },
                'sections.items' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order');
                },
            ])
            ->firstOrFail();

        return response()->json($this->buildPagePayload($page));
    }

    public function updateEditor(Request $request, SitePage $page): JsonResponse
    {
        $data = $request->validate([
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                Rule::unique('site_pages', 'slug')->ignore($page->id),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'theme' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'sections' => ['nullable', 'array'],
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        $page = $this->editor->updatePage($page, $data, $this->resolveActor($request), [
            'change_summary' => $data['change_summary'] ?? null,
        ]);

        return response()->json($this->buildPagePayload($page));
    }

    public function versions(SitePage $page): JsonResponse
    {
        $versions = $page->versions()
            ->with(['actor', 'restoredFrom'])
            ->get()
            ->map(fn (SitePageVersion $version) => $this->buildVersionPayload($version));

        return response()->json($versions);
    }

    public function showVersion(SitePage $page, SitePageVersion $version): JsonResponse
    {
        abort_unless($version->site_page_id === $page->id, 404);

        $version->load(['actor', 'restoredFrom', 'changeLogs']);

        return response()->json($this->buildVersionPayload($version, true));
    }

    public function changes(Request $request, SitePage $page): JsonResponse
    {
        $query = $page->changeLogs()->with(['actor', 'version']);

        if ($request->filled('section_key')) {
            $query->where('section_key', $request->string('section_key'));
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->string('entity_type'));
        }

        $changes = $query->get()->map(function (SitePageChangeLog $change) {
            return [
                'id' => $change->id,
                'section_key' => $change->section_key,
                'item_name' => $change->item_name,
                'entity_type' => $change->entity_type,
                'action' => $change->action,
                'field_name' => $change->field_name,
                'summary' => $change->summary,
                'before_state' => $change->before_state,
                'after_state' => $change->after_state,
                'version' => $change->version ? [
                    'id' => $change->version->id,
                    'version_number' => $change->version->version_number,
                    'action' => $change->version->action,
                ] : null,
                'actor' => $this->buildActorPayload($change->actor, $change->created_by_name, $change->created_by_email),
                'created_at' => optional($change->created_at)->toIso8601String(),
            ];
        });

        return response()->json($changes);
    }

    public function restoreVersion(Request $request, SitePage $page, SitePageVersion $version): JsonResponse
    {
        abort_unless($version->site_page_id === $page->id, 404);

        $data = $request->validate([
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ]);

        $page = $this->editor->restoreVersion(
            $page,
            $version,
            $this->resolveActor($request),
            $data['change_summary'] ?? null
        );

        return response()->json($this->buildPagePayload($page));
    }

    public function destroy(SitePage $page): JsonResponse
    {
        $page->delete();

        return response()->json(['message' => 'Pagina eliminada']);
    }

    protected function buildPagePayload(SitePage $page): array
    {
        return $this->payloadBuilder->build($page);
    }

    protected function buildVersionPayload(SitePageVersion $version, bool $includeChanges = false): array
    {
        return [
            'id' => $version->id,
            'version_number' => $version->version_number,
            'action' => $version->action,
            'change_summary' => $version->change_summary,
            'restored_from_version_id' => $version->restored_from_version_id,
            'actor' => $this->buildActorPayload($version->actor, $version->created_by_name, $version->created_by_email),
            'created_at' => optional($version->created_at)->toIso8601String(),
            'snapshot' => $version->snapshot,
            'changes' => $includeChanges
                ? $version->changeLogs->map(fn (SitePageChangeLog $change) => [
                    'id' => $change->id,
                    'section_key' => $change->section_key,
                    'item_name' => $change->item_name,
                    'entity_type' => $change->entity_type,
                    'action' => $change->action,
                    'field_name' => $change->field_name,
                    'summary' => $change->summary,
                    'before_state' => $change->before_state,
                    'after_state' => $change->after_state,
                    'created_at' => optional($change->created_at)->toIso8601String(),
                ])->values()
                : [],
        ];
    }

    protected function buildActorPayload($actor, ?string $fallbackName, ?string $fallbackEmail): array
    {
        return [
            'id' => $actor?->id,
            'name' => $actor?->name ?? $fallbackName,
            'email' => $actor?->email ?? $fallbackEmail,
        ];
    }

    protected function resolveActor(Request $request)
    {
        return $request->user('api_users') ?: $request->attributes->get('admin_user');
    }
}
