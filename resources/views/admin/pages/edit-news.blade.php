@extends('layouts.admin')

@php
    $featuredStory = $editorData['featured_story'];
    $categoryFilters = $editorData['category_filters'];
    $newsGrid = $editorData['news_grid'];
    $newsletter = $editorData['newsletter'];
    $pagination = $editorData['pagination'];
@endphp

@section('content')
<div class="admin-shell stack" x-data="{ tab: @js(request('tab', 'design_text')), go(section) { this.tab = section; } }">
    <div class="admin-topbar">
        <div class="admin-brand">
            <h2>Editor Studio de {{ $page->name }}</h2>
            <p>Administra la portada de noticias. El header y el footer se siguen heredando desde Home.</p>
        </div>
        <div class="actions">
            <a href="{{ route('admin.dashboard') }}" class="button button-secondary">Volver al panel</a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="button button-ghost">Cerrar sesion</button></form>
        </div>
    </div>

    <div class="panel hero-panel">
        <div class="split-header">
            <div style="max-width:760px;">
                <div class="section-eyebrow">Newsroom Studio</div>
                <h1 style="margin:14px 0 10px; font-size:40px; line-height:1;">Control visual de "Noticias"</h1>
                <p class="section-copy">Edita la noticia destacada, filtros, tarjetas, bloque de boletin y paginacion desde un solo lugar.</p>
            </div>
            <div class="section-metrics">
                <span class="pill {{ $page->is_active ? 'pill-ok' : 'pill-off' }}">{{ $page->is_active ? 'Publicada' : 'Oculta' }}</span>
                <span class="pill pill-off">{{ count($newsGrid['items']) }} noticias</span>
                <span class="pill pill-off">{{ count($categoryFilters['items']) }} filtros</span>
                <span class="pill pill-off">{{ count($pagination['items']) }} paginas</span>
            </div>
        </div>
    </div>

    <div class="card-grid">
        <div class="spot-card"><span>Destacado</span><strong>{{ count($featuredStory['items']) }}</strong><p>Hero principal administrable con llamada a la accion.</p></div>
        <div class="spot-card"><span>Noticias</span><strong>{{ count($newsGrid['items']) }}</strong><p>Tarjetas de noticias listas para edicion visual.</p></div>
        <div class="spot-card"><span>Boletin</span><strong>1</strong><p>Bloque de suscripcion y paginacion editable.</p></div>
    </div>

    @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="notice notice-error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="editor-layout">
            <aside class="editor-sidebar">
                <div class="editor-nav">
                    <h3>Secciones de noticias</h3>
                    <p>Esta vista centraliza el contenido editorial sin duplicar el layout global.</p>
                    <div class="editor-nav-list">
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'design_text' }" @click="go('design_text')"><strong>Diseno</strong><span>SEO y tema visual</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'featured' }" @click="go('featured')"><strong>Destacado</strong><span>Hero principal</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'filters' }" @click="go('filters')"><strong>Filtros</strong><span>Categorias y busqueda</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'grid' }" @click="go('grid')"><strong>Grid</strong><span>Tarjetas de noticias</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'newsletter' }" @click="go('newsletter')"><strong>Boletin</strong><span>Suscripcion y paginacion</span></button>
                    </div>
                </div>
            </aside>

            <div class="editor-main">
                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Base editorial</div>
                            <h3 class="section-title">Configuracion general</h3>
                            <p class="section-copy">Gestiona la informacion principal y la identidad visual de la pagina.</p>
                        </div>
                        <div class="section-metrics"><span class="pill pill-off">SEO</span><span class="pill pill-off">Tema</span><span class="pill pill-off">Estado</span></div>
                    </div>
                    <div class="design-grid">
                        <div class="subpanel span-8">
                            <h4>Informacion principal</h4>
                            <div class="grid grid-2">
                                <div class="field"><label>Nombre interno</label><input type="text" name="name" value="{{ old('name', $page->name) }}" required></div>
                                <div class="field"><label>Slug</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required></div>
                                <div class="field"><label>Titulo SEO</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                                <div class="field"><label>Descripcion SEO</label><textarea name="meta_description" class="field-small">{{ old('meta_description', $page->meta_description) }}</textarea></div>
                            </div>
                        </div>
                        <div class="subpanel span-4">
                            <h4>Estado del sitio</h4>
                            <label style="display:flex; gap:10px; align-items:center; font-weight:800; margin-top:16px;"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>Pagina activa</label>
                            <div class="image-frame" style="margin-top:16px;">
                                <strong>Layout compartido</strong>
                                <p style="margin-top:10px;">El header y el footer visibles en frontend se leen desde Home.</p>
                            </div>
                        </div>
                        <div class="subpanel span-6">
                            <h4>Paleta</h4>
                            <div class="grid grid-3">
                                <div class="field"><label>Color principal</label><input type="text" name="theme[primary_color]" value="{{ old('theme.primary_color', $editorData['theme']['primary_color'] ?? '#20539a') }}"></div>
                                <div class="field"><label>Color secundario</label><input type="text" name="theme[secondary_color]" value="{{ old('theme.secondary_color', $editorData['theme']['secondary_color'] ?? '#102542') }}"></div>
                                <div class="field"><label>Color acento</label><input type="text" name="theme[accent_color]" value="{{ old('theme.accent_color', $editorData['theme']['accent_color'] ?? '#f3b53f') }}"></div>
                            </div>
                        </div>
                        <div class="subpanel span-6">
                            <h4>Logo referencial</h4>
                            <div class="grid grid-2">
                                <div class="field"><label>URL del logo</label><input type="text" name="theme[logo_url]" value="{{ old('theme.logo_url', $editorData['theme']['logo_url'] ?? '') }}"></div>
                                <div class="field"><label>Subir logo</label><input type="file" name="theme[logo_file]" accept="image/*"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'featured'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Hero editorial</div><h3 class="section-title">Noticia destacada</h3><p class="section-copy">Bloque principal con badge, titular, resumen, categoria e imagen.</p></div>
                        <span class="pill pill-off">{{ count($featuredStory['items']) }} destacado(s)</span>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-2">
                            <div class="field"><label>Texto del boton</label><input type="text" name="featured_story[button_label]" value="{{ old('featured_story.button_label', $featuredStory['settings']['button_label'] ?? 'Leer noticia completa') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Elemento destacado</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar destacado</button></div>
                        <div class="stack" data-collection data-base="featured_story[items]" data-template="featured-template">
                            <div data-rows>
                                @foreach ($featuredStory['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Destacado' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Badge</label><input type="text" data-field="badge" value="{{ $item['badge'] ?? '' }}"></div>
                                            <div class="field"><label>Categoria</label><input type="text" data-field="category" value="{{ $item['category'] ?? '' }}"></div>
                                            <div class="field"><label>URL noticia</label><input type="text" data-field="article_url" value="{{ $item['article_url'] ?? '' }}"></div>
                                            <div class="field" style="grid-column:1 / -1;"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>URL imagen</label><input type="text" data-field="image" value="{{ $item['image'] ?? '' }}"></div>
                                            <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*"></div>
                                        </div>
                                        <div class="field" style="margin-top:12px;"><label>Resumen</label><textarea class="field-small" data-field="excerpt">{{ $item['excerpt'] ?? '' }}</textarea></div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'filters'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Navegacion</div><h3 class="section-title">Filtros y busqueda</h3><p class="section-copy">Controla las categorias visibles y el texto del buscador referencial.</p></div>
                    </div>
                    <div class="subpanel">
                        <div class="field"><label>Placeholder del buscador</label><input type="text" name="category_filters[search_placeholder]" value="{{ old('category_filters.search_placeholder', $categoryFilters['settings']['search_placeholder'] ?? 'Buscar noticias...') }}"></div>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Categorias visibles</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar filtro</button></div>
                        <div class="stack" data-collection data-base="category_filters[items]" data-template="filter-template">
                            <div data-rows>
                                @foreach ($categoryFilters['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Filtro' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Etiqueta</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}"></div>
                                            <div class="field"><label>Activo</label><input type="checkbox" data-field="is_active" value="1" {{ !empty($item['is_active']) ? 'checked' : '' }}></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'grid'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Contenido</div><h3 class="section-title">Grid de noticias</h3><p class="section-copy">Tarjetas con fecha, categoria, titular, extracto, imagen y enlace.</p></div>
                        <span class="pill pill-off">{{ count($newsGrid['items']) }} noticia(s)</span>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-3">
                            <div class="field"><label>Titulo de seccion</label><input type="text" name="news_grid[title]" value="{{ old('news_grid.title', $newsGrid['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Subtitulo</label><input type="text" name="news_grid[subtitle]" value="{{ old('news_grid.subtitle', $newsGrid['settings']['subtitle'] ?? '') }}"></div>
                            <div class="field"><label>Texto CTA</label><input type="text" name="news_grid[cta_label]" value="{{ old('news_grid.cta_label', $newsGrid['settings']['cta_label'] ?? 'Leer mas') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Tarjetas de noticias</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar noticia</button></div>
                        <div class="stack" data-collection data-base="news_grid[items]" data-template="news-card-template">
                            <div data-rows>
                                @foreach ($newsGrid['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Noticia' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Fecha</label><input type="text" data-field="date" value="{{ $item['date'] ?? '' }}"></div>
                                            <div class="field"><label>Categoria</label><input type="text" data-field="category" value="{{ $item['category'] ?? '' }}"></div>
                                            <div class="field"><label>URL noticia</label><input type="text" data-field="article_url" value="{{ $item['article_url'] ?? '' }}"></div>
                                            <div class="field" style="grid-column:1 / -1;"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>URL imagen</label><input type="text" data-field="image" value="{{ $item['image'] ?? '' }}"></div>
                                            <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*"></div>
                                        </div>
                                        <div class="field" style="margin-top:12px;"><label>Extracto</label><textarea class="field-small" data-field="excerpt">{{ $item['excerpt'] ?? '' }}</textarea></div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'newsletter'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Captacion</div><h3 class="section-title">Boletin y paginacion</h3><p class="section-copy">Personaliza el bloque amarillo de suscripcion y las paginas visibles al pie.</p></div>
                    </div>
                    <div class="subpanel">
                        <h4>Bloque de boletin</h4>
                        <div class="grid grid-2">
                            <div class="field"><label>Badge</label><input type="text" name="newsletter[badge]" value="{{ old('newsletter.badge', $newsletter['settings']['badge'] ?? '') }}"></div>
                            <div class="field"><label>Titulo</label><input type="text" name="newsletter[title]" value="{{ old('newsletter.title', $newsletter['settings']['title'] ?? '') }}"></div>
                            <div class="field" style="grid-column:1 / -1;"><label>Texto</label><textarea name="newsletter[text]" class="field-small">{{ old('newsletter.text', $newsletter['settings']['text'] ?? '') }}</textarea></div>
                            <div class="field"><label>Placeholder email</label><input type="text" name="newsletter[placeholder]" value="{{ old('newsletter.placeholder', $newsletter['settings']['placeholder'] ?? '') }}"></div>
                            <div class="field"><label>Texto boton</label><input type="text" name="newsletter[button_label]" value="{{ old('newsletter.button_label', $newsletter['settings']['button_label'] ?? '') }}"></div>
                            <div class="field" style="grid-column:1 / -1;"><label>Texto legal</label><input type="text" name="newsletter[legal_text]" value="{{ old('newsletter.legal_text', $newsletter['settings']['legal_text'] ?? '') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-2">
                            <div class="field"><label>Texto boton cargar mas</label><input type="text" name="pagination[load_more_label]" value="{{ old('pagination.load_more_label', $pagination['settings']['load_more_label'] ?? 'Cargar mas noticias') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Items de paginacion</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar pagina</button></div>
                        <div class="stack" data-collection data-base="pagination[items]" data-template="pagination-template">
                            <div data-rows>
                                @foreach ($pagination['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Pagina' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-4" style="margin-top:12px;">
                                            <div class="field"><label>Etiqueta</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}"></div>
                                            <div class="field"><label>URL</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}"></div>
                                            <div class="field"><label>Activo</label><input type="checkbox" data-field="is_active" value="1" {{ !empty($item['is_active']) ? 'checked' : '' }}></div>
                                            <div class="field"><label>Es puntos suspensivos</label><input type="checkbox" data-field="is_ellipsis" value="1" {{ !empty($item['is_ellipsis']) ? 'checked' : '' }}></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <div class="save-dock">
                    <div style="flex:1;">
                        <strong style="display:block; margin-bottom:4px;">Todo listo para guardar</strong>
                        <p>Los cambios impactan directamente en la portada pública de noticias.</p>
                        <div class="field" style="margin-top:12px;">
                            <label>Resumen del cambio</label>
                            <input type="text" name="change_summary" value="{{ old('change_summary') }}" placeholder="Ej: Actualice destacado, grid y boletin">
                        </div>
                    </div>
                    <button type="submit" class="button button-primary">Guardar cambios del diseno</button>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="featured-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Destacado</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-3" style="margin-top:12px;">
            <div class="field"><label>Badge</label><input type="text" data-field="badge"></div>
            <div class="field"><label>Categoria</label><input type="text" data-field="category"></div>
            <div class="field"><label>URL noticia</label><input type="text" data-field="article_url"></div>
            <div class="field" style="grid-column:1 / -1;"><label>Titulo</label><input type="text" data-field="title"></div>
            <div class="field"><label>URL imagen</label><input type="text" data-field="image"></div>
            <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*"></div>
        </div>
        <div class="field" style="margin-top:12px;"><label>Resumen</label><textarea class="field-small" data-field="excerpt"></textarea></div>
        <input type="hidden" data-field="id">
    </div>
</template>

<template id="filter-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Filtro</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-3" style="margin-top:12px;">
            <div class="field"><label>Etiqueta</label><input type="text" data-field="label"></div>
            <div class="field"><label>URL</label><input type="text" data-field="url"></div>
            <div class="field"><label>Activo</label><input type="checkbox" data-field="is_active" value="1"></div>
        </div>
        <input type="hidden" data-field="id">
    </div>
</template>

<template id="news-card-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Noticia</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-3" style="margin-top:12px;">
            <div class="field"><label>Fecha</label><input type="text" data-field="date"></div>
            <div class="field"><label>Categoria</label><input type="text" data-field="category"></div>
            <div class="field"><label>URL noticia</label><input type="text" data-field="article_url"></div>
            <div class="field" style="grid-column:1 / -1;"><label>Titulo</label><input type="text" data-field="title"></div>
            <div class="field"><label>URL imagen</label><input type="text" data-field="image"></div>
            <div class="field"><label>Subir imagen</label><input type="file" data-field="image_file" accept="image/*"></div>
        </div>
        <div class="field" style="margin-top:12px;"><label>Extracto</label><textarea class="field-small" data-field="excerpt"></textarea></div>
        <input type="hidden" data-field="id">
    </div>
</template>

<template id="pagination-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Pagina</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-4" style="margin-top:12px;">
            <div class="field"><label>Etiqueta</label><input type="text" data-field="label"></div>
            <div class="field"><label>URL</label><input type="text" data-field="url"></div>
            <div class="field"><label>Activo</label><input type="checkbox" data-field="is_active" value="1"></div>
            <div class="field"><label>Es puntos suspensivos</label><input type="checkbox" data-field="is_ellipsis" value="1"></div>
        </div>
        <input type="hidden" data-field="id">
    </div>
</template>
@endsection
