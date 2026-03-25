@extends('layouts.admin')

@php
    $announcement = $editorData['announcement_modal'];
    $header = $editorData['header'];
    $hero = $editorData['hero'];
    $services = $editorData['services'];
    $status = $editorData['status'];
    $tools = $editorData['tools'];
    $appBanner = $editorData['app_banner'];
    $market = $editorData['market'];
    $footer = $editorData['footer'];
    $address = explode('|', $footer['settings']['address'] ?? '|');
    $phone = explode('|', $footer['settings']['phone'] ?? '|');
@endphp

@section('content')
<div
    class="admin-shell stack"
    x-data="{
        tab: 'design_text',
        previewDevice: 'mobile',
        go(section) {
            this.tab = section;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }"
>
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>Editor Studio de {{ $page->name }}</h2>
            <p>Una experiencia pensada para diseo, contenido e imagenes sin tocar codigo.</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.dashboard') }}" class="button button-secondary">Volver al panel</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="button button-ghost">Cerrar sesion</button></form>
        </div>
    </div>

    <div class="panel hero-panel">
        <div class="split-header">
            <div style="max-width: 760px;">
                <div class="section-eyebrow">Creative workspace</div>
                <h1 style="margin:14px 0 10px; font-size:40px; line-height:1;">Control visual total del sitio</h1>
                <p class="section-copy">Edita encabezado, portada, servicios, productos, pie de pagina y estilos desde una interfaz mas clara, elegante y preparada para un administrador no tecnico.</p>
            </div>
            <div class="section-metrics">
                <span class="pill {{ $page->is_active ? 'pill-ok' : 'pill-off' }}">{{ $page->is_active ? 'Publicada' : 'Oculta' }}</span>
                <span class="pill pill-off">{{ count($header['links']) }} enlaces</span>
                <span class="pill pill-off">{{ count($services['items']) }} servicios</span>
                <span class="pill pill-off">{{ count($market['items']) }} productos</span>
            </div>
        </div>
    </div>

    @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="notice notice-error">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="editor-layout">
            <aside class="editor-sidebar">
                <div class="editor-nav">
                    <h3>Secciones del diseo</h3>
                    <p>Muevete por bloques como si editaras una mesa de trabajo.</p>

                    <div class="editor-nav-list">
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'announcement' }" @click="go('announcement')"><strong>Popup de inicio</strong><span>Imagen institucional al abrir</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'design_text' }" @click="go('design_text')"><strong>Diseno</strong><span>Textos, logo y enlaces</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'backgrounds' }" @click="go('backgrounds')"><strong>Fondos</strong><span>Carrusel de imagenes o videos</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'services' }" @click="go('services')"><strong>Servicios</strong><span>Agregar, quitar y ordenar</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'banner' }" @click="go('banner')"><strong>Banner</strong><span>Imagen directa del bloque app</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'market' }" @click="go('market')"><strong>Filatelia</strong><span>Productos y colecciones</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'footer' }" @click="go('footer')"><strong>Footer</strong><span>Textos, urls y logo</span></button>
                    </div>
                </div>
            </aside>

            <div class="editor-main">
                <section class="section-card" x-show="tab === 'announcement'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Startup announcement</div>
                            <h3 class="section-title">Popup de inicio</h3>
                            <p class="section-copy">Sube una sola imagen institucional para que aparezca al cargar la pagina. El frontend la mostrara completa, sin barras internas de desplazamiento.</p>
                        </div>
                        <div class="section-metrics">
                            <span class="pill {{ !empty($announcement['settings']['poster_image']) ? 'pill-ok' : 'pill-off' }}">{{ !empty($announcement['settings']['poster_image']) ? 'Imagen cargada' : 'Sin imagen' }}</span>
                            <span class="pill {{ !empty($announcement['settings']['enabled']) ? 'pill-ok' : 'pill-off' }}">{{ !empty($announcement['settings']['enabled']) ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-4">
                            <h4>Visibilidad</h4>
                            <p>Activa o desactiva el popup sin tocar codigo.</p>
                            <div class="stack" style="gap: 12px;">
                                <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
                                    <input type="checkbox" name="announcement_modal[enabled]" value="1" {{ !empty($announcement['settings']['enabled']) ? 'checked' : '' }}>
                                    Mostrar popup al cargar
                                </label>
                                <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
                                    <input type="checkbox" name="announcement_modal[show_once]" value="1" {{ !empty($announcement['settings']['show_once']) ? 'checked' : '' }}>
                                    Mostrar solo una vez por navegador
                                </label>
                                <div class="field">
                                    <label>Clave de control</label>
                                    <input type="text" name="announcement_modal[storage_key]" value="{{ old('announcement_modal.storage_key', $announcement['settings']['storage_key'] ?? 'cb-home-announcement') }}">
                                </div>
                            </div>
                        </div>

                        <div class="subpanel span-8">
                            <h4>Imagen del comunicado</h4>
                            <p>Este bloque esta pensado para una sola pieza grafica vertical u horizontal. Si subes una nueva imagen, el popup del frontend se actualizara con ese arte.</p>
                            <div class="image-frame">
                                @if (!empty($announcement['settings']['poster_image']))
                                    <img src="{{ $announcement['settings']['poster_image'] }}" alt="Popup actual" class="thumb" style="max-width: 420px; aspect-ratio: auto;">
                                @endif
                                <div class="grid grid-2">
                                    <div class="field">
                                        <label>URL imagen</label>
                                        <input type="text" name="announcement_modal[poster_image]" value="{{ old('announcement_modal.poster_image', $announcement['settings']['poster_image'] ?? '') }}">
                                    </div>
                                    <div class="field">
                                        <label>Subir imagen</label>
                                        <input type="file" name="announcement_modal[poster_file]" accept="image/*">
                                    </div>
                                    <div class="field">
                                        <label>Texto alternativo</label>
                                        <input type="text" name="announcement_modal[poster_alt]" value="{{ old('announcement_modal.poster_alt', $announcement['settings']['poster_alt'] ?? 'Comunicado institucional') }}">
                                    </div>
                                    <div class="field">
                                        <label>Titulo opcional</label>
                                        <input type="text" name="announcement_modal[poster_title]" value="{{ old('announcement_modal.poster_title', $announcement['settings']['poster_title'] ?? '') }}">
                                    </div>
                                </div>
                                <div class="field">
                                    <label>Pie opcional</label>
                                    <input type="text" name="announcement_modal[poster_caption]" value="{{ old('announcement_modal.poster_caption', $announcement['settings']['poster_caption'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Base de marca</div>
                            <h3 class="section-title">Configuracion general</h3>
                            <p class="section-copy">Gestiona identidad visual, SEO y estado de publicacion.</p>
                        </div>
                        <div class="section-metrics">
                            <span class="pill pill-off">Logo</span>
                            <span class="pill pill-off">Colores</span>
                            <span class="pill pill-off">SEO</span>
                        </div>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-8">
                            <h4>Informacion principal</h4>
                            <p>Estos datos organizan la pagina dentro del panel y mejoran como se presenta en buscadores.</p>
                            <div class="grid grid-2">
                                <div class="field"><label>Nombre interno</label><input type="text" name="name" value="{{ old('name', $page->name) }}" required></div>
                                <div class="field"><label>Slug</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required></div>
                                <div class="field"><label>Titulo SEO</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                                <div class="field"><label>Descripcion SEO</label><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"></div>
                            </div>
                        </div>

                        <div class="subpanel span-4">
                            <h4>Estado del sitio</h4>
                            <p>Decide si esta vista se muestra publicamente.</p>
                            <label style="display:flex; gap:10px; align-items:center; font-weight:700;">
                                <input type="checkbox" name="is_active" value="1" {{ $page->is_active ? 'checked' : '' }}>
                                Pagina activa
                            </label>
                        </div>

                        <div
                            class="subpanel span-6"
                            x-data="{
                                primaryColor: @js(old('theme.primary_color', $editorData['theme']['primary_color'])),
                                secondaryColor: @js(old('theme.secondary_color', $editorData['theme']['secondary_color'])),
                                accentColor: @js(old('theme.accent_color', $editorData['theme']['accent_color']))
                            }"
                        >
                            <h4>Paleta</h4>
                            <p>Colores base para mantener una identidad consistente.</p>
                            <div class="palette-grid">
                                <div class="color-token">
                                    <div class="field">
                                        <label>Color principal</label>
                                        <input type="text" name="theme[primary_color]" x-model="primaryColor">
                                    </div>
                                    <div class="color-swatch-card">
                                        <div class="color-swatch" :style="{ backgroundColor: primaryColor || '#20539a' }"></div>
                                        <span class="color-swatch-value" x-text="primaryColor || '#20539a'"></span>
                                    </div>
                                </div>
                                <div class="color-token">
                                    <div class="field">
                                        <label>Color secundario</label>
                                        <input type="text" name="theme[secondary_color]" x-model="secondaryColor">
                                    </div>
                                    <div class="color-swatch-card">
                                        <div class="color-swatch" :style="{ backgroundColor: secondaryColor || '#102542' }"></div>
                                        <span class="color-swatch-value" x-text="secondaryColor || '#102542'"></span>
                                    </div>
                                </div>
                                <div class="color-token">
                                    <div class="field">
                                        <label>Color acento</label>
                                        <input type="text" name="theme[accent_color]" x-model="accentColor">
                                    </div>
                                    <div class="color-swatch-card">
                                        <div class="color-swatch" :style="{ backgroundColor: accentColor || '#f3b53f' }"></div>
                                        <span class="color-swatch-value" x-text="accentColor || '#f3b53f'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="subpanel span-6">
                            <h4>Logo</h4>
                            <p>Sube un archivo o deja una URL directa si ya lo tienes en otro servidor.</p>
                            <div class="image-frame">
                                @if ($editorData['theme']['logo_url'])
                                    <img src="{{ $editorData['theme']['logo_url'] }}" alt="Logo actual" class="thumb" style="max-width: 280px;">
                                @endif
                                <div class="field"><label>URL del logo</label><input type="text" name="theme[logo_url]" value="{{ old('theme.logo_url', $editorData['theme']['logo_url']) }}"></div>
                                <div class="field"><label>Subir nuevo logo</label><input type="file" name="theme[logo_file]" accept="image/*"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Navigation system</div>
                            <h3 class="section-title">Encabezado y menu</h3>
                            <p class="section-copy">Define idiomas, accesos rapidos y enlaces principales del sitio.</p>
                        </div>
                        <span class="pill pill-off">{{ count($header['links']) }} enlaces activos</span>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-12">
                            <h4>Textos del header</h4>
                            <div class="grid grid-3">
                                <div class="field"><label>Idioma principal</label><input type="text" name="header[language_primary]" value="{{ old('header.language_primary', $header['settings']['language_primary']) }}"></div>
                                <div class="field"><label>Idioma secundario</label><input type="text" name="header[language_secondary]" value="{{ old('header.language_secondary', $header['settings']['language_secondary']) }}"></div>
                                <div class="field"><label>Accesibilidad</label><input type="text" name="header[accessibility_label]" value="{{ old('header.accessibility_label', $header['settings']['accessibility_label']) }}"></div>
                                <div class="field"><label>Ayuda / contacto</label><input type="text" name="header[help_label]" value="{{ old('header.help_label', $header['settings']['help_label']) }}"></div>
                                <div class="field"><label>Boton login</label><input type="text" name="header[login_label]" value="{{ old('header.login_label', $header['settings']['login_label']) }}"></div>
                                <div class="field"><label>Texto buscador</label><input type="text" name="header[search_placeholder]" value="{{ old('header.search_placeholder', $header['settings']['search_placeholder']) }}"></div>
                            </div>
                        </div>

                        <div class="subpanel span-12">
                            <div class="toolbar">
                                <div>
                                    <h4>Enlaces del menu</h4>
                                    <p>Agrega, elimina y reordena. Arrastra cada tarjeta para cambiar el orden.</p>
                                </div>
                                <button type="button" class="button button-secondary" data-add-row>Agregar enlace</button>
                            </div>
                            <div class="stack" data-collection data-base="header[links]" data-template="link-template">
                                <div data-rows>
                                    @forelse ($header['links'] as $link)
                                        <div class="repeater-card" data-row>
                                            <div class="toolbar">
                                                <div class="actions">
                                                    <span class="drag-handle" data-drag>::</span>
                                                    <strong>{{ $link['label'] ?? 'Enlace' }}</strong>
                                                </div>
                                                <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                            </div>
                                            <div class="grid grid-2" style="margin-top:12px;">
                                                <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                                <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                            </div>
                                            <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                        </div>
                                    @empty
                                        <div class="empty-note">Todavia no hay enlaces cargados.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'backgrounds'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Hero media</div>
                            <h3 class="section-title">Fondos y carrusel</h3>
                            <p class="section-copy">Administra los fondos de la portada sin cambiar el diseno del frontend. Puedes cargar imagenes o videos.</p>
                        </div>
                        <span class="pill pill-off">{{ count($hero['media'] ?? []) }} slides</span>
                    </div>

                    <div class="subpanel">
                        <h4>Textos principales de portada</h4>
                        <div class="grid grid-2">
                            <div class="field"><label>Titulo principal</label><input type="text" name="hero[title]" value="{{ old('hero.title', $hero['settings']['title']) }}"></div>
                            <div class="field"><label>Subtitulo</label><input type="text" name="hero[subtitle]" value="{{ old('hero.subtitle', $hero['settings']['subtitle']) }}"></div>
                            <div class="field"><label>Titulo rastreo</label><input type="text" name="hero[tracking_title]" value="{{ old('hero.tracking_title', $hero['settings']['tracking_title']) }}"></div>
                            <div class="field"><label>Texto rastreo</label><input type="text" name="hero[tracking_text]" value="{{ old('hero.tracking_text', $hero['settings']['tracking_text']) }}"></div>
                            <div class="field"><label>Etiqueta campo</label><input type="text" name="hero[tracking_label]" value="{{ old('hero.tracking_label', $hero['settings']['tracking_label']) }}"></div>
                            <div class="field"><label>Placeholder campo</label><input type="text" name="hero[tracking_placeholder]" value="{{ old('hero.tracking_placeholder', $hero['settings']['tracking_placeholder']) }}"></div>
                            <div class="field"><label>Texto boton</label><input type="text" name="hero[tracking_button]" value="{{ old('hero.tracking_button', $hero['settings']['tracking_button']) }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Carrusel de fondos</h4>
                                <p>Sube imagenes o videos. El frontend mantendra el mismo formato visual con este contenido.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar slide</button>
                        </div>
                        <div class="stack" data-collection data-base="hero[media]" data-template="hero-media-template">
                            <div data-rows>
                                @forelse (($hero['media'] ?? []) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Slide' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Nombre interno</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Tipo</label><select data-field="media_type" data-media-type><option value="image" {{ ($item['media_type'] ?? 'image') === 'image' ? 'selected' : '' }}>Imagen</option><option value="video" {{ ($item['media_type'] ?? '') === 'video' ? 'selected' : '' }}>Video</option></select></div>
                                            <div class="field"><label>Archivo actual</label><input type="text" data-field="src" value="{{ $item['src'] ?? '' }}"></div>
                                            <div class="field"><label>Subir archivo</label><input type="file" data-field="media_file" accept="image/*,video/*"></div>
                                            <div class="field" data-poster-field><label>Poster o imagen previa</label><input type="text" data-field="poster" value="{{ $item['poster'] ?? '' }}"></div>
                                            <div class="field" data-poster-field><label>Subir poster</label><input type="file" data-field="poster_file" accept="image/*"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay fondos configurados todavia.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'services'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Service gallery</div>
                            <h3 class="section-title">Servicios destacados</h3>
                            <p class="section-copy">Cada tarjeta puede llevar texto, icono y una imagen propia.</p>
                        </div>
                        <span class="pill pill-off">{{ count($services['items']) }} bloques</span>
                    </div>

                    <div class="subpanel">
                        <h4>Cabecera de la seccion</h4>
                        <div class="grid grid-3">
                            <div class="field"><label>Titulo seccion</label><input type="text" name="services[title]" value="{{ old('services.title', $services['settings']['title']) }}"></div>
                            <div class="field"><label>Subtitulo</label><input type="text" name="services[subtitle]" value="{{ old('services.subtitle', $services['settings']['subtitle']) }}"></div>
                            <div class="field"><label>Texto superior</label><input type="text" name="services[kicker]" value="{{ old('services.kicker', $services['settings']['kicker']) }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Tarjetas de servicio</h4>
                                <p>Sube imagenes y reorganiza el orden arrastrando cada tarjeta.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar servicio</button>
                        </div>
                        <div class="stack" data-collection data-base="services[items]" data-template="service-template">
                            <div data-rows>
                                @forelse ($services['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Servicio' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                            <div class="field"><label>Imagen actual</label><input type="text" data-field="iconImage" value="{{ $item['iconImage'] ?? '' }}"></div>
                                            <div class="field"><label>Subir imagen</label><input type="file" data-field="iconImage_file" accept="image/*" data-preview-input></div>
                                            <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><input type="text" data-field="text" value="{{ $item['text'] ?? '' }}"></div>
                                        </div>
                                        <img src="{{ $item['iconImage'] ?? '' }}" alt="Imagen servicio" class="thumb" data-preview-image style="{{ empty($item['iconImage']) ? 'display:none; margin-top:14px;' : 'margin-top:14px;' }}">
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay servicios todavia. Crea el primero desde este mismo panel.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Utility area</div>
                            <h3 class="section-title">Herramientas</h3>
                            <p class="section-copy">Ajusta el bloque de mapa y la calculadora de envios.</p>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-3">
                            <div class="field"><label>Titulo mapa</label><input type="text" name="tools[map_title]" value="{{ old('tools.map_title', $tools['settings']['map_title'] ?? '') }}"></div>
                            <div class="field"><label>Texto mapa</label><input type="text" name="tools[map_text]" value="{{ old('tools.map_text', $tools['settings']['map_text'] ?? '') }}"></div>
                            <div class="field"><label>Boton mapa</label><input type="text" name="tools[map_button_label]" value="{{ old('tools.map_button_label', $tools['settings']['map_button_label'] ?? '') }}"></div>
                            <div class="field"><label>Titulo calculadora</label><input type="text" name="tools[calculator_title]" value="{{ old('tools.calculator_title', $tools['settings']['calculator_title'] ?? '') }}"></div>
                            <div class="field"><label>Texto calculadora</label><input type="text" name="tools[calculator_text]" value="{{ old('tools.calculator_text', $tools['settings']['calculator_text'] ?? '') }}"></div>
                            <div class="field"><label>Etiqueta origen</label><input type="text" name="tools[origin_label]" value="{{ old('tools.origin_label', $tools['settings']['origin_label'] ?? '') }}"></div>
                            <div class="field"><label>Placeholder origen</label><input type="text" name="tools[origin_placeholder]" value="{{ old('tools.origin_placeholder', $tools['settings']['origin_placeholder'] ?? '') }}"></div>
                            <div class="field"><label>Etiqueta destino</label><input type="text" name="tools[destination_label]" value="{{ old('tools.destination_label', $tools['settings']['destination_label'] ?? '') }}"></div>
                            <div class="field"><label>Placeholder destino</label><input type="text" name="tools[destination_placeholder]" value="{{ old('tools.destination_placeholder', $tools['settings']['destination_placeholder'] ?? '') }}"></div>
                            <div class="field"><label>Etiqueta peso</label><input type="text" name="tools[weight_label]" value="{{ old('tools.weight_label', $tools['settings']['weight_label'] ?? '') }}"></div>
                            <div class="field"><label>Placeholder peso</label><input type="text" name="tools[weight_placeholder]" value="{{ old('tools.weight_placeholder', $tools['settings']['weight_placeholder'] ?? '') }}"></div>
                            <div class="field"><label>Boton calcular</label><input type="text" name="tools[calculate_button_label]" value="{{ old('tools.calculate_button_label', $tools['settings']['calculate_button_label'] ?? '') }}"></div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'banner'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">App promotion</div>
                            <h3 class="section-title">Banner administrable</h3>
                            <p class="section-copy">Cambia la imagen principal del banner manteniendo exactamente el mismo bloque visual del frontend.</p>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-2">
                            <div class="field"><label>Titulo</label><input type="text" name="app_banner[title]" value="{{ old('app_banner.title', $appBanner['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Texto</label><input type="text" name="app_banner[text]" value="{{ old('app_banner.text', $appBanner['settings']['text'] ?? '') }}"></div>
                            <div class="field"><label>Texto App Store</label><input type="text" name="app_banner[app_store_label]" value="{{ old('app_banner.app_store_label', $appBanner['settings']['app_store_label'] ?? '') }}"></div>
                            <div class="field"><label>URL App Store</label><input type="text" name="app_banner[app_store_url]" value="{{ old('app_banner.app_store_url', $appBanner['settings']['app_store_url'] ?? '') }}"></div>
                            <div class="field"><label>Texto Google Play</label><input type="text" name="app_banner[play_store_label]" value="{{ old('app_banner.play_store_label', $appBanner['settings']['play_store_label'] ?? '') }}"></div>
                            <div class="field"><label>URL Google Play</label><input type="text" name="app_banner[play_store_url]" value="{{ old('app_banner.play_store_url', $appBanner['settings']['play_store_url'] ?? '') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <h4>Imagen del banner</h4>
                        <p>Si subes una imagen, el fondo del bloque se actualiza sin romper el formato actual.</p>
                        <div class="image-frame">
                            @if (!empty($appBanner['settings']['background_image']))
                                <img src="{{ $appBanner['settings']['background_image'] }}" alt="Banner actual" class="thumb" style="max-width: 320px;">
                            @endif
                            <div class="field"><label>URL imagen</label><input type="text" name="app_banner[background_image]" value="{{ old('app_banner.background_image', $appBanner['settings']['background_image'] ?? '') }}"></div>
                            <div class="field"><label>Subir imagen</label><input type="file" name="app_banner[background_file]" accept="image/*"></div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'market'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Commerce curation</div>
                            <h3 class="section-title">Market y productos</h3>
                            <p class="section-copy">Carga piezas destacadas, imagenes, precios y descripciones.</p>
                        </div>
                        <span class="pill pill-off">{{ count($market['items']) }} productos</span>
                    </div>

                    <div class="subpanel">
                        <h4>Cabecera de market</h4>
                        <div class="grid grid-2">
                            <div class="field"><label>Titulo</label><input type="text" name="market[title]" value="{{ old('market.title', $market['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Subtitulo</label><input type="text" name="market[subtitle]" value="{{ old('market.subtitle', $market['settings']['subtitle'] ?? '') }}"></div>
                            <div class="field"><label>Texto boton final</label><input type="text" name="market[view_all_label]" value="{{ old('market.view_all_label', $market['settings']['view_all_label'] ?? '') }}"></div>
                            <div class="field"><label>URL boton final</label><input type="text" name="market[view_all_url]" value="{{ old('market.view_all_url', $market['settings']['view_all_url'] ?? '') }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Productos destacados</h4>
                                <p>Sube fotos y organiza el orden de aparicion con arrastrar y soltar.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar producto</button>
                        </div>
                        <div class="stack" data-collection data-base="market[items]" data-template="product-template">
                            <div data-rows>
                                @forelse ($market['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Producto' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Precio</label><input type="text" data-field="price" value="{{ $item['price'] ?? '' }}"></div>
                                            <div class="field"><label>Anio o etiqueta</label><input type="text" data-field="year" value="{{ $item['year'] ?? '' }}"></div>
                                            <div class="field"><label>Serie</label><input type="text" data-field="series" value="{{ $item['series'] ?? '' }}"></div>
                                            <div class="field"><label>Imagen actual</label><input type="text" data-field="image" value="{{ $item['image'] ?? '' }}"></div>
                                            <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div>
                                        </div>
                                        <div class="field" style="margin-top:12px;"><label>Descripcion</label><textarea class="field-small" data-field="description">{{ $item['description'] ?? '' }}</textarea></div>
                                        <img src="{{ $item['image'] ?? '' }}" alt="Imagen producto" class="thumb" data-preview-image style="{{ empty($item['image']) ? 'display:none; margin-top:14px;' : 'margin-top:14px;' }}">
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay productos cargados en este momento.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'footer'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Closure and contact</div>
                            <h3 class="section-title">Pie de pagina</h3>
                            <p class="section-copy">Cierra la experiencia con enlaces, datos de contacto y redes sociales.</p>
                        </div>
                    </div>

                    <div class="subpanel">
                        <h4>Textos base</h4>
                        <div class="grid grid-3">
                            <div class="field"><label>Titulo ayuda</label><input type="text" name="footer[help_title]" value="{{ old('footer.help_title', $footer['settings']['help_title'] ?? '') }}"></div>
                            <div class="field"><label>Titulo empresa</label><input type="text" name="footer[company_title]" value="{{ old('footer.company_title', $footer['settings']['company_title'] ?? '') }}"></div>
                            <div class="field"><label>Titulo contacto</label><input type="text" name="footer[contact_title]" value="{{ old('footer.contact_title', $footer['settings']['contact_title'] ?? '') }}"></div>
                            <div class="field"><label>Titulo redes</label><input type="text" name="footer[social_title]" value="{{ old('footer.social_title', $footer['settings']['social_title'] ?? '') }}"></div>
                            <div class="field"><label>Texto redes</label><input type="text" name="footer[social_text]" value="{{ old('footer.social_text', $footer['settings']['social_text'] ?? '') }}"></div>
                            <div class="field"><label>Email</label><input type="text" name="footer[email]" value="{{ old('footer.email', $footer['settings']['email'] ?? '') }}"></div>
                            <div class="field"><label>Direccion linea 1</label><input type="text" name="footer[address_line_1]" value="{{ old('footer.address_line_1', $address[0] ?? '') }}"></div>
                            <div class="field"><label>Direccion linea 2</label><input type="text" name="footer[address_line_2]" value="{{ old('footer.address_line_2', $address[1] ?? '') }}"></div>
                            <div class="field"><label>Telefono linea 1</label><input type="text" name="footer[phone_line_1]" value="{{ old('footer.phone_line_1', $phone[0] ?? '') }}"></div>
                            <div class="field"><label>Telefono linea 2</label><input type="text" name="footer[phone_line_2]" value="{{ old('footer.phone_line_2', $phone[1] ?? '') }}"></div>
                            <div class="field"><label>Copyright</label><input type="text" name="footer[copyright]" value="{{ old('footer.copyright', $footer['settings']['copyright'] ?? '') }}"></div>
                            <div class="field"><label>Texto legal</label><input type="text" name="footer[legal_text]" value="{{ old('footer.legal_text', $footer['settings']['legal_text'] ?? '') }}"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Enlaces de ayuda</h4>
                                <p>Preguntas frecuentes, contacto o soporte.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar enlace</button>
                        </div>
                        <div class="stack" data-collection data-base="footer[help_links]" data-template="link-template">
                            <div data-rows>
                                @forelse ($footer['help_links'] as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $link['label'] ?? 'Enlace' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay enlaces de ayuda todavia.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Enlaces de empresa</h4>
                                <p>Seccion institucional del footer.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar enlace</button>
                        </div>
                        <div class="stack" data-collection data-base="footer[company_links]" data-template="link-template">
                            <div data-rows>
                                @forelse ($footer['company_links'] as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $link['label'] ?? 'Enlace' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay enlaces de empresa todavia.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Redes sociales</h4>
                                <p>Nombre corto, nombre accesible y enlace final.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar red</button>
                        </div>
                        <div class="stack" data-collection data-base="footer[social_links]" data-template="social-template">
                            <div data-rows>
                                @forelse ($footer['social_links'] as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $link['aria_label'] ?? 'Red social' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Texto corto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div>
                                            <div class="field"><label>Nombre accesible</label><input type="text" data-field="aria_label" value="{{ $link['aria_label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay redes sociales configuradas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                <div class="save-dock">
                    <div>
                        <strong style="display:block; margin-bottom:4px;">Todo listo para guardar</strong>
                        <p>El frontend publico no cambia de estructura, solo actualiza lo que el administrador controla aqui.</p>
                    </div>
                    <button type="submit" class="button button-primary">Guardar cambios del diseno</button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
