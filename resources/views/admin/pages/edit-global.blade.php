@extends('layouts.admin')

@php
    $theme = $editorData['theme'] ?? [];
    $announcement = $editorData['announcement_modal'] ?? ['settings' => [], 'items' => []];
    $header = $editorData['header'] ?? ['settings' => [], 'links' => []];
    $headerSettings = $header['settings'] ?? [];
    $footer = $editorData['footer'] ?? ['settings' => []];
    $footerSettings = $footer['settings'] ?? [];
    $address = array_pad(explode('|', $footerSettings['address'] ?? '|'), 2, '');
    $phone = array_pad(explode('|', $footerSettings['phone'] ?? '|'), 2, '');
    $footerGroups = [
        ['key' => 'help_links', 'title' => 'Enlaces de ayuda', 'description' => 'Preguntas frecuentes, contacto o soporte.', 'items' => $footer['help_links'] ?? []],
        ['key' => 'company_links', 'title' => 'Enlaces institucionales', 'description' => 'Información de la empresa y accesos corporativos.', 'items' => $footer['company_links'] ?? []],
        ['key' => 'alliances_links', 'title' => 'Entidades relacionadas', 'description' => 'Instituciones nacionales destacadas.', 'items' => $footer['alliances_links'] ?? []],
        ['key' => 'international_links', 'title' => 'Organizaciones internacionales', 'description' => 'Vínculos internacionales visibles en el pie.', 'items' => $footer['international_links'] ?? []],
    ];
@endphp

@section('content')
<div
    class="admin-shell stack"
    x-data="{
        tab: @js(request('tab', 'identity')),
        go(section) {
            this.tab = section;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', section);
            window.history.replaceState({}, '', url);
        }
    }"
>
    @if (session('status'))
        <div class="notice notice-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="notice notice-error">
            <strong>No pudimos guardar la configuración.</strong>
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <header class="admin-topbar editor-heading">
        <div class="admin-brand">
            <a class="back-link" href="{{ route('admin.dashboard') }}">← Contenido del sitio</a>
            <h1>Configuración global <span class="pill pill-ok">Todo el sitio</span></h1>
            <p>Administra el popup, la identidad visual, el encabezado y el pie de página desde un solo lugar.</p>
        </div>
        <a class="button button-ghost" href="{{ route('admin.pages.edit', ['page' => $page, 'tab' => 'history_overview']) }}">Ver historial</a>
    </header>

    <form id="global-settings-form" method="POST" action="{{ route('admin.global.update') }}" class="stack" data-editor-form enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="editor-layout">
            <aside class="editor-sidebar">
                <div class="editor-nav">
                    <h3>Configuración</h3>
                    <p>Estos cambios se reflejan en todas las páginas.</p>
                    <div class="editor-nav-list">
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'popup' }" @click="go('popup')"><strong>Popup de entrada</strong><span>Avisos al iniciar una sesión</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'identity' }" @click="go('identity')"><strong>Identidad visual</strong><span>Logo y colores corporativos</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'header' }" @click="go('header')"><strong>Encabezado</strong><span>Menú, idiomas y novedades</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'footer' }" @click="go('footer')"><strong>Pie de página</strong><span>Contacto, enlaces y redes</span></button>
                    </div>
                </div>
            </aside>

            <div class="editor-main">
                <section class="section-card" x-show="tab === 'popup'">
                    <div class="section-header announcement-header">
                        <div><div class="section-eyebrow">Entrada del sitio</div><h3 class="section-title">Popup informativo</h3><p class="section-copy">Publica uno o varios comunicados que se mostrarán al comenzar una nueva sesión.</p></div>
                        <div class="section-metrics">
                            <span class="pill {{ count($announcement['items'] ?? []) ? 'pill-ok' : 'pill-off' }}">{{ count($announcement['items'] ?? []) }} aviso(s)</span>
                            <span class="pill {{ !empty($announcement['settings']['enabled']) ? 'pill-ok' : 'pill-off' }}">{{ !empty($announcement['settings']['enabled']) ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                    </div>

                    <div class="design-grid announcement-layout">
                        <div class="subpanel span-4">
                            <h4>Visibilidad</h4>
                            <p>Controla cuándo debe aparecer el aviso.</p>
                            <div class="stack" style="gap:12px;">
                                <label style="display:flex; gap:10px; align-items:center; font-weight:700;"><input type="hidden" name="announcement_modal[enabled]" value="0"><input type="checkbox" name="announcement_modal[enabled]" value="1" {{ old('announcement_modal.enabled', $announcement['settings']['enabled'] ?? false) ? 'checked' : '' }}> Mostrar popup al ingresar</label>
                                <label style="display:flex; gap:10px; align-items:center; font-weight:700;"><input type="hidden" name="announcement_modal[show_once]" value="0"><input type="checkbox" name="announcement_modal[show_once]" value="1" {{ old('announcement_modal.show_once', $announcement['settings']['show_once'] ?? true) ? 'checked' : '' }}> Mostrar una vez por sesión</label>
                                <p class="field-help">Al cerrar completamente la pestaña o el navegador, podrá mostrarse en la siguiente sesión.</p>
                                <div class="field"><label>Clave de control</label><input type="text" name="announcement_modal[storage_key]" value="{{ old('announcement_modal.storage_key', $announcement['settings']['storage_key'] ?? 'cb-home-announcement') }}"></div>
                            </div>
                        </div>

                        <div class="subpanel span-8">
                            <div class="toolbar"><div><h4>Secuencia de avisos</h4><p>Arrastra las piezas para definir su orden.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar popup</button></div>
                            <div class="stack" data-collection data-base="announcement_modal[items]" data-template="announcement-template">
                                <div data-rows>
                                    @foreach (old('announcement_modal.items', $announcement['items'] ?? []) as $item)
                                        <div class="repeater-card" data-row>
                                            <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Popup institucional' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                            <div class="announcement-form-grid" style="margin-top:12px;">
                                                <div class="field"><label>Nombre interno</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                                <div class="field"><label>Texto alternativo</label><input type="text" data-field="poster_alt" value="{{ $item['poster_alt'] ?? 'Comunicado institucional' }}"></div>
                                                <div class="field"><label>Imagen actual</label><input type="text" data-field="poster_image" value="{{ $item['poster_image'] ?? '' }}"></div>
                                                <div class="field"><label>Subir imagen</label><input type="file" data-field="poster_file" accept="image/*" data-preview-input></div>
                                                <div class="field"><label>Título visible</label><input type="text" data-field="poster_title" value="{{ $item['poster_title'] ?? '' }}"></div>
                                                <div class="field"><label>Pie o detalle</label><input type="text" data-field="poster_caption" value="{{ $item['poster_caption'] ?? '' }}"></div>
                                            </div>
                                            @if (!empty($item['poster_image']))<img class="thumb" data-preview-image data-no-inline-preview="1" src="{{ $item['poster_image'] }}" style="display:none;" alt="Vista previa">@endif
                                            <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'identity'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Marca compartida</div>
                            <h3 class="section-title">Identidad visual</h3>
                            <p class="section-copy">El logo y esta paleta se utilizan en todas las páginas públicas.</p>
                        </div>
                    </div>

                    <div class="design-grid">
                        <div class="subpanel span-6" x-data="{
                            primaryColor: @js(old('theme.primary_color', $theme['primary_color'] ?? '#20539a')),
                            secondaryColor: @js(old('theme.secondary_color', $theme['secondary_color'] ?? '#102542')),
                            accentColor: @js(old('theme.accent_color', $theme['accent_color'] ?? '#f3b53f'))
                        }">
                            <h4>Paleta corporativa</h4>
                            <p>Usa valores hexadecimales de seis dígitos.</p>
                            <div class="palette-grid">
                                @foreach ([['key' => 'primary_color', 'label' => 'Color principal', 'model' => 'primaryColor'], ['key' => 'secondary_color', 'label' => 'Color secundario', 'model' => 'secondaryColor'], ['key' => 'accent_color', 'label' => 'Color de acento', 'model' => 'accentColor']] as $color)
                                    <div class="color-token">
                                        <div class="field"><label>{{ $color['label'] }}</label><input type="text" name="theme[{{ $color['key'] }}]" x-model="{{ $color['model'] }}" maxlength="7" required></div>
                                        <div class="color-swatch-card"><div class="color-swatch" :style="{ backgroundColor: {{ $color['model'] }} }"></div><span class="color-swatch-value" x-text="{{ $color['model'] }}"></span></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="subpanel span-6">
                            <h4>Logo principal</h4>
                            <p>Se mostrará en el encabezado y en los espacios de identidad del sitio.</p>
                            @if (!empty($theme['logo_url']))
                                <div class="image-frame" style="margin-bottom:14px;"><img src="{{ $theme['logo_url'] }}" alt="Logo actual" class="thumb" style="display:block; max-width:280px;"></div>
                            @endif
                            <div class="field"><label>Enlace del logo actual</label><input type="text" name="theme[logo_url]" value="{{ old('theme.logo_url', $theme['logo_url'] ?? '') }}"></div>
                            <div class="field"><label>Subir nuevo logo</label><input type="file" name="theme[logo_file]" accept=".jpg,.jpeg,.png,.webp,.svg"></div>
                            <div class="field-help">Recomendado: PNG o SVG horizontal, con fondo transparente.</div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'header'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Navegación global</div><h3 class="section-title">Encabezado</h3><p class="section-copy">Configura los textos y enlaces que aparecen en la parte superior de todas las páginas.</p></div>
                        <span class="pill pill-off">{{ count($header['links'] ?? []) }} enlaces</span>
                    </div>

                    <div class="subpanel">
                        <h4>Textos del encabezado</h4>
                        <div class="grid grid-3">
                            <div class="field"><label>Idioma principal</label><input type="text" name="header[language_primary]" value="{{ old('header.language_primary', $headerSettings['language_primary'] ?? '') }}" maxlength="30"></div>
                            <div class="field"><label>Idioma secundario</label><input type="text" name="header[language_secondary]" value="{{ old('header.language_secondary', $headerSettings['language_secondary'] ?? '') }}" maxlength="30"></div>
                            <div class="field"><label>Accesibilidad</label><input type="text" name="header[accessibility_label]" value="{{ old('header.accessibility_label', $headerSettings['accessibility_label'] ?? '') }}" maxlength="40"></div>
                            <div class="field"><label>Ayuda / contacto</label><input type="text" name="header[help_label]" value="{{ old('header.help_label', $headerSettings['help_label'] ?? '') }}" maxlength="40"></div>
                            <div class="field"><label>Inicio de sesión</label><input type="text" name="header[login_label]" value="{{ old('header.login_label', $headerSettings['login_label'] ?? '') }}" maxlength="40"></div>
                            <div class="field"><label>Texto del buscador</label><input type="text" name="header[search_placeholder]" value="{{ old('header.search_placeholder', $headerSettings['search_placeholder'] ?? '') }}" maxlength="60"></div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Franja de novedades</h4><p>Con varias novedades, la franja se mostrará como un ticker.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar novedad</button></div>
                        <div class="field" style="max-width:360px; margin-bottom:14px;"><label>Etiqueta de la franja</label><input type="text" name="header[news_ticker_label]" value="{{ old('header.news_ticker_label', $headerSettings['news_ticker_label'] ?? 'Novedades') }}" maxlength="40"></div>
                        <div class="stack" data-collection data-base="header[ticker_items]" data-template="link-template">
                            <div data-rows>
                                @foreach (old('header.ticker_items', $headerSettings['news_ticker_items'] ?? []) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? ($item['label'] ?? 'Novedad') }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-2" style="margin-top:12px;"><div class="field"><label>Titular</label><input type="text" data-field="label" value="{{ $item['title'] ?? ($item['label'] ?? '') }}"></div><div class="field"><label>Enlace</label><input type="text" data-field="url" value="{{ $item['url'] ?? '/noticias' }}"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Enlaces del menú</h4><p>Agrega, elimina o arrastra para cambiar el orden.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar enlace</button></div>
                        <div class="stack" data-collection data-base="header[links]" data-template="link-template">
                            <div data-rows>
                                @foreach (old('header.links', $header['links'] ?? []) as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $link['label'] ?? 'Enlace' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-2" style="margin-top:12px;"><div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div><div class="field"><label>Enlace</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div></div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'footer'">
                    <div class="section-header"><div><div class="section-eyebrow">Información institucional</div><h3 class="section-title">Pie de página</h3><p class="section-copy">Estos datos, enlaces y redes se muestran al final de todas las páginas.</p></div></div>

                    <div class="subpanel">
                        <h4>Textos y contacto</h4>
                        <div class="grid grid-3">
                            <div class="field"><label>Título de ayuda</label><input type="text" name="footer[help_title]" value="{{ old('footer.help_title', $footerSettings['help_title'] ?? '') }}"></div>
                            <div class="field"><label>Título institucional</label><input type="text" name="footer[company_title]" value="{{ old('footer.company_title', $footerSettings['company_title'] ?? '') }}"></div>
                            <div class="field"><label>Título de entidades</label><input type="text" name="footer[alliances_title]" value="{{ old('footer.alliances_title', $footerSettings['alliances_title'] ?? '') }}"></div>
                            <div class="field"><label>Título internacional</label><input type="text" name="footer[international_title]" value="{{ old('footer.international_title', $footerSettings['international_title'] ?? '') }}"></div>
                            <div class="field"><label>Título de contacto</label><input type="text" name="footer[contact_title]" value="{{ old('footer.contact_title', $footerSettings['contact_title'] ?? '') }}"></div>
                            <div class="field"><label>Título de redes</label><input type="text" name="footer[social_title]" value="{{ old('footer.social_title', $footerSettings['social_title'] ?? '') }}"></div>
                            <div class="field"><label>Texto de redes</label><input type="text" name="footer[social_text]" value="{{ old('footer.social_text', $footerSettings['social_text'] ?? '') }}"></div>
                            <div class="field"><label>Correo</label><input type="email" name="footer[email]" value="{{ old('footer.email', $footerSettings['email'] ?? '') }}"></div>
                            <div class="field"><label>Dirección línea 1</label><input type="text" name="footer[address_line_1]" value="{{ old('footer.address_line_1', $address[0]) }}"></div>
                            <div class="field"><label>Dirección línea 2</label><input type="text" name="footer[address_line_2]" value="{{ old('footer.address_line_2', $address[1]) }}"></div>
                            <div class="field"><label>Teléfono línea 1</label><input type="text" name="footer[phone_line_1]" value="{{ old('footer.phone_line_1', $phone[0]) }}"></div>
                            <div class="field"><label>Teléfono línea 2</label><input type="text" name="footer[phone_line_2]" value="{{ old('footer.phone_line_2', $phone[1]) }}"></div>
                            <div class="field"><label>Copyright</label><input type="text" name="footer[copyright]" value="{{ old('footer.copyright', $footerSettings['copyright'] ?? '') }}"></div>
                            <div class="field"><label>Texto legal</label><input type="text" name="footer[legal_text]" value="{{ old('footer.legal_text', $footerSettings['legal_text'] ?? '') }}"></div>
                            <div class="field"><label>Logo inferior actual</label><input type="text" name="footer[seal_logo]" value="{{ old('footer.seal_logo', $footerSettings['seal_logo'] ?? '') }}"></div>
                            <div class="field"><label>Subir logo inferior</label><input type="file" name="footer[seal_logo_file]" accept=".jpg,.jpeg,.png,.webp,.svg"></div>
                        </div>
                    </div>

                    @foreach ($footerGroups as $group)
                        <div class="subpanel">
                            <div class="toolbar"><div><h4>{{ $group['title'] }}</h4><p>{{ $group['description'] }}</p></div><button type="button" class="button button-secondary" data-add-row>Agregar enlace</button></div>
                            <div class="stack" data-collection data-base="footer[{{ $group['key'] }}]" data-template="link-template">
                                <div data-rows>
                                    @foreach (old('footer.' . $group['key'], $group['items']) as $link)
                                        <div class="repeater-card" data-row>
                                            <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $link['label'] ?? 'Enlace' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                            <div class="grid grid-2" style="margin-top:12px;"><div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div><div class="field"><label>Enlace</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div></div>
                                            <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Redes sociales</h4><p>Configura el nombre, enlace e icono de cada red.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar red</button></div>
                        <div class="stack" data-collection data-base="footer[social_links]" data-template="social-template">
                            <div data-rows>
                                @foreach (old('footer.social_links', $footer['social_links'] ?? []) as $link)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $link['aria_label'] ?? ($link['label'] ?? 'Red social') }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-3" style="margin-top:12px;"><div class="field"><label>Texto corto</label><input type="text" data-field="label" value="{{ $link['label'] ?? '' }}"></div><div class="field"><label>Descripción accesible</label><input type="text" data-field="aria_label" value="{{ $link['aria_label'] ?? '' }}"></div><div class="field"><label>Enlace</label><input type="text" data-field="url" value="{{ $link['url'] ?? '#' }}"></div><div class="field"><label>Imagen actual</label><input type="text" data-field="image" value="{{ $link['image'] ?? '' }}"></div><div class="field"><label>Subir icono</label><input type="file" data-field="image_file" accept="image/*" data-preview-input></div></div>
                                        <input type="hidden" data-field="id" value="{{ $link['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        @include('admin.pages.partials.save')
    </form>
</div>
@endsection
