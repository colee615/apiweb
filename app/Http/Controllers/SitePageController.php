<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Models\SiteSection;
use App\Models\SiteSectionItem;
use App\Services\SitePageEditor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SitePageController extends Controller
{
    public function __construct(
        protected SitePageEditor $editor
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
        ]);

        $page = SitePage::create([
            'slug' => $data['slug'],
            'name' => $data['name'],
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'theme' => $data['theme'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

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
        ]);

        $page = $this->editor->updatePage($page, $data);

        return response()->json($this->buildPagePayload($page));
    }

    public function destroy(SitePage $page): JsonResponse
    {
        $page->delete();

        return response()->json(['message' => 'Pagina eliminada']);
    }

    protected function buildPagePayload(SitePage $page): array
    {
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
                })->values(),
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
            'sections' => $sections,
            'section_map' => $sections->keyBy('key'),
        ];
    }

    protected function normalizeAssetFields(array $data): array
    {
        $assetKeys = ['logo_url', 'background_image', 'iconImage', 'image', 'src', 'poster'];

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

    protected function normalizeAssetUrl(?string $value): ?string
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
