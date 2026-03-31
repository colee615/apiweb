<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Models\SitePageChangeLog;
use App\Models\SitePageVersion;
use App\Services\SitePageEditor;
use App\Support\ContentSecurity;
use Illuminate\Support\Collection;
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
            'versions' => $page->versions()->with(['actor', 'changeLogs'])->take(12)->get(),
            'historyData' => $this->buildHistoryData($page),
        ]);
    }

    public function update(Request $request, SitePage $page): RedirectResponse
    {
        $messages = [
            'required' => 'Completa el campo :attribute.',
            'string' => 'El campo :attribute debe ser texto.',
            'integer' => 'El campo :attribute debe ser un numero entero.',
            'boolean' => 'El campo :attribute debe ser un valor valido.',
            'file' => 'Debes subir un archivo valido en :attribute.',
            'image' => 'El archivo de :attribute debe ser una imagen valida.',
            'mimes' => 'La :attribute debe estar en uno de estos formatos: :values.',
            'mimetypes' => 'El archivo de :attribute tiene un formato no permitido.',
            'max.string' => 'El campo :attribute no debe superar los :max caracteres.',
            'max.numeric' => 'El campo :attribute no puede ser mayor a :max.',
            'max.file' => 'La :attribute es demasiado pesada. Reduce el archivo antes de subirlo.',
            'min.numeric' => 'El campo :attribute no puede ser menor a :min.',
            'unique' => 'La :attribute ya esta en uso. Usa otro valor.',
            'theme.logo_file.max' => 'El logo del sitio debe pesar como maximo 15 MB.',
            'announcement_modal.poster_file.max' => 'La imagen principal del popup debe pesar como maximo 15 MB.',
            'announcement_modal.items.*.poster_file.max' => 'Cada imagen del popup debe pesar como maximo 15 MB.',
            'app_banner.background_file.max' => 'La imagen base del banner debe pesar como maximo 15 MB.',
            'app_banner.items.*.image_file.max' => 'Cada imagen del banner debe pesar como maximo 15 MB.',
            'services.items.*.iconImage_file.max' => 'Cada icono del servicio debe pesar como maximo 15 MB.',
            'market.items.*.image_file.max' => 'Cada imagen del producto debe pesar como maximo 15 MB.',
            'hero.media.*.media_file.max' => 'Cada imagen o video del carrusel principal debe pesar como maximo 15 MB.',
            'hero.media.*.poster_file.max' => 'Cada portada del carrusel principal debe pesar como maximo 15 MB.',
        ];

        $attributes = [
            'slug' => 'URL interna',
            'name' => 'nombre de la pagina',
            'meta_title' => 'titulo SEO',
            'meta_description' => 'descripcion SEO',
            'is_active' => 'estado de publicacion',
            'change_summary' => 'resumen del cambio',
            'theme.logo_file' => 'logo del sitio',
            'announcement_modal.poster_file' => 'imagen principal del popup',
            'announcement_modal.items.*.poster_file' => 'imagen de un popup',
            'app_banner.background_file' => 'imagen base del banner',
            'app_banner.items.*.image_file' => 'imagen de un slide del banner',
            'app_banner.items.*.duration_seconds' => 'duracion de un slide del banner',
            'services.items.*.iconImage_file' => 'icono del servicio',
            'market.items.*.image_file' => 'imagen del producto',
            'hero.media.*.media_file' => 'archivo del carrusel principal',
            'hero.media.*.poster_file' => 'portada del video principal',
            'hero.media.*.duration_seconds' => 'duracion de un elemento del carrusel principal',
        ];

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
            'change_summary' => ['nullable', 'string', 'max:1000'],
            'theme.logo_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'announcement_modal.poster_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'announcement_modal.items.*.poster_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'app_banner.background_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'app_banner.items.*.image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'app_banner.items.*.duration_seconds' => ['nullable', 'integer', 'min:1', 'max:300'],
            'services.items.*.iconImage_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'market.items.*.image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'hero.media.*.media_file' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml,video/mp4,video/webm', 'max:15360'],
            'hero.media.*.poster_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'hero.media.*.duration_seconds' => ['nullable', 'integer', 'min:1', 'max:300'],
        ], $messages, $attributes);

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

        $this->editor->updatePage(
            $page,
            $payload,
            $request->attributes->get('admin_user'),
            ['change_summary' => $data['change_summary'] ?? null]
        );

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Diseño y contenidos actualizados correctamente.');
    }

    public function restore(Request $request, SitePage $page, SitePageVersion $version): RedirectResponse
    {
        abort_unless($version->site_page_id === $page->id, 404);

        $data = $request->validate([
            'change_summary' => ['nullable', 'string', 'max:1000'],
        ], [
            'max.string' => 'El resumen del cambio no debe superar los :max caracteres.',
        ], [
            'change_summary' => 'resumen del cambio',
        ]);

        $this->editor->restoreVersion(
            $page,
            $version,
            $request->attributes->get('admin_user'),
            $data['change_summary'] ?? null
        );

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Se restauró la versión seleccionada correctamente.');
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
                'items' => $this->sectionItems($page, 'announcement_modal'),
            ],
            'header' => [
                'settings' => $this->sectionSettings($page, 'header', [
                    'language_primary' => 'Español',
                    'language_secondary' => 'English',
                    'accessibility_label' => 'Accesibilidad',
                    'help_label' => 'Ayuda / Contacto',
                    'login_label' => 'Iniciar sesión',
                    'search_placeholder' => 'Buscar...',
                ]),
                'links' => $this->sectionItems($page, 'header'),
            ],
            'hero' => [
                'settings' => $this->sectionSettings($page, 'hero', [
                    'title' => 'Conectando Bolivia|con el Mundo',
                    'subtitle' => 'Servicio postal confiable, rápido y seguro',
                    'tracking_title' => 'Rastrea tu envío',
                    'tracking_text' => 'Ingresa tu código de seguimiento para conocer el estado de tu paquete',
                    'tracking_label' => 'Código de seguimiento',
                    'tracking_placeholder' => 'Ej: PE123456789',
                    'tracking_button' => 'Buscar',
                ]),
                'media' => $this->sectionItems($page, 'hero'),
            ],
            'services' => [
                'settings' => $this->sectionSettings($page, 'services', [
                    'title' => 'Servicios Destacados',
                    'subtitle' => 'Soluciones integrales para todas tus necesidades de envío',
                    'kicker' => 'Servicio destacado',
                ]),
                'items' => $this->sectionItems($page, 'services'),
            ],
            'status' => [
                'settings' => $this->sectionSettings($page, 'status', [
                    'title' => 'Estado de tu envío',
                    'subtitle' => 'Ingresa tu número de seguimiento para conocer el estado de tu paquete',
                    'placeholder' => 'Ej: PE123456789',
                    'button_label' => 'Rastrear',
                ]),
            ],
            'tools' => [
                'settings' => $this->sectionSettings($page, 'tools', []),
                'items' => $this->sectionItems($page, 'tools') ?: $this->defaultToolOfficeItems(),
            ],
            'app_banner' => [
                'settings' => $this->sectionSettings($page, 'app_banner', [
                    'background_image' => '',
                ]),
                'items' => $this->sectionItems($page, 'app_banner') ?: $this->defaultAppBannerItems($page),
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
        $statusSettings = data_get($this->buildEditorData($page), 'status.settings', []);
        $announcementItems = $this->mapRepeaterItems(data_get($form, 'announcement_modal.items', []), 'announcement_slide', function ($item) {
            return [
                'title' => $item['title'] ?? '',
                'poster_image' => $this->storeRepeaterImage($item, 'poster_file', 'poster_image', 'cms/announcement'),
                'poster_alt' => $item['poster_alt'] ?? 'Comunicado institucional',
                'poster_title' => $item['poster_title'] ?? '',
                'poster_caption' => $item['poster_caption'] ?? '',
            ];
        });

        $announcementSettings = [
            'enabled' => $request->boolean('announcement_modal.enabled'),
            'show_once' => $request->boolean('announcement_modal.show_once'),
            'storage_key' => $request->input('announcement_modal.storage_key') ?: 'cb-home-announcement',
            'poster_image' => $this->storeUploadedImage($request, 'announcement_modal.poster_file', $request->input('announcement_modal.poster_image'), 'cms/announcement'),
            'poster_alt' => $request->input('announcement_modal.poster_alt'),
            'poster_title' => $request->input('announcement_modal.poster_title'),
            'poster_caption' => $request->input('announcement_modal.poster_caption'),
        ];

        if (empty($announcementItems)) {
            $announcementSettings['poster_image'] = '';
            $announcementSettings['poster_title'] = '';
            $announcementSettings['poster_caption'] = '';
        }

        return [
            $this->makeSectionPayload($page, 'announcement_modal', 'Popup de inicio', 'announcement_modal', 0, $announcementSettings, $announcementItems),

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
                    'url' => ContentSecurity::sanitizeLinkUrl($item['url'] ?? '#') ?? '#',
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

            $this->makeSectionPayload($page, 'status', 'Estado de envío', 'tracking_form', 4, [
                'title' => $request->input('status.title', $statusSettings['title'] ?? 'Estado de tu envío'),
                'subtitle' => $request->input('status.subtitle', $statusSettings['subtitle'] ?? 'Ingresa tu número de seguimiento para conocer el estado de tu paquete'),
                'placeholder' => $request->input('status.placeholder', $statusSettings['placeholder'] ?? 'Ej: PE123456789'),
                'button_label' => $request->input('status.button_label', $statusSettings['button_label'] ?? 'Rastrear'),
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
            ], $this->mapRepeaterItems(data_get($form, 'tools.items', []), 'office', function ($item) {
                return [
                    'title' => $item['title'] ?? '',
                    'name' => $item['name'] ?? '',
                    'dept' => strtoupper(trim((string) ($item['dept'] ?? ''))),
                    'address' => $item['address'] ?? '',
                    'hours' => $item['hours'] ?? '',
                    'weekday_hours' => $item['weekday_hours'] ?? '',
                    'saturday_hours' => $item['saturday_hours'] ?? '',
                    'phone' => $item['phone'] ?? '',
                    'left' => $item['left'] ?? '',
                    'top' => $item['top'] ?? '',
                    'maps_url' => ContentSecurity::sanitizeLinkUrl($item['maps_url'] ?? '#') ?? '#',
                ];
            })),

            $this->makeSectionPayload($page, 'app_banner', 'Banner App', 'app_banner', 6, [
                'title' => $request->input('app_banner.title'),
                'text' => $request->input('app_banner.text'),
                'app_store_label' => $request->input('app_banner.app_store_label'),
                'play_store_label' => $request->input('app_banner.play_store_label'),
                'app_store_url' => ContentSecurity::sanitizeLinkUrl($request->input('app_banner.app_store_url')) ?? '#',
                'play_store_url' => ContentSecurity::sanitizeLinkUrl($request->input('app_banner.play_store_url')) ?? '#',
                'background_image' => $this->storeUploadedImage($request, 'app_banner.background_file', $request->input('app_banner.background_image'), 'cms/app-banner'),
            ], $this->mapRepeaterItems(data_get($form, 'app_banner.items', []), 'app_banner_slide', function ($item) {
                return [
                    'title' => $item['title'] ?? '',
                    'image' => $this->storeRepeaterImage($item, 'image_file', 'image', 'cms/app-banner'),
                    'duration_seconds' => max(1, min(300, (int) ($item['duration_seconds'] ?? 5))),
                ];
            })),

            $this->makeSectionPayload($page, 'market', 'Market', 'product_grid', 7, [
                'title' => $request->input('market.title'),
                'subtitle' => $request->input('market.subtitle'),
                'view_all_label' => $request->input('market.view_all_label'),
                'view_all_url' => ContentSecurity::sanitizeLinkUrl($request->input('market.view_all_url')) ?? '#',
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

            $this->makeSectionPayload($page, 'footer', 'Pie de página', 'footer', 8, [
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
                        'duration_seconds' => max(1, min(300, (int) ($item['duration_seconds'] ?? 5))),
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
                    'url' => ContentSecurity::sanitizeLinkUrl($item['url'] ?? '#') ?? '#',
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

    protected function normalizeAssetUrl(?string $value): ?string
    {
        return ContentSecurity::normalizeAssetUrl($value);
    }

    protected function defaultToolOfficeItems(): array
    {
        return [
            ['title' => 'Oficina Correos Cobija', 'name' => 'Cobija', 'dept' => 'BON', 'address' => 'Av. 9 de Febrero, Cobija, Pando', 'hours' => 'Lun a Vie, 08:30 a 16:30', 'weekday_hours' => '08:30 a 16:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 3 8420001', 'left' => '33.2%', 'top' => '16.5%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-11.0267,-68.7692'],
            ['title' => 'Oficina Central La Paz', 'name' => 'La Paz', 'dept' => 'BOL', 'address' => 'Av. Mariscal Santa Cruz, La Paz', 'hours' => 'Lun a Vie, 08:00 a 18:00', 'weekday_hours' => '08:00 a 18:00', 'saturday_hours' => '09:00 a 13:00', 'phone' => '+591 2 2312121', 'left' => '29.6%', 'top' => '46%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-16.4957,-68.1336'],
            ['title' => 'Oficina Correos Trinidad', 'name' => 'Trinidad', 'dept' => 'BOB', 'address' => 'Zona Central, Trinidad, Beni', 'hours' => 'Lun a Vie, 08:30 a 16:30', 'weekday_hours' => '08:30 a 16:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 3 4622001', 'left' => '43.8%', 'top' => '35.5%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-14.8333,-64.9'],
            ['title' => 'Oficina Correos Oruro', 'name' => 'Oruro', 'dept' => 'BOO', 'address' => 'Calle La Plata, Oruro', 'hours' => 'Lun a Vie, 08:30 a 16:30', 'weekday_hours' => '08:30 a 16:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 2 5277001', 'left' => '31.8%', 'top' => '67.2%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-17.9647,-67.106'],
            ['title' => 'Oficina Correos Cochabamba', 'name' => 'Cochabamba', 'dept' => 'BOC', 'address' => 'Av. Ayacucho, Cochabamba', 'hours' => 'Lun a Vie, 08:00 a 17:30', 'weekday_hours' => '08:00 a 17:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 4 4528001', 'left' => '41.8%', 'top' => '58.5%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-17.3895,-66.1568'],
            ['title' => 'Oficina Correos Santa Cruz', 'name' => 'Santa Cruz', 'dept' => 'BOS', 'address' => 'Av. Irala, Santa Cruz de la Sierra', 'hours' => 'Lun a Vie, 08:00 a 17:30', 'weekday_hours' => '08:00 a 17:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 3 3366001', 'left' => '59.2%', 'top' => '58%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-17.7833,-63.1821'],
            ['title' => 'Oficina Correos Sucre', 'name' => 'Sucre', 'dept' => 'BOH', 'address' => 'Calle Aniceto Arce, Sucre', 'hours' => 'Lun a Vie, 08:30 a 16:30', 'weekday_hours' => '08:30 a 16:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 4 6459001', 'left' => '46.6%', 'top' => '75.2%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-19.047,-65.2595'],
            ['title' => 'Oficina Correos Potosi', 'name' => 'Potosi', 'dept' => 'BOP', 'address' => 'Zona Central, Potosi', 'hours' => 'Lun a Vie, 08:30 a 16:30', 'weekday_hours' => '08:30 a 16:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 2 6229001', 'left' => '35.7%', 'top' => '81.2%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-19.5723,-65.755'],
            ['title' => 'Oficina Correos Tarija', 'name' => 'Tarija', 'dept' => 'BOT', 'address' => 'Calle General Trigo, Tarija', 'hours' => 'Lun a Vie, 08:30 a 16:30', 'weekday_hours' => '08:30 a 16:30', 'saturday_hours' => '09:00 a 12:30', 'phone' => '+591 4 6648001', 'left' => '47.7%', 'top' => '87.5%', 'maps_url' => 'https://www.google.com/maps/search/?api=1&query=-21.5355,-64.7296'],
        ];
    }

    protected function defaultAnnouncementModalItems(SitePage $page): array
    {
        $settings = $page->sections->firstWhere('key', 'announcement_modal')?->settings ?? [];
        $posterImage = $this->normalizeAssetUrl($settings['poster_image'] ?? '');

        if (! filled($posterImage)) {
            return [];
        }

        return [[
            'title' => $settings['poster_title'] ?: 'Popup principal',
            'poster_image' => $posterImage,
            'poster_alt' => $settings['poster_alt'] ?? 'Comunicado institucional',
            'poster_title' => $settings['poster_title'] ?? '',
            'poster_caption' => $settings['poster_caption'] ?? '',
        ]];
    }

    protected function defaultAppBannerItems(SitePage $page): array
    {
        $settings = $page->sections->firstWhere('key', 'app_banner')?->settings ?? [];
        $backgroundImage = $this->normalizeAssetUrl($settings['background_image'] ?? '');

        if (! filled($backgroundImage)) {
            return [];
        }

        return [[
            'title' => 'Banner principal',
            'image' => $backgroundImage,
            'duration_seconds' => 5,
        ]];
    }

    protected function buildHistoryData(SitePage $page): array
    {
        $changeLogs = $page->changeLogs()
            ->with(['actor', 'version'])
            ->orderByDesc('created_at')
            ->get();

        $sectionLabels = collect([
            'general' => 'General',
            'announcement_modal' => 'Popup de inicio',
        ])->merge(
            $page->sections
                ->sortBy('sort_order')
                ->mapWithKeys(fn ($section) => [$section->key => $section->name ?: ucfirst(str_replace('_', ' ', $section->key))])
        );

        $historySections = $sectionLabels->map(function ($label, $key) use ($changeLogs) {
            $logs = $key === 'general'
                ? $changeLogs->filter(fn (SitePageChangeLog $log) => empty($log->section_key))
                : $changeLogs->filter(fn (SitePageChangeLog $log) => $log->section_key === $key);

            $versions = $logs
                ->pluck('version')
                ->filter()
                ->unique('id')
                ->sortByDesc('version_number')
                ->values();

            return [
                'key' => $key,
                'label' => $label,
                'count' => $logs->count(),
                'logs' => $logs->values(),
                'versions' => $versions,
            ];
        })->values();

        return [
            'total_changes' => $changeLogs->count(),
            'history_sections' => $historySections,
            'latest_changes' => $changeLogs->take(20)->values(),
        ];
    }
}
