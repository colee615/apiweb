@extends('layouts.admin')

@php
    $theme = $editorData['theme'] ?? [];
    $header = $editorData['header'] ?? [];
    $headerSettings = $header['settings'] ?? [];
    $applications = $editorData['applications'] ?? [];
    $applicationSettings = $applications['settings'] ?? [];
    $highlights = $editorData['applications_highlights']['items'] ?? [];
    $footer = $editorData['footer'] ?? [];
    $footerSettings = $footer['settings'] ?? [];
    $address = array_pad(explode('|', $footerSettings['address'] ?? '|'), 2, '');
    $phone = array_pad(explode('|', $footerSettings['phone'] ?? '|'), 2, '');
    $tickerItems = $header['ticker_items'] ?? ($headerSettings['news_ticker_items'] ?? []);
@endphp

@section('content')
    <div class="admin-shell stack">
        @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
        @if ($errors->any())
            <div class="notice notice-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form id="page-edit-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">General</div>
                        <h3 class="section-title">Aplicaciones y Sistemas</h3>
                        <p class="section-copy">Configura la URL, SEO, identidad visual y estado de publicación de esta página.</p>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label>Slug</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}"></div>
                    <div class="field"><label>Nombre de la página</label><input type="text" name="name" value="{{ old('name', $page->name) }}"></div>
                    <div class="field"><label>Título SEO</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                    <div class="field"><label>Descripción SEO</label><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"></div>
                    <div class="field"><label>Color principal</label><input type="text" name="theme[primary_color]" value="{{ old('theme.primary_color', $theme['primary_color'] ?? '#20539a') }}"></div>
                    <div class="field"><label>Color secundario</label><input type="text" name="theme[secondary_color]" value="{{ old('theme.secondary_color', $theme['secondary_color'] ?? '#2f3f5c') }}"></div>
                    <div class="field"><label>Color de acento</label><input type="text" name="theme[accent_color]" value="{{ old('theme.accent_color', $theme['accent_color'] ?? '#fecc36') }}"></div>
                    <div class="field"><label>Logo actual</label><input type="text" name="theme[logo_url]" value="{{ old('theme.logo_url', $theme['logo_url'] ?? '') }}"></div>
                    <div class="field"><label>Subir logo</label><input type="file" name="theme[logo_file]" accept="image/*"></div>
                    <div class="field" style="display:flex; align-items:end;">
                        <label style="display:flex; gap:10px; align-items:center; margin:0; text-transform:none; letter-spacing:0; font-size:14px; color:#123047;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                            Publicar esta página
                        </label>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Header</div>
                        <h3 class="section-title">Encabezado y novedades</h3>
                        <p class="section-copy">Esta copia del header queda administrable para esta página sin depender de textos fijos del frontend.</p>
                    </div>
                </div>
                <div class="grid grid-3">
                    <div class="field"><label>Idioma principal</label><input type="text" name="header[language_primary]" value="{{ old('header.language_primary', $headerSettings['language_primary'] ?? '') }}"></div>
                    <div class="field"><label>Idioma secundario</label><input type="text" name="header[language_secondary]" value="{{ old('header.language_secondary', $headerSettings['language_secondary'] ?? '') }}"></div>
                    <div class="field"><label>Ayuda / contacto</label><input type="text" name="header[help_label]" value="{{ old('header.help_label', $headerSettings['help_label'] ?? '') }}"></div>
                    <div class="field"><label>Inicio de sesión</label><input type="text" name="header[login_label]" value="{{ old('header.login_label', $headerSettings['login_label'] ?? '') }}"></div>
                    <div class="field"><label>Placeholder de búsqueda</label><input type="text" name="header[search_placeholder]" value="{{ old('header.search_placeholder', $headerSettings['search_placeholder'] ?? '') }}"></div>
                    <div class="field"><label>Etiqueta de novedades</label><input type="text" name="header[news_ticker_label]" value="{{ old('header.news_ticker_label', $headerSettings['news_ticker_label'] ?? 'Novedades') }}"></div>
                </div>

                <div class="subpanel">
                    <div class="toolbar"><div><h4>Carrusel superior de novedades</h4><p>Agrega los textos y enlaces que aparecen en la franja azul superior.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar novedad</button></div>
                    <div class="stack" data-collection data-base="header[ticker_items]" data-template="link-template">
                        <div data-rows>
                            @foreach (old('header.ticker_items', $tickerItems) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Novedad' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $item['label'] ?? ($item['title'] ?? '') }}"></div>
                                        <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="subpanel">
                    <div class="toolbar"><div><h4>Enlaces de navegación</h4><p>Ordena y administra los botones del menú de esta página.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar enlace</button></div>
                    <div class="stack" data-collection data-base="header[links]" data-template="link-template">
                        <div data-rows>
                            @foreach (old('header.links', $header['links'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Enlace' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}"></div>
                                        <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Aplicaciones</div>
                        <h3 class="section-title">Contenido de la página</h3>
                        <p class="section-copy">Todos estos textos, enlaces, colores, iconos y capturas se reflejan directamente en /misaplicaciones.</p>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label>Etiqueta superior</label><input type="text" name="applications[hero_eyebrow]" value="{{ old('applications.hero_eyebrow', $applicationSettings['hero_eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Imagen de fondo actual</label><input type="text" name="applications[background_image]" value="{{ old('applications.background_image', $applicationSettings['background_image'] ?? '') }}"></div>
                    <div class="field"><label>Título principal</label><input type="text" name="applications[hero_title]" value="{{ old('applications.hero_title', $applicationSettings['hero_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto destacado del título</label><input type="text" name="applications[hero_title_accent]" value="{{ old('applications.hero_title_accent', $applicationSettings['hero_title_accent'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto introductorio</label><textarea class="field-small" name="applications[hero_text]">{{ old('applications.hero_text', $applicationSettings['hero_text'] ?? '') }}</textarea></div>
                    <div class="field"><label>Subir imagen de fondo</label><input type="file" name="applications[background_file]" accept="image/*"></div>
                    <div class="field"><label>Etiqueta del catálogo</label><input type="text" name="applications[catalog_eyebrow]" value="{{ old('applications.catalog_eyebrow', $applicationSettings['catalog_eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Título del catálogo</label><input type="text" name="applications[catalog_title]" value="{{ old('applications.catalog_title', $applicationSettings['catalog_title'] ?? '') }}"></div>
                    <div class="field"><label>Placeholder de búsqueda</label><input type="text" name="applications[search_placeholder]" value="{{ old('applications.search_placeholder', $applicationSettings['search_placeholder'] ?? '') }}"></div>
                </div>

                <div class="subpanel">
                    <div class="toolbar"><div><h4>Aplicaciones y sitios web</h4><p>Cada registro se muestra como una tarjeta. Puedes agregar, quitar y ordenar elementos.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar aplicación</button></div>
                    <div class="stack" data-collection data-base="applications[items]" data-template="application-template">
                        <div data-rows>
                            @foreach (old('applications.items', $applications['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['name'] ?? 'Aplicación' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-3" style="margin-top:12px;">
                                        <div class="field"><label>Nombre</label><input type="text" data-field="name" value="{{ $item['name'] ?? '' }}"></div>
                                        <div class="field"><label>Tipo</label><input type="text" data-field="type" value="{{ $item['type'] ?? '' }}" placeholder="Aplicación / Sistema / Sitio web"></div>
                                        <div class="field"><label>Categoría</label><input type="text" data-field="category" value="{{ $item['category'] ?? 'Otros' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="description">{{ $item['description'] ?? '' }}</textarea></div>
                                        <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? 'grid' }}" placeholder="package, document, users..."></div>
                                        <div class="field"><label>Color</label><input type="text" data-field="color" value="{{ $item['color'] ?? '#20539a' }}" placeholder="#20539a"></div>
                                        <div class="field"><label>Texto del botón</label><input type="text" data-field="action" value="{{ $item['action'] ?? 'Ingresar' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>URL</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}" placeholder="/ruta-interna o https://... "></div>
                                        <div class="field"><label>Imagen actual</label><input type="text" data-field="image" value="{{ $item['image'] ?? '' }}"></div>
                                        <div class="field"><label>Subir captura o imagen</label><input type="file" data-field="image_file" accept="image/*"></div>
                                        <div class="field"><label>Estilo de preview</label><input type="text" data-field="preview" value="{{ $item['preview'] ?? 'default' }}" placeholder="tracking, ems, default..."></div>
                                        <div class="field"><label>Etiqueta del preview</label><input type="text" data-field="preview_label" value="{{ $item['preview_label'] ?? ($item['name'] ?? '') }}"></div>
                                        <div class="field"><label>Título del preview</label><input type="text" data-field="preview_title" value="{{ $item['preview_title'] ?? ($item['name'] ?? '') }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="subpanel">
                    <div class="toolbar"><div><h4>Beneficios del hero</h4><p>Los tres mensajes breves que acompañan la portada.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar beneficio</button></div>
                    <div class="stack" data-collection data-base="applications_highlights[items]" data-template="application-highlight-template">
                        <div data-rows>
                            @foreach (old('applications_highlights.items', $highlights) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Beneficio' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? 'spark' }}"></div>
                                        <div class="field"><label>Texto</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="subpanel">
                    <h4>Bloque de ayuda</h4>
                    <div class="grid grid-2">
                        <div class="field"><label>Etiqueta</label><input type="text" name="applications[support_eyebrow]" value="{{ old('applications.support_eyebrow', $applicationSettings['support_eyebrow'] ?? '') }}"></div>
                        <div class="field"><label>Título</label><input type="text" name="applications[support_title]" value="{{ old('applications.support_title', $applicationSettings['support_title'] ?? '') }}"></div>
                        <div class="field" style="grid-column:1/-1;"><label>Texto</label><textarea class="field-small" name="applications[support_text]">{{ old('applications.support_text', $applicationSettings['support_text'] ?? '') }}</textarea></div>
                        <div class="field"><label>Texto del botón</label><input type="text" name="applications[support_button_label]" value="{{ old('applications.support_button_label', $applicationSettings['support_button_label'] ?? '') }}"></div>
                        <div class="field"><label>URL del botón</label><input type="text" name="applications[support_button_url]" value="{{ old('applications.support_button_url', $applicationSettings['support_button_url'] ?? '') }}"></div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Footer</div><h3 class="section-title">Pie de página</h3><p class="section-copy">El footer se administra desde esta misma página, igual que los demás contenidos del sitio.</p></div></div>
                <div class="grid grid-3">
                    <div class="field"><label>Título de ayuda</label><input type="text" name="footer[help_title]" value="{{ old('footer.help_title', $footerSettings['help_title'] ?? '') }}"></div>
                    <div class="field"><label>Título de empresa</label><input type="text" name="footer[company_title]" value="{{ old('footer.company_title', $footerSettings['company_title'] ?? '') }}"></div>
                    <div class="field"><label>Título de contacto</label><input type="text" name="footer[contact_title]" value="{{ old('footer.contact_title', $footerSettings['contact_title'] ?? '') }}"></div>
                    <div class="field"><label>Título de alianzas</label><input type="text" name="footer[alliances_title]" value="{{ old('footer.alliances_title', $footerSettings['alliances_title'] ?? '') }}"></div>
                    <div class="field"><label>Título internacional</label><input type="text" name="footer[international_title]" value="{{ old('footer.international_title', $footerSettings['international_title'] ?? '') }}"></div>
                    <div class="field"><label>Título de redes</label><input type="text" name="footer[social_title]" value="{{ old('footer.social_title', $footerSettings['social_title'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto de redes</label><textarea class="field-small" name="footer[social_text]">{{ old('footer.social_text', $footerSettings['social_text'] ?? '') }}</textarea></div>
                    <div class="field"><label>Dirección línea 1</label><input type="text" name="footer[address_line_1]" value="{{ old('footer.address_line_1', $address[0]) }}"></div>
                    <div class="field"><label>Dirección línea 2</label><input type="text" name="footer[address_line_2]" value="{{ old('footer.address_line_2', $address[1]) }}"></div>
                    <div class="field"><label>Correo</label><input type="email" name="footer[email]" value="{{ old('footer.email', $footerSettings['email'] ?? '') }}"></div>
                    <div class="field"><label>Teléfono línea 1</label><input type="text" name="footer[phone_line_1]" value="{{ old('footer.phone_line_1', $phone[0]) }}"></div>
                    <div class="field"><label>Teléfono línea 2</label><input type="text" name="footer[phone_line_2]" value="{{ old('footer.phone_line_2', $phone[1]) }}"></div>
                    <div class="field"><label>Logo inferior actual</label><input type="text" name="footer[seal_logo]" value="{{ old('footer.seal_logo', $footerSettings['seal_logo'] ?? '') }}"></div>
                    <div class="field"><label>Subir logo inferior</label><input type="file" name="footer[seal_logo_file]" accept="image/*"></div>
                    <div class="field"><label>Copyright</label><input type="text" name="footer[copyright]" value="{{ old('footer.copyright', $footerSettings['copyright'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto legal</label><input type="text" name="footer[legal_text]" value="{{ old('footer.legal_text', $footerSettings['legal_text'] ?? '') }}"></div>
                </div>

                @foreach ([['key' => 'help_links', 'title' => 'Enlaces de ayuda', 'items' => $footer['help_links'] ?? []], ['key' => 'company_links', 'title' => 'Enlaces de empresa', 'items' => $footer['company_links'] ?? []], ['key' => 'alliances_links', 'title' => 'Enlaces de alianzas', 'items' => $footer['alliances_links'] ?? []], ['key' => 'international_links', 'title' => 'Enlaces internacionales', 'items' => $footer['international_links'] ?? []]] as $linkGroup)
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>{{ $linkGroup['title'] }}</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar enlace</button></div>
                        <div class="stack" data-collection data-base="footer[{{ $linkGroup['key'] }}]" data-template="link-template">
                            <div data-rows>
                                @foreach (old('footer.' . $linkGroup['key'], $linkGroup['items']) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Enlace' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-2" style="margin-top:12px;"><div class="field"><label>Texto</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}"></div><div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}"></div></div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="subpanel">
                    <div class="toolbar"><div><h4>Redes sociales</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar red</button></div>
                    <div class="stack" data-collection data-base="footer[social_links]" data-template="social-template">
                        <div data-rows>
                            @foreach (old('footer.social_links', $footer['social_links'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['aria_label'] ?? ($item['label'] ?? 'Red social') }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-3" style="margin-top:12px;"><div class="field"><label>Etiqueta</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}"></div><div class="field"><label>Nombre accesible</label><input type="text" data-field="aria_label" value="{{ $item['aria_label'] ?? '' }}"></div><div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}"></div><div class="field"><label>Imagen actual</label><input type="text" data-field="image" value="{{ $item['image'] ?? '' }}"></div><div class="field"><label>Subir icono</label><input type="file" data-field="image_file" accept="image/*"></div></div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <div class="save-dock">
                <div style="flex:1;"><strong style="display:block; margin-bottom:4px;">Guardar Aplicaciones y Sistemas</strong><p>Los cambios se versionan y se publican en /misaplicaciones.</p><div class="field" style="margin-top:12px;"><label>Resumen del cambio</label><input type="text" name="change_summary" value="{{ old('change_summary') }}"></div></div>
                <button type="submit" class="button button-primary">Guardar cambios</button>
            </div>
        </form>

        <template id="application-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Aplicación</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-3" style="margin-top:12px;">
                    <div class="field"><label>Nombre</label><input type="text" data-field="name"></div>
                    <div class="field"><label>Tipo</label><input type="text" data-field="type" placeholder="Aplicación / Sistema / Sitio web"></div>
                    <div class="field"><label>Categoría</label><input type="text" data-field="category" value="Otros"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="description"></textarea></div>
                    <div class="field"><label>Icono</label><input type="text" data-field="icon" value="grid"></div>
                    <div class="field"><label>Color</label><input type="text" data-field="color" value="#20539a"></div>
                    <div class="field"><label>Texto del botón</label><input type="text" data-field="action" value="Ingresar"></div>
                    <div class="field" style="grid-column:1/-1;"><label>URL</label><input type="text" data-field="url"></div>
                    <div class="field"><label>Imagen actual</label><input type="text" data-field="image"></div>
                    <div class="field"><label>Subir captura</label><input type="file" data-field="image_file" accept="image/*"></div>
                    <div class="field"><label>Estilo de preview</label><input type="text" data-field="preview" value="default"></div>
                    <div class="field"><label>Etiqueta del preview</label><input type="text" data-field="preview_label"></div>
                    <div class="field"><label>Título del preview</label><input type="text" data-field="preview_title"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="application-highlight-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Beneficio</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;"><div class="field"><label>Icono</label><input type="text" data-field="icon" value="spark"></div><div class="field"><label>Texto</label><input type="text" data-field="title"></div></div>
                <input type="hidden" data-field="id">
            </div>
        </template>
    </div>
@endsection
