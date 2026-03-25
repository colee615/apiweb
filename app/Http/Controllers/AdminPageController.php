<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Services\SitePageEditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPageController extends Controller
{
    public function __construct(
        protected SitePageEditor $editor
    ) {
    }

    public function index(): View
    {
        $pages = SitePage::withCount('sections')
            ->orderBy('name')
            ->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(SitePage $page): View
    {
        $page->load([
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.items' => function ($query) {
                $query->orderBy('sort_order');
            },
        ]);

        return view('admin.pages.edit', [
            'page' => $page,
            'editorData' => $this->buildEditorData($page),
        ]);
    }

    public function update(Request $request, SitePage $page): RedirectResponse
    {
        $data = $request->validate([
            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('site_pages', 'slug')->ignore($page->id),
            ],
            'name' => ['required', 'string', 'max:160'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'slug' => $data['slug'],
            'name' => $data['name'],
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'theme' => [
                'logo_url' => $this->storeUploadedImage($request, 'theme.logo_file', $request->input('theme.logo_url'), 'cms/theme'),
                'primary_color' => $request->input('theme.primary_color'),
                'secondary_color' => $request->input('theme.secondary_color'),
                'accent_color' => $request->input('theme.accent_color'),
            ],
            'sections' => $this->buildSectionsPayload($request, $page),
        ];

        $this->editor->updatePage($page, $payload);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Diseno y contenidos actualizados correctamente.');
    }

    protected function buildEditorData(SitePage $page): array
    {
        $theme = $page->theme ?? [];

        return [
            'theme' => [
                'logo_url' => $this->normalizeAssetUrl($theme['logo_url'] ?? ''),
                'primary_color' => $theme['primary_color'] ?? '#20539a',
                'secondary_color' => $theme['secondary_color'] ?? '#102542',
                'accent_color' => $theme['accent_color'] ?? '#f3b53f',
            ],
            'announcement_modal' => [
                'settings' => $this->sectionSettings($page, 'announcement_modal', [
                    'enabled' => false,
                    'show_once' => false,
                    'storage_key' => 'cb-home-announcement',
                    'poster_image' => '',
                    'poster_alt' => 'Comunicado institucional',
                    'poster_title' => '',
                    'poster_caption' => '',
                ]),
            ],
            'header' => [
                'settings' => $this->sectionSettings($page, 'header', [
                    'language_primary' => 'Espanol',
                    'language_secondary' => 'English',
                    'accessibility_label' => 'Accesibilidad',
                    'help_label' => 'Ayuda / Contacto',
                    'login_label' => 'Iniciar Sesion',
                    'search_placeholder' => 'Buscar...',
                ]),
                'links' => $this->sectionItems($page, 'header'),
            ],
            'hero' => [
                'settings' => $this->sectionSettings($page, 'hero', [
                    'title' => 'Conectando Bolivia|con el Mundo',
                    'subtitle' => 'Servicio postal confiable, rapido y seguro',
                    'tracking_title' => 'Rastrea tu Envio',
                    'tracking_text' => 'Ingresa tu codigo de seguimiento para conocer el estado de tu paquete',
                    'tracking_label' => 'Codigo de seguimiento',
                    'tracking_placeholder' => 'Ej: PE123456789',
                    'tracking_button' => 'Buscar',
                ]),
                'media' => $this->sectionItems($page, 'hero'),
            ],
            'services' => [
                'settings' => $this->sectionSettings($page, 'services', [
                    'title' => 'Servicios Destacados',
                    'subtitle' => 'Soluciones integrales para todas tus necesidades de envio',
                    'kicker' => 'Servicio destacado',
                ]),
                'items' => $this->sectionItems($page, 'services'),
            ],
            'status' => [
                'settings' => $this->sectionSettings($page, 'status', [
                    'title' => 'Estado de tu Envio',
                    'subtitle' => 'Ingresa tu numero de seguimiento para conocer el estado de tu paquete',
                    'placeholder' => 'Ej: PE123456789',
                    'button_label' => 'Rastrear',
                ]),
            ],
            'tools' => [
                'settings' => $this->sectionSettings($page, 'tools', []),
            ],
            'app_banner' => [
                'settings' => $this->sectionSettings($page, 'app_banner', [
                    'background_image' => '',
                ]),
            ],
            'market' => [
                'settings' => $this->sectionSettings($page, 'market', []),
                'items' => $this->sectionItems($page, 'market'),
            ],
            'footer' => [
                'settings' => $this->sectionSettings($page, 'footer', []),
                'help_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'help')),
                'company_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'company')),
                'social_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'social')),
            ],
        ];
    }

    protected function buildSectionsPayload(Request $request, SitePage $page): array
    {
        $form = $request->all();

        return [
            $this->makeSectionPayload($page, 'announcement_modal', 'Popup de inicio', 'announcement_modal', 0, [
                'enabled' => $request->boolean('announcement_modal.enabled'),
                'show_once' => $request->boolean('announcement_modal.show_once'),
                'storage_key' => $request->input('announcement_modal.storage_key') ?: 'cb-home-announcement',
                'poster_image' => $this->storeUploadedImage($request, 'announcement_modal.poster_file', $request->input('announcement_modal.poster_image'), 'cms/announcement'),
                'poster_alt' => $request->input('announcement_modal.poster_alt'),
                'poster_title' => $request->input('announcement_modal.poster_title'),
                'poster_caption' => $request->input('announcement_modal.poster_caption'),
            ]),

            $this->makeSectionPayload($page, 'header', 'Encabezado', 'header', 1, [
                'language_primary' => $request->input('header.language_primary'),
                'language_secondary' => $request->input('header.language_secondary'),
                'accessibility_label' => $request->input('header.accessibility_label'),
                'help_label' => $request->input('header.help_label'),
                'login_label' => $request->input('header.login_label'),
                'search_placeholder' => $request->input('header.search_placeholder'),
            ], $this->mapRepeaterItems(data_get($form, 'header.links', []), 'nav_link', function ($item) {
                return [
                    'label' => $item['label'] ?? '',
                    'url' => $item['url'] ?? '#',
                ];
            })),

            $this->makeSectionPayload($page, 'hero', 'Hero', 'hero', 2, [
                'title' => $request->input('hero.title'),
                'subtitle' => $request->input('hero.subtitle'),
                'tracking_title' => $request->input('hero.tracking_title'),
                'tracking_text' => $request->input('hero.tracking_text'),
                'tracking_label' => $request->input('hero.tracking_label'),
                'tracking_placeholder' => $request->input('hero.tracking_placeholder'),
                'tracking_button' => $request->input('hero.tracking_button'),
            ], $this->mapHeroMediaItems(data_get($form, 'hero.media', []))),

            $this->makeSectionPayload($page, 'services', 'Servicios', 'service_grid', 3, [
                'title' => $request->input('services.title'),
                'subtitle' => $request->input('services.subtitle'),
                'kicker' => $request->input('services.kicker'),
            ], $this->mapRepeaterItems(data_get($form, 'services.items', []), 'service', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'iconImage' => $this->storeRepeaterImage($item, 'iconImage_file', 'iconImage', 'cms/services'),
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'status', 'Estado de envio', 'tracking_form', 4, [
                'title' => $request->input('status.title', data_get($this->buildEditorData($page), 'status.settings.title')),
                'subtitle' => $request->input('status.subtitle', data_get($this->buildEditorData($page), 'status.settings.subtitle')),
                'placeholder' => $request->input('status.placeholder', data_get($this->buildEditorData($page), 'status.settings.placeholder')),
                'button_label' => $request->input('status.button_label', data_get($this->buildEditorData($page), 'status.settings.button_label')),
            ]),

            $this->makeSectionPayload($page, 'tools', 'Herramientas', 'tools', 5, [
                'map_title' => $request->input('tools.map_title'),
                'map_text' => $request->input('tools.map_text'),
                'map_button_label' => $request->input('tools.map_button_label'),
                'calculator_title' => $request->input('tools.calculator_title'),
                'calculator_text' => $request->input('tools.calculator_text'),
                'origin_label' => $request->input('tools.origin_label'),
                'origin_placeholder' => $request->input('tools.origin_placeholder'),
                'destination_label' => $request->input('tools.destination_label'),
                'destination_placeholder' => $request->input('tools.destination_placeholder'),
                'weight_label' => $request->input('tools.weight_label'),
                'weight_placeholder' => $request->input('tools.weight_placeholder'),
                'calculate_button_label' => $request->input('tools.calculate_button_label'),
            ]),

            $this->makeSectionPayload($page, 'app_banner', 'Banner App', 'app_banner', 6, [
                'title' => $request->input('app_banner.title'),
                'text' => $request->input('app_banner.text'),
                'app_store_label' => $request->input('app_banner.app_store_label'),
                'play_store_label' => $request->input('app_banner.play_store_label'),
                'app_store_url' => $request->input('app_banner.app_store_url'),
                'play_store_url' => $request->input('app_banner.play_store_url'),
                'background_image' => $this->storeUploadedImage($request, 'app_banner.background_file', $request->input('app_banner.background_image'), 'cms/app-banner'),
            ]),

            $this->makeSectionPayload($page, 'market', 'Market', 'product_grid', 7, [
                'title' => $request->input('market.title'),
                'subtitle' => $request->input('market.subtitle'),
                'view_all_label' => $request->input('market.view_all_label'),
                'view_all_url' => $request->input('market.view_all_url'),
            ], $this->mapRepeaterItems(data_get($form, 'market.items', []), 'product', function ($item) {
                return [
                    'title' => $item['title'] ?? '',
                    'price' => $item['price'] ?? '',
                    'image' => $this->storeRepeaterImage($item, 'image_file', 'image', 'cms/products'),
                    'year' => $item['year'] ?? '',
                    'series' => $item['series'] ?? '',
                    'description' => $item['description'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'footer', 'Pie de pagina', 'footer', 8, [
                'help_title' => $request->input('footer.help_title'),
                'company_title' => $request->input('footer.company_title'),
                'contact_title' => $request->input('footer.contact_title'),
                'social_title' => $request->input('footer.social_title'),
                'social_text' => $request->input('footer.social_text'),
                'address' => trim(($request->input('footer.address_line_1') ?? '') . '|' . ($request->input('footer.address_line_2') ?? ''), '|'),
                'phone' => trim(($request->input('footer.phone_line_1') ?? '') . '|' . ($request->input('footer.phone_line_2') ?? ''), '|'),
                'email' => $request->input('footer.email'),
                'copyright' => $request->input('footer.copyright'),
                'legal_text' => $request->input('footer.legal_text'),
            ], array_merge(
                $this->mapFooterLinks(data_get($form, 'footer.help_links', []), 'help', 'help_link'),
                $this->mapFooterLinks(data_get($form, 'footer.company_links', []), 'company', 'company_link'),
                $this->mapFooterLinks(data_get($form, 'footer.social_links', []), 'social', 'social_link', true),
            )),
        ];
    }

    protected function makeSectionPayload(
        SitePage $page,
        string $key,
        string $name,
        string $type,
        int $sortOrder,
        array $settings,
        array $items = []
    ): array {
        $section = $page->sections->firstWhere('key', $key);

        return [
            'id' => $section?->id,
            'key' => $key,
            'name' => $name,
            'type' => $type,
            'settings' => $settings,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'items' => $items,
        ];
    }

    protected function mapRepeaterItems(array $items, string $type, callable $dataMapper): array
    {
        return collect($items)
            ->filter(fn ($item) => filled($item['title'] ?? $item['label'] ?? $item['name'] ?? null))
            ->values()
            ->map(function ($item, $index) use ($type, $dataMapper) {
                return [
                    'id' => $item['id'] ?? null,
                    'name' => $item['title'] ?? $item['label'] ?? $item['name'] ?? null,
                    'type' => $type,
                    'sort_order' => $index,
                    'is_active' => true,
                    'data' => $dataMapper($item),
                ];
            })
            ->all();
    }

    protected function mapHeroMediaItems(array $items): array
    {
        return collect($items)
            ->filter(function ($item) {
                return filled($item['title'] ?? null)
                    || filled($item['src'] ?? null)
                    || (($item['media_file'] ?? null) instanceof \Illuminate\Http\UploadedFile);
            })
            ->values()
            ->map(function ($item, $index) {
                return [
                    'id' => $item['id'] ?? null,
                    'name' => $item['title'] ?? ('Slide ' . ($index + 1)),
                    'type' => 'hero_media',
                    'sort_order' => $index,
                    'is_active' => true,
                    'data' => [
                        'title' => $item['title'] ?? ('Slide ' . ($index + 1)),
                        'media_type' => $item['media_type'] ?? 'image',
                        'src' => $this->storeRepeaterAsset($item, 'media_file', 'src', 'cms/hero'),
                        'poster' => $this->storeRepeaterAsset($item, 'poster_file', 'poster', 'cms/hero'),
                    ],
                ];
            })
            ->all();
    }

    protected function storeUploadedImage(Request $request, string $fileKey, ?string $fallbackUrl, string $directory): ?string
    {
        if (! $request->hasFile($fileKey)) {
            return $this->normalizeAssetUrl($fallbackUrl);
        }

        $path = $request->file($fileKey)->store($directory, 'public');

        return $this->normalizeAssetUrl(Storage::disk('public')->url($path));
    }

    protected function storeRepeaterImage(array $item, string $fileField, string $urlField, string $directory): ?string
    {
        if (($item[$fileField] ?? null) instanceof \Illuminate\Http\UploadedFile) {
            $path = $item[$fileField]->store($directory, 'public');

            return $this->normalizeAssetUrl(Storage::disk('public')->url($path));
        }

        return $this->normalizeAssetUrl($item[$urlField] ?? '');
    }

    protected function storeRepeaterAsset(array $item, string $fileField, string $urlField, string $directory): ?string
    {
        if (($item[$fileField] ?? null) instanceof \Illuminate\Http\UploadedFile) {
            $path = $item[$fileField]->store($directory, 'public');

            return $this->normalizeAssetUrl(Storage::disk('public')->url($path));
        }

        return $this->normalizeAssetUrl($item[$urlField] ?? '');
    }

    protected function mapFooterLinks(array $items, string $group, string $type, bool $withAria = false): array
    {
        return collect($items)
            ->filter(fn ($item) => filled($item['label'] ?? null))
            ->values()
            ->map(function ($item, $index) use ($group, $type, $withAria) {
                $data = [
                    'group' => $group,
                    'label' => $item['label'] ?? '',
                    'url' => $item['url'] ?? '#',
                ];

                if ($withAria) {
                    $data['aria_label'] = $item['aria_label'] ?? $item['label'] ?? '';
                }

                return [
                    'id' => $item['id'] ?? null,
                    'name' => $item['label'] ?? '',
                    'type' => $type,
                    'sort_order' => $index,
                    'is_active' => true,
                    'data' => $data,
                ];
            })
            ->all();
    }

    protected function sectionSettings(SitePage $page, string $key, array $defaults = []): array
    {
        $settings = $page->sections->firstWhere('key', $key)?->settings ?? [];

        return $this->normalizeAssetFields(array_merge($defaults, $settings));
    }

    protected function sectionItems(SitePage $page, string $key): array
    {
        $section = $page->sections->firstWhere('key', $key);

        if (! $section) {
            return [];
        }

        return $section->items
            ->map(function ($item) {
                return $this->normalizeAssetFields(array_merge([
                    'id' => $item->id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'sort_order' => $item->sort_order,
                ], $item->data ?? []));
            })
            ->values()
            ->all();
    }

    protected function normalizeAssetFields(array $data): array
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
