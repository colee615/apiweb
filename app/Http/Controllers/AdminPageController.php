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

        if ($this->isDeliveryExpressPage($page)) {
            return view('admin.pages.edit-delivery', [
                'page' => $page,
                'editorData' => $this->buildDeliveryEditorData($page),
                'versions' => $page->versions()->with(['actor', 'changeLogs'])->take(12)->get(),
                'historyData' => $this->buildHistoryData($page),
            ]);
        }

        if ($this->isEcaPage($page)) {
            return view('admin.pages.edit-eca', [
                'page' => $page,
                'editorData' => $this->buildEcaEditorData($page),
                'versions' => $page->versions()->with(['actor', 'changeLogs'])->take(12)->get(),
                'historyData' => $this->buildHistoryData($page),
            ]);
        }

        if ($this->isEncomiendaPage($page)) {
            return view('admin.pages.edit-encomienda', [
                'page' => $page,
                'editorData' => $this->buildEncomiendaEditorData($page),
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
            'ems_intro.image_file.max' => 'La imagen principal de EMS debe pesar como maximo 15 MB.',
            'delivery_hero.visual_image_file.max' => 'La imagen del hero de Delivery Express debe pesar como maximo 15 MB.',
            'delivery_intro.visual_image_file.max' => 'La imagen del bloque introductorio de Delivery Express debe pesar como maximo 15 MB.',
            'delivery_cta.app_store_badge_file.max' => 'La imagen del boton App Store debe pesar como maximo 15 MB.',
            'delivery_cta.play_store_badge_file.max' => 'La imagen del boton Google Play debe pesar como maximo 15 MB.',
            'delivery_cta.register_qr_file.max' => 'La imagen QR de registro debe pesar como maximo 15 MB.',
            'eca_hero.visual_image_file.max' => 'La imagen del hero de ECA debe pesar como maximo 15 MB.',
            'eca_cta.qr_image_file.max' => 'La imagen QR de ECA debe pesar como maximo 15 MB.',
            'encomienda_hero.visual_image_file.max' => 'La imagen del hero de Encomienda debe pesar como maximo 15 MB.',
            'encomienda_intro.image_file.max' => 'La imagen introductoria de Encomienda debe pesar como maximo 15 MB.',
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
            'header.news_ticker_label.max' => 'La etiqueta de novedades no debe superar los 40 caracteres.',
            'header.ticker_items.*.label.max' => 'Cada titular de novedades no debe superar los 180 caracteres.',
            'header.ticker_items.*.url.max' => 'Cada URL de novedades no debe superar los 2048 caracteres.',
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
            'ems_intro.image_file' => 'imagen principal de EMS',
            'delivery_hero.visual_image_file' => 'imagen del hero de Delivery Express',
            'delivery_intro.visual_image_file' => 'imagen del bloque introductorio de Delivery Express',
            'delivery_cta.app_store_badge_file' => 'imagen del boton App Store',
            'delivery_cta.play_store_badge_file' => 'imagen del boton Google Play',
            'delivery_cta.register_qr_file' => 'imagen QR de registro empresarial',
            'eca_hero.visual_image_file' => 'imagen del hero de ECA',
            'eca_cta.qr_image_file' => 'imagen QR de ECA',
            'encomienda_hero.visual_image_file' => 'imagen del hero de Encomienda',
            'encomienda_intro.image_file' => 'imagen introductoria de Encomienda',
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
            'header.news_ticker_label' => 'etiqueta de novedades',
            'header.ticker_items.*.label' => 'titular de novedad',
            'header.ticker_items.*.url' => 'URL de novedad',
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
            'ems_intro.image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'delivery_hero.visual_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'delivery_intro.visual_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'delivery_cta.app_store_badge_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'delivery_cta.play_store_badge_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'delivery_cta.register_qr_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'eca_hero.visual_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'eca_cta.qr_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'encomienda_hero.visual_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'encomienda_intro.image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'footer.seal_logo_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:15360'],
            'header.news_ticker_label' => ['nullable', 'string', 'max:40'],
            'header.ticker_items' => ['nullable', 'array'],
            'header.ticker_items.*.label' => ['nullable', 'string', 'max:180'],
            'header.ticker_items.*.url' => ['nullable', 'string', 'max:2048'],
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
                    : ($this->isDeliveryExpressPage($page)
                        ? $this->buildDeliverySectionsPayload($request, $page)
                        : ($this->isEcaPage($page)
                            ? $this->buildEcaSectionsPayload($request, $page)
                            : ($this->isEncomiendaPage($page)
                                ? $this->buildEncomiendaSectionsPayload($request, $page)
                                : $this->buildSectionsPayload($request, $page))))),
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
                    'news_ticker_label' => 'Novedades',
                    'news_ticker_items' => [],
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
            'ems_intro' => [
                'settings' => $this->sectionSettings($page, 'ems_intro', [
                    'eyebrow' => 'Express Mail Service',
                    'hero_title' => 'Velocidad que conecta',
                    'watermark_text' => 'EMS',
                    'title' => 'Conectamos tus sueños, aceleramos tu mundo.',
                    'highlight_text' => 'Descubre el nuevo EMS (Express Mail Service): la evolución de la mensajería urgente en Bolivia.',
                    'paragraph_one' => '',
                    'paragraph_two' => '',
                    'paragraph_three' => '',
                    'primary_button_label' => 'Solicitar servicio EMS',
                    'primary_button_url' => '#',
                    'visual_icon' => 'plane',
                    'image' => '',
                ]),
            ],
            'ems_benefits' => [
                'settings' => $this->sectionSettings($page, 'ems_benefits', [
                    'title' => 'No solo enviamos paquetes, entregamos tranquilidad.',
                ]),
                'items' => $this->sectionItems($page, 'ems_benefits'),
            ],
            'ems_national' => [
                'settings' => $this->sectionSettings($page, 'ems_national', [
                    'title' => 'Bolivia, más cerca que nunca.',
                    'subtitle' => 'Conectando el corazón de Sudamérica con eficiencia y compromiso',
                    'stat_label' => 'Tiempo de entrega nacional:',
                    'stat_value' => '24 a 48 horas',
                    'stat_caption' => 'En principales ciudades',
                ]),
                'items' => $this->sectionItems($page, 'ems_national'),
            ],
            'ems_international' => [
                'settings' => $this->sectionSettings($page, 'ems_international', [
                    'title' => 'El mundo en la palma de tu mano.',
                    'subtitle' => 'Gracias al convenio con la Unión Postal Universal (UPU), tu envío puede llegar a cualquier rincón del planeta con la calidad y seguridad que nos caracteriza.',
                    'highlight_text' => 'UPU',
                    'cta_text' => '',
                    'secondary_button_label' => 'Cotizar envío internacional',
                    'secondary_button_url' => '#',
                ]),
                'items' => $this->sectionItems($page, 'ems_international'),
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

    protected function buildDeliveryEditorData(SitePage $page): array
    {
        $theme = $page->theme ?? [];

        return [
            'theme' => [
                'logo_url' => $this->normalizeAssetUrl($theme['logo_url'] ?? ''),
                'primary_color' => $theme['primary_color'] ?? '#20539a',
                'secondary_color' => $theme['secondary_color'] ?? '#102542',
                'accent_color' => $theme['accent_color'] ?? '#f3b53f',
            ],
            'delivery_hero' => [
                'settings' => $this->sectionSettings($page, 'delivery_hero', [
                    'title_primary' => '',
                    'title_secondary' => '',
                    'eyebrow' => '',
                    'subtitle' => '',
                    'primary_button_label' => '',
                    'primary_button_url' => '',
                    'secondary_button_label' => '',
                    'secondary_button_url' => '',
                    'visual_icon' => '',
                    'floating_icon' => '',
                    'visual_image' => '',
                ]),
            ],
            'delivery_intro' => [
                'settings' => $this->sectionSettings($page, 'delivery_intro', [
                    'title' => '',
                    'highlight_text' => '',
                    'paragraph_one' => '',
                    'paragraph_two' => '',
                    'closing_text' => '',
                    'chip_one' => '',
                    'chip_two' => '',
                    'chip_three' => '',
                    'visual_icon' => '',
                    'visual_badge' => '',
                    'visual_image' => '',
                ]),
            ],
            'delivery_advantages' => [
                'settings' => $this->sectionSettings($page, 'delivery_advantages', [
                    'title' => '',
                    'subtitle' => '',
                ]),
                'items' => $this->sectionItems($page, 'delivery_advantages'),
            ],
            'delivery_process' => [
                'settings' => $this->sectionSettings($page, 'delivery_process', [
                    'title' => '',
                    'subtitle' => '',
                    'feature_title' => '',
                    'feature_text' => '',
                    'bullet_one' => '',
                    'bullet_two' => '',
                    'bullet_three' => '',
                    'tracker_title' => '',
                    'tracker_status' => '',
                    'tracker_stage' => '',
                    'timeline_one_title' => '',
                    'timeline_one_time' => '',
                    'timeline_two_title' => '',
                    'timeline_two_time' => '',
                    'timeline_three_title' => '',
                    'timeline_three_time' => '',
                ]),
                'items' => $this->sectionItems($page, 'delivery_process'),
            ],
            'delivery_info' => [
                'settings' => $this->sectionSettings($page, 'delivery_info', [
                    'title' => '',
                    'subtitle' => '',
                    'footnote' => '',
                ]),
                'items' => $this->sectionItems($page, 'delivery_info'),
            ],
            'delivery_cta' => [
                'settings' => $this->sectionSettings($page, 'delivery_cta', [
                    'title' => '',
                    'subtitle' => '',
                    'trust_text' => '',
                    'app_title' => '',
                    'app_note' => '',
                    'app_store_label' => '',
                    'app_store_url' => '',
                    'app_store_badge' => '',
                    'play_store_label' => '',
                    'play_store_url' => '',
                    'play_store_badge' => '',
                    'register_title' => '',
                    'register_text' => '',
                    'register_qr_image' => '',
                    'contact_title' => '',
                    'contact_whatsapp_label' => '',
                    'contact_whatsapp_url' => '',
                    'contact_web_label' => '',
                    'contact_web_url' => '',
                    'contact_phone_label' => '',
                    'contact_phone_url' => '',
                ]),
            ],
        ];
    }

    protected function buildEcaEditorData(SitePage $page): array
    {
        $theme = $page->theme ?? [];

        return [
            'theme' => [
                'logo_url' => $this->normalizeAssetUrl($theme['logo_url'] ?? ''),
                'primary_color' => $theme['primary_color'] ?? '#20539a',
                'secondary_color' => $theme['secondary_color'] ?? '#102542',
                'accent_color' => $theme['accent_color'] ?? '#fecc36',
            ],
            'eca_hero' => [
                'settings' => $this->sectionSettings($page, 'eca_hero', [
                    'badge' => '',
                    'title_line_one_blue' => '',
                    'title_line_one_yellow' => '',
                    'title_line_two_yellow' => '',
                    'title_line_three_blue' => '',
                    'subtitle' => '',
                    'primary_button_label' => '',
                    'primary_button_url' => '',
                    'secondary_button_label' => '',
                    'secondary_button_url' => '',
                    'visual_icon' => '',
                    'visual_image' => '',
                ]),
            ],
            'eca_intro' => [
                'settings' => $this->sectionSettings($page, 'eca_intro', [
                    'eyebrow' => '',
                    'title' => '',
                    'paragraph_one' => '',
                    'paragraph_two' => '',
                ]),
                'items' => $this->sectionItems($page, 'eca_intro'),
            ],
            'eca_rates' => [
                'settings' => $this->sectionSettings($page, 'eca_rates', [
                    'title' => '',
                    'subtitle' => '',
                    'note_title' => '',
                    'note_text' => '',
                    'primary_button_label' => '',
                    'primary_button_url' => '',
                ]),
                'items' => $this->sectionItems($page, 'eca_rates'),
            ],
            'eca_coverage' => [
                'settings' => $this->sectionSettings($page, 'eca_coverage', [
                    'title' => '',
                    'subtitle' => '',
                    'note_title' => '',
                    'note_text' => '',
                ]),
                'items' => $this->sectionItems($page, 'eca_coverage'),
            ],
            'eca_solutions' => [
                'settings' => $this->sectionSettings($page, 'eca_solutions', [
                    'title' => '',
                    'subtitle' => '',
                ]),
                'items' => $this->sectionItems($page, 'eca_solutions'),
            ],
            'eca_cta' => [
                'settings' => $this->sectionSettings($page, 'eca_cta', [
                    'title' => '',
                    'text' => '',
                    'phone_label' => '',
                    'phone_value' => '',
                    'email_label' => '',
                    'email_value' => '',
                    'address_label' => '',
                    'address_value' => '',
                    'footnote' => '',
                    'qr_title' => '',
                    'qr_text' => '',
                    'qr_image' => '',
                    'button_label' => '',
                    'button_url' => '',
                ]),
            ],
        ];
    }

    protected function buildEncomiendaEditorData(SitePage $page): array
    {
        $theme = $page->theme ?? [];

        return [
            'theme' => [
                'logo_url' => $this->normalizeAssetUrl($theme['logo_url'] ?? ''),
                'primary_color' => $theme['primary_color'] ?? '#0d47b5',
                'secondary_color' => $theme['secondary_color'] ?? '#2a4268',
                'accent_color' => $theme['accent_color'] ?? '#ffc61a',
            ],
            'encomienda_hero' => [
                'settings' => $this->sectionSettings($page, 'encomienda_hero', [
                    'badge' => '',
                    'title_line_one_white' => '',
                    'title_line_one_yellow' => '',
                    'title_line_two_white' => '',
                    'title_line_two_yellow' => '',
                    'subtitle' => '',
                    'primary_button_label' => '',
                    'primary_button_url' => '',
                    'secondary_button_label' => '',
                    'secondary_button_url' => '',
                    'visual_icon' => '',
                    'visual_image' => '',
                ]),
            ],
            'encomienda_intro' => [
                'settings' => $this->sectionSettings($page, 'encomienda_intro', [
                    'title' => '',
                    'paragraph_one' => '',
                    'paragraph_two' => '',
                    'quote' => '',
                    'image' => '',
                    'visual_icon' => '',
                    'badge_label' => '',
                    'badge_value' => '',
                    'badge_suffix' => '',
                ]),
                'items' => $this->sectionItems($page, 'encomienda_intro'),
            ],
            'encomienda_features' => [
                'settings' => $this->sectionSettings($page, 'encomienda_features', [
                    'title' => '',
                    'subtitle' => '',
                ]),
                'items' => $this->sectionItems($page, 'encomienda_features'),
            ],
            'encomienda_faq' => [
                'settings' => $this->sectionSettings($page, 'encomienda_faq', [
                    'title' => '',
                    'subtitle' => '',
                    'tip_text' => '',
                ]),
                'items' => $this->sectionItems($page, 'encomienda_faq'),
            ],
            'encomienda_cta' => [
                'settings' => $this->sectionSettings($page, 'encomienda_cta', [
                    'title' => '',
                    'subtitle' => '',
                    'button_one_label' => '',
                    'button_one_url' => '',
                    'button_two_label' => '',
                    'button_two_url' => '',
                    'button_three_label' => '',
                    'button_three_url' => '',
                    'footnote' => '',
                    'watermark_text' => '',
                ]),
                'items' => $this->sectionItems($page, 'encomienda_cta'),
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
                'news_ticker_label' => $request->input('header.news_ticker_label') ?: 'Novedades',
                'news_ticker_items' => collect(data_get($form, 'header.ticker_items', []))
                    ->map(function ($item) {
                        $label = trim((string) ($item['label'] ?? ''));
                        $url = ContentSecurity::sanitizeLinkUrl($item['url'] ?? '') ?? '';

                        if ($label === '') {
                            return null;
                        }

                        return [
                            'title' => $label,
                            'url' => $url,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all(),
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

            $this->makeSectionPayload($page, 'ems_intro', 'EMS Intro', 'ems_intro', 5, [
                'eyebrow' => $request->input('ems_intro.eyebrow'),
                'hero_title' => $request->input('ems_intro.hero_title'),
                'watermark_text' => $request->input('ems_intro.watermark_text'),
                'title' => $request->input('ems_intro.title'),
                'highlight_text' => $request->input('ems_intro.highlight_text'),
                'paragraph_one' => $request->input('ems_intro.paragraph_one'),
                'paragraph_two' => $request->input('ems_intro.paragraph_two'),
                'paragraph_three' => $request->input('ems_intro.paragraph_three'),
                'primary_button_label' => $request->input('ems_intro.primary_button_label'),
                'primary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('ems_intro.primary_button_url')) ?? '',
                'visual_icon' => $request->input('ems_intro.visual_icon'),
                'image' => $this->storeUploadedImage($request, 'ems_intro.image_file', $request->input('ems_intro.image'), 'cms/ems'),
            ]),

            $this->makeSectionPayload($page, 'ems_benefits', 'EMS Beneficios', 'ems_card_grid', 6, [
                'title' => $request->input('ems_benefits.title'),
            ], $this->mapRepeaterItems(data_get($form, 'ems_benefits.items', []), 'ems_benefit', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'badge' => $item['badge'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'ems_national', 'EMS Nacional', 'ems_card_grid', 7, [
                'title' => $request->input('ems_national.title'),
                'subtitle' => $request->input('ems_national.subtitle'),
                'stat_label' => $request->input('ems_national.stat_label'),
                'stat_value' => $request->input('ems_national.stat_value'),
                'stat_caption' => $request->input('ems_national.stat_caption'),
            ], $this->mapRepeaterItems(data_get($form, 'ems_national.items', []), 'ems_national_card', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'badge' => $item['badge'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'ems_international', 'EMS Internacional', 'ems_card_grid', 8, [
                'title' => $request->input('ems_international.title'),
                'subtitle' => $request->input('ems_international.subtitle'),
                'highlight_text' => $request->input('ems_international.highlight_text'),
                'cta_text' => $request->input('ems_international.cta_text'),
                'secondary_button_label' => $request->input('ems_international.secondary_button_label'),
                'secondary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('ems_international.secondary_button_url')) ?? '',
            ], $this->mapRepeaterItems(data_get($form, 'ems_international.items', []), 'ems_international_card', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'badge' => $item['badge'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'status', 'Estado de envio', 'tracking_form', 4, [
                'title' => $request->input('status.title', $statusSettings['title'] ?? ''),
                'subtitle' => $request->input('status.subtitle', $statusSettings['subtitle'] ?? ''),
                'placeholder' => $request->input('status.placeholder', $statusSettings['placeholder'] ?? ''),
                'button_label' => $request->input('status.button_label', $statusSettings['button_label'] ?? ''),
            ]),

            $this->makeSectionPayload($page, 'tools', 'Herramientas', 'tools', 9, [
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

            $this->makeSectionPayload($page, 'app_banner', 'Banner App', 'app_banner', 10, [
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

            $this->makeSectionPayload($page, 'market', 'Market', 'product_grid', 11, [
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

            $this->makeSectionPayload($page, 'footer', 'Pie de pagina', 'footer', 12, [
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

    protected function buildDeliverySectionsPayload(Request $request, SitePage $page): array
    {
        $form = $request->all();

        $sections = array_values(array_filter([
            $this->preserveExistingSectionPayload($page, 'header', 0),

            $this->makeSectionPayload($page, 'delivery_hero', 'Delivery Hero', 'delivery_hero', 1, [
                'title_primary' => $request->input('delivery_hero.title_primary'),
                'title_secondary' => $request->input('delivery_hero.title_secondary'),
                'eyebrow' => $request->input('delivery_hero.eyebrow'),
                'subtitle' => $request->input('delivery_hero.subtitle'),
                'primary_button_label' => $request->input('delivery_hero.primary_button_label'),
                'primary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('delivery_hero.primary_button_url')) ?? '',
                'secondary_button_label' => $request->input('delivery_hero.secondary_button_label'),
                'secondary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('delivery_hero.secondary_button_url')) ?? '',
                'visual_icon' => $request->input('delivery_hero.visual_icon'),
                'floating_icon' => $request->input('delivery_hero.floating_icon'),
                'visual_image' => $this->storeUploadedImage($request, 'delivery_hero.visual_image_file', $request->input('delivery_hero.visual_image'), 'cms/deliveryexpress/hero'),
            ]),

            $this->makeSectionPayload($page, 'delivery_intro', 'Delivery Intro', 'delivery_intro', 2, [
                'title' => $request->input('delivery_intro.title'),
                'highlight_text' => $request->input('delivery_intro.highlight_text'),
                'paragraph_one' => $request->input('delivery_intro.paragraph_one'),
                'paragraph_two' => $request->input('delivery_intro.paragraph_two'),
                'closing_text' => $request->input('delivery_intro.closing_text'),
                'chip_one' => $request->input('delivery_intro.chip_one'),
                'chip_two' => $request->input('delivery_intro.chip_two'),
                'chip_three' => $request->input('delivery_intro.chip_three'),
                'visual_icon' => $request->input('delivery_intro.visual_icon'),
                'visual_badge' => $request->input('delivery_intro.visual_badge'),
                'visual_image' => $this->storeUploadedImage($request, 'delivery_intro.visual_image_file', $request->input('delivery_intro.visual_image'), 'cms/deliveryexpress/intro'),
            ]),

            $this->makeSectionPayload($page, 'delivery_advantages', 'Delivery Ventajas', 'delivery_card_grid', 3, [
                'title' => $request->input('delivery_advantages.title'),
                'subtitle' => $request->input('delivery_advantages.subtitle'),
            ], $this->mapRepeaterItems(data_get($form, 'delivery_advantages.items', []), 'delivery_advantage', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'badge' => $item['badge'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'delivery_process', 'Delivery Proceso', 'delivery_process', 4, [
                'title' => $request->input('delivery_process.title'),
                'subtitle' => $request->input('delivery_process.subtitle'),
                'feature_title' => $request->input('delivery_process.feature_title'),
                'feature_text' => $request->input('delivery_process.feature_text'),
                'bullet_one' => $request->input('delivery_process.bullet_one'),
                'bullet_two' => $request->input('delivery_process.bullet_two'),
                'bullet_three' => $request->input('delivery_process.bullet_three'),
                'tracker_title' => $request->input('delivery_process.tracker_title'),
                'tracker_status' => $request->input('delivery_process.tracker_status'),
                'tracker_stage' => $request->input('delivery_process.tracker_stage'),
                'timeline_one_title' => $request->input('delivery_process.timeline_one_title'),
                'timeline_one_time' => $request->input('delivery_process.timeline_one_time'),
                'timeline_two_title' => $request->input('delivery_process.timeline_two_title'),
                'timeline_two_time' => $request->input('delivery_process.timeline_two_time'),
                'timeline_three_title' => $request->input('delivery_process.timeline_three_title'),
                'timeline_three_time' => $request->input('delivery_process.timeline_three_time'),
            ], $this->mapRepeaterItems(data_get($form, 'delivery_process.items', []), 'delivery_step', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'badge' => $item['badge'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'delivery_info', 'Delivery Informacion', 'delivery_card_grid', 5, [
                'title' => $request->input('delivery_info.title'),
                'subtitle' => $request->input('delivery_info.subtitle'),
                'footnote' => $request->input('delivery_info.footnote'),
            ], $this->mapRepeaterItems(data_get($form, 'delivery_info.items', []), 'delivery_info_card', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'badge' => $item['badge'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'delivery_cta', 'Delivery CTA', 'delivery_cta', 6, [
                'title' => $request->input('delivery_cta.title'),
                'subtitle' => $request->input('delivery_cta.subtitle'),
                'trust_text' => $request->input('delivery_cta.trust_text'),
                'app_title' => $request->input('delivery_cta.app_title'),
                'app_note' => $request->input('delivery_cta.app_note'),
                'app_store_label' => $request->input('delivery_cta.app_store_label'),
                'app_store_url' => ContentSecurity::sanitizeLinkUrl($request->input('delivery_cta.app_store_url')) ?? '',
                'app_store_badge' => $this->storeUploadedImage($request, 'delivery_cta.app_store_badge_file', $request->input('delivery_cta.app_store_badge'), 'cms/deliveryexpress/cta'),
                'play_store_label' => $request->input('delivery_cta.play_store_label'),
                'play_store_url' => ContentSecurity::sanitizeLinkUrl($request->input('delivery_cta.play_store_url')) ?? '',
                'play_store_badge' => $this->storeUploadedImage($request, 'delivery_cta.play_store_badge_file', $request->input('delivery_cta.play_store_badge'), 'cms/deliveryexpress/cta'),
                'register_title' => $request->input('delivery_cta.register_title'),
                'register_text' => $request->input('delivery_cta.register_text'),
                'register_qr_image' => $this->storeUploadedImage($request, 'delivery_cta.register_qr_file', $request->input('delivery_cta.register_qr_image'), 'cms/deliveryexpress/cta'),
                'contact_title' => $request->input('delivery_cta.contact_title'),
                'contact_whatsapp_label' => $request->input('delivery_cta.contact_whatsapp_label'),
                'contact_whatsapp_url' => ContentSecurity::sanitizeLinkUrl($request->input('delivery_cta.contact_whatsapp_url')) ?? '',
                'contact_web_label' => $request->input('delivery_cta.contact_web_label'),
                'contact_web_url' => ContentSecurity::sanitizeLinkUrl($request->input('delivery_cta.contact_web_url')) ?? '',
                'contact_phone_label' => $request->input('delivery_cta.contact_phone_label'),
                'contact_phone_url' => ContentSecurity::sanitizeLinkUrl($request->input('delivery_cta.contact_phone_url')) ?? '',
            ]),

            $this->preserveExistingSectionPayload($page, 'footer', 7),
        ]));

        return $sections;
    }

    protected function buildEcaSectionsPayload(Request $request, SitePage $page): array
    {
        $form = $request->all();

        return array_values(array_filter([
            $this->preserveExistingSectionPayload($page, 'header', 0),

            $this->makeSectionPayload($page, 'eca_hero', 'ECA Hero', 'eca_hero', 1, [
                'badge' => $request->input('eca_hero.badge'),
                'title_line_one_blue' => $request->input('eca_hero.title_line_one_blue'),
                'title_line_one_yellow' => $request->input('eca_hero.title_line_one_yellow'),
                'title_line_two_yellow' => $request->input('eca_hero.title_line_two_yellow'),
                'title_line_three_blue' => $request->input('eca_hero.title_line_three_blue'),
                'subtitle' => $request->input('eca_hero.subtitle'),
                'primary_button_label' => $request->input('eca_hero.primary_button_label'),
                'primary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('eca_hero.primary_button_url')) ?? '',
                'secondary_button_label' => $request->input('eca_hero.secondary_button_label'),
                'secondary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('eca_hero.secondary_button_url')) ?? '',
                'visual_icon' => $request->input('eca_hero.visual_icon'),
                'visual_image' => $this->storeUploadedImage($request, 'eca_hero.visual_image_file', $request->input('eca_hero.visual_image'), 'cms/eca/hero'),
            ]),

            $this->makeSectionPayload($page, 'eca_intro', 'ECA Intro', 'eca_intro', 2, [
                'eyebrow' => $request->input('eca_intro.eyebrow'),
                'title' => $request->input('eca_intro.title'),
                'paragraph_one' => $request->input('eca_intro.paragraph_one'),
                'paragraph_two' => $request->input('eca_intro.paragraph_two'),
            ], $this->mapRepeaterItems(data_get($form, 'eca_intro.items', []), 'eca_segment', function ($item) {
                return [
                    'title' => $item['title'] ?? '',
                    'icon' => $item['icon'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'eca_rates', 'ECA Tarifas', 'eca_rates', 3, [
                'title' => $request->input('eca_rates.title'),
                'subtitle' => $request->input('eca_rates.subtitle'),
                'note_title' => $request->input('eca_rates.note_title'),
                'note_text' => $request->input('eca_rates.note_text'),
                'primary_button_label' => $request->input('eca_rates.primary_button_label'),
                'primary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('eca_rates.primary_button_url')) ?? '',
            ], $this->mapRepeaterItems(data_get($form, 'eca_rates.items', []), 'eca_rate_stat', function ($item) {
                return [
                    'value' => $item['value'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'eca_coverage', 'ECA Cobertura', 'eca_coverage', 4, [
                'title' => $request->input('eca_coverage.title'),
                'subtitle' => $request->input('eca_coverage.subtitle'),
                'note_title' => $request->input('eca_coverage.note_title'),
                'note_text' => $request->input('eca_coverage.note_text'),
            ], $this->mapRepeaterItems(data_get($form, 'eca_coverage.items', []), 'eca_coverage_card', function ($item) {
                return [
                    'eyebrow' => $item['eyebrow'] ?? '',
                    'title' => $item['title'] ?? '',
                    'icon' => $item['icon'] ?? '',
                    'row_one_label' => $item['row_one_label'] ?? '',
                    'row_one_value' => $item['row_one_value'] ?? '',
                    'row_two_label' => $item['row_two_label'] ?? '',
                    'row_two_value' => $item['row_two_value'] ?? '',
                    'row_three_label' => $item['row_three_label'] ?? '',
                    'row_three_value' => $item['row_three_value'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'eca_solutions', 'ECA Soluciones', 'eca_solutions', 5, [
                'title' => $request->input('eca_solutions.title'),
                'subtitle' => $request->input('eca_solutions.subtitle'),
            ], $this->mapRepeaterItems(data_get($form, 'eca_solutions.items', []), 'eca_solution_card', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                    'badge' => $item['badge'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'eca_cta', 'ECA CTA', 'eca_cta', 6, [
                'title' => $request->input('eca_cta.title'),
                'text' => $request->input('eca_cta.text'),
                'phone_label' => $request->input('eca_cta.phone_label'),
                'phone_value' => $request->input('eca_cta.phone_value'),
                'email_label' => $request->input('eca_cta.email_label'),
                'email_value' => $request->input('eca_cta.email_value'),
                'address_label' => $request->input('eca_cta.address_label'),
                'address_value' => $request->input('eca_cta.address_value'),
                'footnote' => $request->input('eca_cta.footnote'),
                'qr_title' => $request->input('eca_cta.qr_title'),
                'qr_text' => $request->input('eca_cta.qr_text'),
                'qr_image' => $this->storeUploadedImage($request, 'eca_cta.qr_image_file', $request->input('eca_cta.qr_image'), 'cms/eca/cta'),
                'button_label' => $request->input('eca_cta.button_label'),
                'button_url' => ContentSecurity::sanitizeLinkUrl($request->input('eca_cta.button_url')) ?? '',
            ]),

            $this->preserveExistingSectionPayload($page, 'footer', 7),
        ]));
    }

    protected function buildEncomiendaSectionsPayload(Request $request, SitePage $page): array
    {
        $form = $request->all();

        return array_values(array_filter([
            $this->preserveExistingSectionPayload($page, 'header', 0),

            $this->makeSectionPayload($page, 'encomienda_hero', 'Encomienda Hero', 'encomienda_hero', 1, [
                'badge' => $request->input('encomienda_hero.badge'),
                'title_line_one_white' => $request->input('encomienda_hero.title_line_one_white'),
                'title_line_one_yellow' => $request->input('encomienda_hero.title_line_one_yellow'),
                'title_line_two_white' => $request->input('encomienda_hero.title_line_two_white'),
                'title_line_two_yellow' => $request->input('encomienda_hero.title_line_two_yellow'),
                'subtitle' => $request->input('encomienda_hero.subtitle'),
                'primary_button_label' => $request->input('encomienda_hero.primary_button_label'),
                'primary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('encomienda_hero.primary_button_url')) ?? '',
                'secondary_button_label' => $request->input('encomienda_hero.secondary_button_label'),
                'secondary_button_url' => ContentSecurity::sanitizeLinkUrl($request->input('encomienda_hero.secondary_button_url')) ?? '',
                'visual_icon' => $request->input('encomienda_hero.visual_icon'),
                'visual_image' => $this->storeUploadedImage($request, 'encomienda_hero.visual_image_file', $request->input('encomienda_hero.visual_image'), 'cms/encomienda/hero'),
            ]),

            $this->makeSectionPayload($page, 'encomienda_intro', 'Encomienda Intro', 'encomienda_intro', 2, [
                'title' => $request->input('encomienda_intro.title'),
                'paragraph_one' => $request->input('encomienda_intro.paragraph_one'),
                'paragraph_two' => $request->input('encomienda_intro.paragraph_two'),
                'quote' => $request->input('encomienda_intro.quote'),
                'image' => $this->storeUploadedImage($request, 'encomienda_intro.image_file', $request->input('encomienda_intro.image'), 'cms/encomienda/intro'),
                'visual_icon' => $request->input('encomienda_intro.visual_icon'),
                'badge_label' => $request->input('encomienda_intro.badge_label'),
                'badge_value' => $request->input('encomienda_intro.badge_value'),
                'badge_suffix' => $request->input('encomienda_intro.badge_suffix'),
            ], $this->mapRepeaterItems(data_get($form, 'encomienda_intro.items', []), 'encomienda_stat', function ($item) {
                return [
                    'value' => $item['value'] ?? '',
                    'label' => $item['label'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'encomienda_features', 'Encomienda Features', 'encomienda_features', 3, [
                'title' => $request->input('encomienda_features.title'),
                'subtitle' => $request->input('encomienda_features.subtitle'),
            ], $this->mapRepeaterItems(data_get($form, 'encomienda_features.items', []), 'encomienda_feature', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'encomienda_faq', 'Encomienda FAQ', 'encomienda_faq', 4, [
                'title' => $request->input('encomienda_faq.title'),
                'subtitle' => $request->input('encomienda_faq.subtitle'),
                'tip_text' => $request->input('encomienda_faq.tip_text'),
            ], $this->mapRepeaterItems(data_get($form, 'encomienda_faq.items', []), 'encomienda_faq_item', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'title' => $item['title'] ?? '',
                    'text' => $item['text'] ?? '',
                ];
            })),

            $this->makeSectionPayload($page, 'encomienda_cta', 'Encomienda CTA', 'encomienda_cta', 5, [
                'title' => $request->input('encomienda_cta.title'),
                'subtitle' => $request->input('encomienda_cta.subtitle'),
                'button_one_label' => $request->input('encomienda_cta.button_one_label'),
                'button_one_url' => ContentSecurity::sanitizeLinkUrl($request->input('encomienda_cta.button_one_url')) ?? '',
                'button_two_label' => $request->input('encomienda_cta.button_two_label'),
                'button_two_url' => ContentSecurity::sanitizeLinkUrl($request->input('encomienda_cta.button_two_url')) ?? '',
                'button_three_label' => $request->input('encomienda_cta.button_three_label'),
                'button_three_url' => ContentSecurity::sanitizeLinkUrl($request->input('encomienda_cta.button_three_url')) ?? '',
                'footnote' => $request->input('encomienda_cta.footnote'),
                'watermark_text' => $request->input('encomienda_cta.watermark_text'),
            ], $this->mapRepeaterItems(data_get($form, 'encomienda_cta.items', []), 'encomienda_contact_chip', function ($item) {
                return [
                    'icon' => $item['icon'] ?? '',
                    'text' => $item['text'] ?? '',
                ];
            })),

            $this->preserveExistingSectionPayload($page, 'footer', 6),
        ]));
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
            ->filter(function ($item) {
                return filled($item['title'] ?? $item['label'] ?? $item['name'] ?? null)
                    || filled($item['text'] ?? null)
                    || filled($item['image'] ?? null)
                    || filled($item['poster_image'] ?? null)
                    || filled($item['media_url'] ?? null)
                    || filled($item['src'] ?? null)
                    || (($item['media_file'] ?? null) instanceof \Illuminate\Http\UploadedFile)
                    || (($item['image_file'] ?? null) instanceof \Illuminate\Http\UploadedFile)
                    || (($item['poster_file'] ?? null) instanceof \Illuminate\Http\UploadedFile)
                    || (($item['iconImage_file'] ?? null) instanceof \Illuminate\Http\UploadedFile);
            })
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

    protected function preserveExistingSectionPayload(SitePage $page, string $key, int $sortOrder): ?array
    {
        $section = $page->sections->firstWhere('key', $key);

        if (! $section) {
            return null;
        }

        return [
            'id' => $section->id,
            'key' => $section->key,
            'name' => $section->name,
            'type' => $section->type,
            'settings' => $section->settings ?? [],
            'sort_order' => $sortOrder,
            'is_active' => (bool) $section->is_active,
            'items' => $section->items
                ->sortBy('sort_order')
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'type' => $item->type,
                        'sort_order' => $item->sort_order,
                        'is_active' => (bool) $item->is_active,
                        'data' => $item->data ?? [],
                    ];
                })
                ->values()
                ->all(),
        ];
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
        return $page->slug === 'quienes-somos'
            || $this->pageHasSectionKeys($page, ['hero_gallery', 'mission_vision', 'history', 'principles', 'organigram', 'objectives']);
    }

    protected function isNewsPage(SitePage $page): bool
    {
        return $page->slug === 'noticias'
            || $this->pageHasSectionKeys($page, ['featured_story', 'category_filters', 'news_grid', 'newsletter', 'pagination']);
    }

    protected function isDeliveryExpressPage(SitePage $page): bool
    {
        return $page->slug === 'deliveryexpress'
            || $this->pageHasSectionKeys($page, ['delivery_hero', 'delivery_intro', 'delivery_advantages', 'delivery_process', 'delivery_info', 'delivery_cta']);
    }

    protected function isEcaPage(SitePage $page): bool
    {
        return $page->slug === 'eca'
            || $this->pageHasSectionKeys($page, ['eca_hero', 'eca_intro', 'eca_rates', 'eca_coverage', 'eca_solutions', 'eca_cta']);
    }

    protected function isEncomiendaPage(SitePage $page): bool
    {
        return $page->slug === 'encomienda'
            || $this->pageHasSectionKeys($page, ['encomienda_hero', 'encomienda_intro', 'encomienda_features', 'encomienda_faq', 'encomienda_cta']);
    }

    protected function pageHasSectionKeys(SitePage $page, array $expectedKeys): bool
    {
        if (! $page->relationLoaded('sections')) {
            $page->loadMissing('sections');
        }

        $sectionKeys = $page->sections
            ->pluck('key')
            ->filter()
            ->all();

        return empty(array_diff($expectedKeys, $sectionKeys));
    }
}
