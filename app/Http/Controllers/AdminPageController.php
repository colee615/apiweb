<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Models\SitePageChangeLog;
use App\Models\SitePageVersion;
use App\Services\SitePageEditor;
use App\Support\ContentSecurity;
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

        if ($this->isAboutPage($page)) {
            return view('admin.pages.edit-about', [
                'page' => $page,
                'editorData' => $this->buildAboutEditorData($page),
                'versions' => $page->versions()->with(['actor', 'changeLogs'])->take(12)->get(),
                'historyData' => $this->buildHistoryData($page),
            ]);
        }

        if ($this->isNewsPage($page)) {
            return view('admin.pages.edit-news', [
                'page' => $page,
                'editorData' => $this->buildNewsEditorData($page),
                'versions' => $page->versions()->with(['actor', 'changeLogs'])->take(12)->get(),
                'historyData' => $this->buildHistoryData($page),
            ]);
        }

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
            'required' => 'Completa el campooooo :attribute.',
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
            'hero_gallery.items.*.media_file.max' => 'Cada imagen o video del carrusel institucional debe pesar como maximo 15 MB.',
            'hero_gallery.items.*.poster_file.max' => 'Cada portada del video institucional debe pesar como maximo 15 MB.',
            'history.items.*.media_file.max' => 'Cada imagen o video de historia debe pesar como maximo 15 MB.',
            'history.items.*.poster_file.max' => 'Cada portada del video de historia debe pesar como maximo 15 MB.',
            'organigram.media_file.max' => 'La imagen o video del organigrama debe pesar como maximo 15 MB.',
            'organigram.poster_file.max' => 'La portada del video del organigrama debe pesar como maximo 15 MB.',
            'featured_story.items.*.media_file.max' => 'Cada imagen o video destacado de noticias debe pesar como maximo 15 MB.',
            'featured_story.items.*.poster_file.max' => 'Cada portada del video destacado debe pesar como maximo 15 MB.',
            'news_grid.items.*.media_file.max' => 'Cada imagen o video de noticia debe pesar como maximo 15 MB.',
            'news_grid.items.*.poster_file.max' => 'Cada portada del video de noticia debe pesar como maximo 15 MB.',
            'footer.seal_logo_file.max' => 'El logo inferior del footer debe pesar como maximo 15 MB.',
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
            'hero_gallery.items.*.media_file' => 'archivo del carrusel institucional',
            'hero_gallery.items.*.poster_file' => 'portada del video institucional',
            'hero_gallery.items.*.duration_seconds' => 'duracion de un slide institucional',
            'history.items.*.media_file' => 'archivo de la historia',
            'history.items.*.poster_file' => 'portada del video de historia',
            'history.items.*.duration_seconds' => 'duracion de un slide de historia',
            'organigram.media_file' => 'archivo del organigrama',
            'organigram.poster_file' => 'portada del video del organigrama',
            'featured_story.items.*.media_file' => 'archivo de la noticia destacada',
            'featured_story.items.*.poster_file' => 'portada del video destacado',
            'news_grid.items.*.media_file' => 'archivo de una noticia',
            'news_grid.items.*.poster_file' => 'portada del video de una noticia',
            'footer.seal_logo_file' => 'logo inferior del footer',
        ];

        $rules = [
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
            'footer.seal_logo_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'hero.media.*.media_file' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml,video/mp4,video/webm', 'max:15360'],
            'hero.media.*.poster_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'],
            'hero.media.*.duration_seconds' => ['nullable', 'integer', 'min:1', 'max:300'],
        ];

        if ($this->isAboutPage($page)) {
            $rules['hero_gallery.items.*.media_type'] = ['nullable', 'string', Rule::in(['image', 'video'])];
            $rules['hero_gallery.items.*.media_file'] = ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml,video/mp4,video/webm', 'max:15360'];
            $rules['hero_gallery.items.*.poster_file'] = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
            $rules['hero_gallery.items.*.duration_seconds'] = ['nullable', 'integer', 'min:1', 'max:300'];
            $rules['history.items.*.media_type'] = ['nullable', 'string', Rule::in(['image', 'video'])];
            $rules['history.items.*.media_file'] = ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml,video/mp4,video/webm', 'max:15360'];
            $rules['history.items.*.poster_file'] = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
            $rules['history.items.*.duration_seconds'] = ['nullable', 'integer', 'min:1', 'max:300'];
            $rules['organigram.media_type'] = ['nullable', 'string', Rule::in(['image', 'video'])];
            $rules['organigram.media_file'] = ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml,video/mp4,video/webm', 'max:15360'];
            $rules['organigram.poster_file'] = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
        }

        if ($this->isNewsPage($page)) {
            $rules['featured_story.items.*.media_type'] = ['nullable', 'string', Rule::in(['image', 'video'])];
            $rules['featured_story.items.*.media_file'] = ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml,video/mp4,video/webm', 'max:15360'];
            $rules['featured_story.items.*.poster_file'] = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
            $rules['news_grid.items.*.media_type'] = ['nullable', 'string', Rule::in(['image', 'video'])];
            $rules['news_grid.items.*.media_file'] = ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/svg+xml,video/mp4,video/webm', 'max:15360'];
            $rules['news_grid.items.*.poster_file'] = ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];
        }

        $data = $request->validate($rules, $messages, $attributes);

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
            'sections' => $this->isAboutPage($page)
                ? $this->buildAboutSectionsPayload($request, $page)
                : ($this->isNewsPage($page)
                    ? $this->buildNewsSectionsPayload($request, $page)
                    : $this->buildSectionsPayload($request, $page)),
        ];

        $this->editor->updatePage(
            $page,
            $payload,
            $request->attributes->get('admin_user'),
            ['change_summary' => $data['change_summary'] ?? null]
        );

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Diseno y contenidos actualizados correctamente.');
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
            ->with('status', 'Se restauro la version seleccionada correctamente.');
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
                    'poster_alt' => '',
                    'poster_title' => '',
                    'poster_caption' => '',
                ]),
                'items' => $this->sectionItems($page, 'announcement_modal'),
            ],
            'header' => [
                'settings' => $this->sectionSettings($page, 'header', [
                    'language_primary' => '',
                    'language_secondary' => '',
                    'accessibility_label' => '',
                    'help_label' => '',
                    'login_label' => '',
                    'search_placeholder' => '',
                ]),
                'links' => $this->sectionItems($page, 'header'),
            ],
            'hero' => [
                'settings' => $this->sectionSettings($page, 'hero', [
                    'title' => '',
                    'subtitle' => '',
                    'tracking_title' => '',
                    'tracking_text' => '',
                    'tracking_label' => '',
                    'tracking_placeholder' => '',
                    'tracking_button' => '',
                ]),
                'media' => $this->sectionItems($page, 'hero'),
            ],
            'services' => [
                'settings' => $this->sectionSettings($page, 'services', [
                    'title' => '',
                    'subtitle' => '',
                    'kicker' => '',
                ]),
                'items' => $this->sectionItems($page, 'services'),
            ],
            'status' => [
                'settings' => $this->sectionSettings($page, 'status', [
                    'title' => '',
                    'subtitle' => '',
                    'placeholder' => '',
                    'button_label' => '',
                ]),
            ],
            'tools' => [
                'settings' => $this->sectionSettings($page, 'tools', []),
                'items' => $this->sectionItems($page, 'tools'),
            ],
            'app_banner' => [
                'settings' => $this->sectionSettings($page, 'app_banner', [
                    'background_image' => '',
                ]),
                'items' => $this->sectionItems($page, 'app_banner'),
            ],
            'market' => [
                'settings' => $this->sectionSettings($page, 'market', []),
                'items' => $this->sectionItems($page, 'market'),
            ],
            'footer' => [
                'settings' => $this->sectionSettings($page, 'footer', [
                    'seal_logo' => '',
                ]),
                'help_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'help')),
                'company_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'company')),
                'alliances_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'alliances')),
                'international_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'international')),
                'social_links' => array_values(array_filter($this->sectionItems($page, 'footer'), fn ($item) => ($item['group'] ?? '') === 'social')),
            ],
        ];
    }

    protected function buildAboutEditorData(SitePage $page): array
    {
        $theme = $page->theme ?? [];

        return [
            'theme' => [
                'logo_url' => $this->normalizeAssetUrl($theme['logo_url'] ?? ''),
                'primary_color' => $theme['primary_color'] ?? '#20539a',
                'secondary_color' => $theme['secondary_color'] ?? '#102542',
                'accent_color' => $theme['accent_color'] ?? '#f3b53f',
            ],
            'hero_gallery' => [
                'settings' => $this->sectionSettings($page, 'hero_gallery', [
                    'title' => '',
                    'subtitle' => '',
                ]),
                'items' => $this->sectionItems($page, 'hero_gallery'),
            ],
            'mission_vision' => [
                'settings' => $this->sectionSettings($page, 'mission_vision', [
                    'mission_title' => 'Mision',
                    'mission_text' => '',
                    'vision_title' => 'Vision',
                    'vision_text' => '',
                ]),
            ],
            'history' => [
                'settings' => $this->sectionSettings($page, 'history', [
                    'kicker' => '',
                    'title' => '',
                    'text' => '',
                    'carousel_title' => '',
                    'carousel_text' => '',
                ]),
                'items' => $this->sectionItems($page, 'history'),
            ],
            'principles' => [
                'settings' => $this->sectionSettings($page, 'principles', [
                    'title' => '',
                    'subtitle' => '',
                ]),
                'items' => $this->sectionItems($page, 'principles'),
            ],
            'organigram' => [
                'settings' => $this->sectionSettings($page, 'organigram', [
                    'title' => '',
                    'subtitle' => '',
                    'card_title' => '',
                    'card_text' => '',
                    'image' => '',
                ]),
            ],
            'objectives' => [
                'settings' => $this->sectionSettings($page, 'objectives', [
                    'title' => '',
                    'subtitle' => '',
                ]),
                'items' => $this->sectionItems($page, 'objectives'),
            ],
        ];
    }

    protected function buildNewsEditorData(SitePage $page): array
    {
        $theme = $page->theme ?? [];

        return [
            'theme' => [
                'logo_url' => $this->normalizeAssetUrl($theme['logo_url'] ?? ''),
                'primary_color' => $theme['primary_color'] ?? '#20539a',
                'secondary_color' => $theme['secondary_color'] ?? '#102542',
                'accent_color' => $theme['accent_color'] ?? '#f3b53f',
            ],
            'featured_story' => [
                'settings' => $this->sectionSettings($page, 'featured_story', [
                    'button_label' => 'Leer noticia completa',
                ]),
                'items' => $this->sectionItems($page, 'featured_story'),
            ],
            'category_filters' => [
                'settings' => $this->sectionSettings($page, 'category_filters', [
                    'search_placeholder' => 'Buscar noticias...',
                ]),
                'items' => $this->sectionItems($page, 'category_filters'),
            ],
            'news_grid' => [
                'settings' => $this->sectionSettings($page, 'news_grid', [
                    'title' => '',
                    'subtitle' => '',
                    'cta_label' => 'Leer mas',
                ]),
                'items' => $this->sectionItems($page, 'news_grid'),
            ],
            'newsletter' => [
                'settings' => $this->sectionSettings($page, 'newsletter', [
                    'badge' => '',
                    'title' => '',
                    'text' => '',
                    'placeholder' => '',
                    'button_label' => '',
                    'legal_text' => '',
                ]),
            ],
            'pagination' => [
                'settings' => $this->sectionSettings($page, 'pagination', [
                    'load_more_label' => 'Cargar mas noticias',
                ]),
                'items' => $this->sectionItems($page, 'pagination'),
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
                'poster_alt' => $item['poster_alt'] ?? '',
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
                    'url' => ContentSecurity::sanitizeLinkUrl($item['url'] ?? '') ?? '',
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
                'title' => $request->input('status.title', $statusSettings['title'] ?? ''),
                'subtitle' => $request->input('status.subtitle', $statusSettings['subtitle'] ?? ''),
                'placeholder' => $request->input('status.placeholder', $statusSettings['placeholder'] ?? ''),
                'button_label' => $request->input('status.button_label', $statusSettings['button_label'] ?? ''),
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
                    'maps_url' => ContentSecurity::sanitizeLinkUrl($item['maps_url'] ?? '') ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'app_banner', 'Banner App', 'app_banner', 6, [
                'title' => $request->input('app_banner.title'),
                'text' => $request->input('app_banner.text'),
                'app_store_label' => $request->input('app_banner.app_store_label'),
                'play_store_label' => $request->input('app_banner.play_store_label'),
                'app_store_url' => ContentSecurity::sanitizeLinkUrl($request->input('app_banner.app_store_url')) ?? '',
                'play_store_url' => ContentSecurity::sanitizeLinkUrl($request->input('app_banner.play_store_url')) ?? '',
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
                'view_all_url' => ContentSecurity::sanitizeLinkUrl($request->input('market.view_all_url')) ?? '',
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
                'alliances_title' => $request->input('footer.alliances_title'),
                'international_title' => $request->input('footer.international_title'),
                'contact_title' => $request->input('footer.contact_title'),
                'social_title' => $request->input('footer.social_title'),
                'social_text' => $request->input('footer.social_text'),
                'address' => trim(($request->input('footer.address_line_1') ?? '') . '|' . ($request->input('footer.address_line_2') ?? ''), '|'),
                'phone' => trim(($request->input('footer.phone_line_1') ?? '') . '|' . ($request->input('footer.phone_line_2') ?? ''), '|'),
                'email' => $request->input('footer.email'),
                'seal_logo' => $this->storeUploadedImage($request, 'footer.seal_logo_file', $request->input('footer.seal_logo'), 'cms/footer'),
                'copyright' => $request->input('footer.copyright'),
                'legal_text' => $request->input('footer.legal_text'),
            ], array_merge(
                $this->mapFooterLinks(data_get($form, 'footer.help_links', []), 'help', 'help_link'),
                $this->mapFooterLinks(data_get($form, 'footer.company_links', []), 'company', 'company_link'),
                $this->mapFooterLinks(data_get($form, 'footer.alliances_links', []), 'alliances', 'alliance_link'),
                $this->mapFooterLinks(data_get($form, 'footer.international_links', []), 'international', 'international_link'),
                $this->mapFooterLinks(data_get($form, 'footer.social_links', []), 'social', 'social_link', true),
            )),
        ];
    }

    protected function buildAboutSectionsPayload(Request $request, SitePage $page): array
    {
        $form = $request->all();

        return [
            $this->makeSectionPayload($page, 'hero_gallery', 'Carrusel superior', 'hero_gallery', 0, [
                'title' => $request->input('hero_gallery.title'),
                'subtitle' => $request->input('hero_gallery.subtitle'),
            ], $this->mapRepeaterItems(data_get($form, 'hero_gallery.items', []), 'hero_gallery_slide', function ($item) {
                $mediaType = ($item['media_type'] ?? '') === 'video' ? 'video' : 'image';
                $mediaUrl = $this->storeRepeaterAsset($item, 'media_file', 'media_url', 'cms/about/hero');
                $duration = (int) ($item['duration_seconds'] ?? 0);

                if ($duration < 1 || $duration > 300) {
                    $duration = 5;
                }

                return [
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''),
                    'image' => $mediaType === 'image'
                        ? ($mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''))
                        : null,
                    'poster_image' => $this->storeRepeaterImage($item, 'poster_file', 'poster_image', 'cms/about/hero'),
                    'duration_seconds' => $duration,
                ];
            })),

            $this->makeSectionPayload($page, 'mission_vision', 'Mision y vision', 'mission_vision', 1, [
                'mission_title' => $request->input('mission_vision.mission_title'),
                'mission_text' => $request->input('mission_vision.mission_text'),
                'vision_title' => $request->input('mission_vision.vision_title'),
                'vision_text' => $request->input('mission_vision.vision_text'),
            ]),

            $this->makeSectionPayload($page, 'history', 'Historia', 'history', 2, [
                'kicker' => $request->input('history.kicker'),
                'title' => $request->input('history.title'),
                'text' => $request->input('history.text'),
                'carousel_title' => $request->input('history.carousel_title'),
                'carousel_text' => $request->input('history.carousel_text'),
            ], $this->mapRepeaterItems(data_get($form, 'history.items', []), 'history_slide', function ($item) {
                $mediaType = ($item['media_type'] ?? '') === 'video' ? 'video' : 'image';
                $mediaUrl = $this->storeRepeaterAsset($item, 'media_file', 'media_url', 'cms/about/history');
                $duration = (int) ($item['duration_seconds'] ?? 0);

                if ($duration < 1 || $duration > 300) {
                    $duration = 6;
                }

                return [
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''),
                    'image' => $mediaType === 'image'
                        ? ($mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''))
                        : null,
                    'poster_image' => $this->storeRepeaterImage($item, 'poster_file', 'poster_image', 'cms/about/history'),
                    'duration_seconds' => $duration,
                ];
            })),

            $this->makeSectionPayload($page, 'principles', 'Principios', 'principles', 3, [
                'title' => $request->input('principles.title'),
                'subtitle' => $request->input('principles.subtitle'),
            ], $this->mapRepeaterItems(data_get($form, 'principles.items', []), 'principle', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'organigram', 'Organigrama', 'organigram', 4, [
                'title' => $request->input('organigram.title'),
                'subtitle' => $request->input('organigram.subtitle'),
                'card_title' => $request->input('organigram.card_title'),
                'card_text' => $request->input('organigram.card_text'),
                'media_type' => $request->input('organigram.media_type') === 'video' ? 'video' : 'image',
                'media_url' => $this->storeUploadedImage($request, 'organigram.media_file', $request->input('organigram.media_url') ?: $request->input('organigram.image'), 'cms/about/organigram'),
                'image' => $request->input('organigram.media_type') === 'video'
                    ? null
                    : $this->storeUploadedImage($request, 'organigram.media_file', $request->input('organigram.media_url') ?: $request->input('organigram.image'), 'cms/about/organigram'),
                'poster_image' => $this->storeUploadedImage($request, 'organigram.poster_file', $request->input('organigram.poster_image'), 'cms/about/organigram'),
            ]),

            $this->makeSectionPayload($page, 'objectives', 'Objetivos institucionales', 'objectives', 5, [
                'title' => $request->input('objectives.title'),
                'subtitle' => $request->input('objectives.subtitle'),
            ], $this->mapTextRepeaterItems(data_get($form, 'objectives.items', []), 'objective', function ($item) {
                return [
                    'icon' => $item['icon'] ?? 'target',
                    'text' => $item['text'] ?? '',
                ];
            })),
        ];
    }

    protected function buildNewsSectionsPayload(Request $request, SitePage $page): array
    {
        $form = $request->all();

        return [
            $this->makeSectionPayload($page, 'featured_story', 'Noticia destacada', 'featured_story', 0, [
                'button_label' => $request->input('featured_story.button_label'),
            ], $this->mapRepeaterItems(data_get($form, 'featured_story.items', []), 'featured_story_item', function ($item) {
                $mediaType = ($item['media_type'] ?? '') === 'video' ? 'video' : 'image';
                $mediaUrl = $this->storeRepeaterAsset($item, 'media_file', 'media_url', 'cms/news/featured');

                return [
                    'badge' => $item['badge'] ?? '',
                    'title' => $item['title'] ?? '',
                    'excerpt' => $item['excerpt'] ?? '',
                    'category' => $item['category'] ?? '',
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''),
                    'image' => $mediaType === 'image'
                        ? ($mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''))
                        : null,
                    'poster_image' => $this->storeRepeaterImage($item, 'poster_file', 'poster_image', 'cms/news/featured'),
                    'article_url' => ContentSecurity::sanitizeLinkUrl($item['article_url'] ?? '') ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'category_filters', 'Filtros de categoria', 'category_filters', 1, [
                'search_placeholder' => $request->input('category_filters.search_placeholder'),
            ], $this->mapRepeaterItems(data_get($form, 'category_filters.items', []), 'news_category', function ($item) {
                return [
                    'label' => $item['label'] ?? '',
                    'url' => ContentSecurity::sanitizeLinkUrl($item['url'] ?? '') ?? '',
                    'is_active' => ! empty($item['is_active']),
                ];
            })),

            $this->makeSectionPayload($page, 'news_grid', 'Grid de noticias', 'news_grid', 2, [
                'title' => $request->input('news_grid.title'),
                'subtitle' => $request->input('news_grid.subtitle'),
                'cta_label' => $request->input('news_grid.cta_label'),
            ], $this->mapRepeaterItems(data_get($form, 'news_grid.items', []), 'news_card', function ($item) {
                $mediaType = ($item['media_type'] ?? '') === 'video' ? 'video' : 'image';
                $mediaUrl = $this->storeRepeaterAsset($item, 'media_file', 'media_url', 'cms/news/cards');

                return [
                    'date' => $item['date'] ?? '',
                    'category' => $item['category'] ?? '',
                    'title' => $item['title'] ?? '',
                    'excerpt' => $item['excerpt'] ?? '',
                    'media_type' => $mediaType,
                    'media_url' => $mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''),
                    'image' => $mediaType === 'image'
                        ? ($mediaUrl ?: $this->normalizeAssetUrl($item['image'] ?? ''))
                        : null,
                    'poster_image' => $this->storeRepeaterImage($item, 'poster_file', 'poster_image', 'cms/news/cards'),
                    'article_url' => ContentSecurity::sanitizeLinkUrl($item['article_url'] ?? '') ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'newsletter', 'Boletin', 'newsletter', 3, [
                'badge' => $request->input('newsletter.badge'),
                'title' => $request->input('newsletter.title'),
                'text' => $request->input('newsletter.text'),
                'placeholder' => $request->input('newsletter.placeholder'),
                'button_label' => $request->input('newsletter.button_label'),
                'legal_text' => $request->input('newsletter.legal_text'),
            ]),

            $this->makeSectionPayload($page, 'pagination', 'Paginacion', 'pagination', 4, [
                'load_more_label' => $request->input('pagination.load_more_label'),
            ], $this->mapRepeaterItems(data_get($form, 'pagination.items', []), 'news_page', function ($item) {
                return [
                    'label' => $item['label'] ?? '',
                    'url' => ContentSecurity::sanitizeLinkUrl($item['url'] ?? '') ?? '',
                    'is_active' => ! empty($item['is_active']),
                    'is_ellipsis' => ! empty($item['is_ellipsis']),
                ];
            })),
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

    protected function mapTextRepeaterItems(array $items, string $type, callable $dataMapper): array
    {
        return collect($items)
            ->filter(fn ($item) => filled($item['text'] ?? null))
            ->values()
            ->map(function ($item, $index) use ($type, $dataMapper) {
                return [
                    'id' => $item['id'] ?? null,
                    'name' => $item['title'] ?? \Illuminate\Support\Str::limit($item['text'] ?? ('Item ' . ($index + 1)), 80),
                    'type' => $type,
                    'sort_order' => $index,
                    'is_active' => true,
                    'data' => $dataMapper($item),
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
                    'url' => ContentSecurity::sanitizeLinkUrl($item['url'] ?? '') ?? '',
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

    protected function isAboutPage(SitePage $page): bool
    {
        return $page->slug === 'quienes-somos';
    }

    protected function isNewsPage(SitePage $page): bool
    {
        return $page->slug === 'noticias';
    }
}
