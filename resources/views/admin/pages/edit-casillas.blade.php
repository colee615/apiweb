@extends('layouts.admin')

@php
    $hero = $editorData['casillas_hero']['settings'] ?? [];
    $intro = $editorData['casillas_intro'];
    $benefits = $editorData['casillas_benefits'];
    $sizes = $editorData['casillas_sizes'];
    $requirements = $editorData['casillas_requirements'];
    $theme = $editorData['theme'] ?? [];
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

        @include('admin.pages.partials.header')

    <form id="page-edit-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" data-editor-form enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Esta página</div>
                        <h3 class="section-title">Configuracion base de Casillas</h3>
                        <p class="section-copy">Nombre, enlace y publicación de esta página.</p><div class="field-help">El logo, los colores, el encabezado y el pie se administran desde Configuración global.</div>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label>Dirección de la página</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}"></div>
                    <div class="field"><label>Nombre de la pagina</label><input type="text" name="name" value="{{ old('name', $page->name) }}"></div>
                    <div class="field"><label>Título en buscadores</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                    <div class="field"><label>Descripción en buscadores</label><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"></div>
                    <div class="field" style="display:flex; align-items:end;">
                        <label style="display:flex; gap:10px; align-items:center; margin:0; text-transform:none; letter-spacing:0; font-size:14px; color:#123047;">
                            <input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                            Publicar esta pagina
                        </label>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Portada</div><h3 class="section-title">Portada con fondo fotogradico</h3></div></div>
                <div class="grid grid-2">
                    <div class="field" style="grid-column:1/-1;"><label>Etiqueta destacada</label><input type="text" name="casillas_hero[badge]" value="{{ old('casillas_hero.badge', $hero['badge'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 blanca</label><input type="text" name="casillas_hero[title_line_one_white]" value="{{ old('casillas_hero.title_line_one_white', $hero['title_line_one_white'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 amarilla</label><input type="text" name="casillas_hero[title_line_one_yellow]" value="{{ old('casillas_hero.title_line_one_yellow', $hero['title_line_one_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 blanca</label><input type="text" name="casillas_hero[title_line_two_white]" value="{{ old('casillas_hero.title_line_two_white', $hero['title_line_two_white'] ?? '') }}"></div>
                    <div class="field"><label>Linea destacada amarilla</label><input type="text" name="casillas_hero[highlight_text]" value="{{ old('casillas_hero.highlight_text', $hero['highlight_text'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Subtitulo</label><textarea class="field-small" name="casillas_hero[subtitle]">{{ old('casillas_hero.subtitle', $hero['subtitle'] ?? '') }}</textarea></div>
                    <div class="field"><label>Boton principal</label><input type="text" name="casillas_hero[primary_button_label]" value="{{ old('casillas_hero.primary_button_label', $hero['primary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton principal</label><input type="text" name="casillas_hero[primary_button_url]" value="{{ old('casillas_hero.primary_button_url', $hero['primary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Boton secundario</label><input type="text" name="casillas_hero[secondary_button_label]" value="{{ old('casillas_hero.secondary_button_label', $hero['secondary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton secundario</label><input type="text" name="casillas_hero[secondary_button_url]" value="{{ old('casillas_hero.secondary_button_url', $hero['secondary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Imagen de fondo actual</label><input type="text" name="casillas_hero[background_image]" value="{{ old('casillas_hero.background_image', $hero['background_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen de fondo</label><input type="file" name="casillas_hero[background_image_file]" accept="image/*"></div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Intro</div><h3 class="section-title">Texto y metricas</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Eyebrow</label><input type="text" name="casillas_intro[eyebrow]" value="{{ old('casillas_intro.eyebrow', $intro['settings']['eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo</label><input type="text" name="casillas_intro[title]" value="{{ old('casillas_intro.title', $intro['settings']['title'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 1</label><textarea class="field-small" name="casillas_intro[paragraph_one]">{{ old('casillas_intro.paragraph_one', $intro['settings']['paragraph_one'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 2</label><textarea class="field-small" name="casillas_intro[paragraph_two]">{{ old('casillas_intro.paragraph_two', $intro['settings']['paragraph_two'] ?? '') }}</textarea></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Metricas inferiores</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar metrica</button></div>
                    <div class="stack" data-collection data-base="casillas_intro[items]" data-template="casillas-stat-template">
                        <div class="stack" data-rows>
                            @foreach (old('casillas_intro.items', $intro['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Metrica' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Valor</label><input type="text" data-field="value" value="{{ $item['value'] ?? '' }}"></div>
                                        <div class="field"><label>Label</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Beneficios</div><h3 class="section-title">Tarjetas de valor</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="casillas_benefits[title]" value="{{ old('casillas_benefits.title', $benefits['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="casillas_benefits[subtitle]" value="{{ old('casillas_benefits.subtitle', $benefits['settings']['subtitle'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="casillas_benefits[items]" data-template="casillas-card-template">
                        <div class="stack" data-rows>
                            @foreach (old('casillas_benefits.items', $benefits['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Tamanos</div><h3 class="section-title">Listado de casillas y planes</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="casillas_sizes[title]" value="{{ old('casillas_sizes.title', $sizes['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="casillas_sizes[subtitle]" value="{{ old('casillas_sizes.subtitle', $sizes['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Label de planes</label><input type="text" name="casillas_sizes[plan_label]" value="{{ old('casillas_sizes.plan_label', $sizes['settings']['plan_label'] ?? '') }}"></div>
                    <div class="field"><label>Titulo del panel</label><input type="text" name="casillas_sizes[panel_title]" value="{{ old('casillas_sizes.panel_title', $sizes['settings']['panel_title'] ?? '') }}"></div>
                    <div class="field"><label>Plan trimestral</label><input type="text" name="casillas_sizes[quarterly_label]" value="{{ old('casillas_sizes.quarterly_label', $sizes['settings']['quarterly_label'] ?? '') }}"></div>
                    <div class="field"><label>Plan semestral</label><input type="text" name="casillas_sizes[semiannual_label]" value="{{ old('casillas_sizes.semiannual_label', $sizes['settings']['semiannual_label'] ?? '') }}"></div>
                    <div class="field"><label>Plan anual</label><input type="text" name="casillas_sizes[annual_label]" value="{{ old('casillas_sizes.annual_label', $sizes['settings']['annual_label'] ?? '') }}"></div>
                    <div class="field"><label>etiqueta del anual</label><input type="text" name="casillas_sizes[annual_badge]" value="{{ old('casillas_sizes.annual_badge', $sizes['settings']['annual_badge'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tamanos disponibles</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tamano</button></div>
                    <div class="stack" data-collection data-base="casillas_sizes[items]" data-template="casillas-size-template">
                        <div class="stack" data-rows>
                            @foreach (old('casillas_sizes.items', $sizes['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tamano' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Etiqueta destacada</label><input type="text" data-field="badge" value="{{ $item['badge'] ?? '' }}"></div>
                                        <div class="field"><label>Categoria</label><input type="text" data-field="category" value="{{ $item['category'] ?? '' }}"></div>
                                        <div class="field"><label>Dimensiones</label><input type="text" data-field="dimensions" value="{{ $item['dimensions'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Requisitos</div><h3 class="section-title">Persona natural y juridica</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="casillas_requirements[title]" value="{{ old('casillas_requirements.title', $requirements['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="casillas_requirements[subtitle]" value="{{ old('casillas_requirements.subtitle', $requirements['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Titulo banner</label><input type="text" name="casillas_requirements[banner_title]" value="{{ old('casillas_requirements.banner_title', $requirements['settings']['banner_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto banner</label><input type="text" name="casillas_requirements[banner_text]" value="{{ old('casillas_requirements.banner_text', $requirements['settings']['banner_text'] ?? '') }}"></div>
                    <div class="field"><label>Boton banner</label><input type="text" name="casillas_requirements[banner_button_label]" value="{{ old('casillas_requirements.banner_button_label', $requirements['settings']['banner_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton banner</label><input type="text" name="casillas_requirements[banner_button_url]" value="{{ old('casillas_requirements.banner_button_url', $requirements['settings']['banner_button_url'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de requisitos</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="casillas_requirements[items]" data-template="casillas-requirement-template">
                        <div class="stack" data-rows>
                            @foreach (old('casillas_requirements.items', $requirements['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Linea 1</label><input type="text" data-field="row_one" value="{{ $item['row_one'] ?? '' }}"></div>
                                        <div class="field"><label>Linea 2</label><input type="text" data-field="row_two" value="{{ $item['row_two'] ?? '' }}"></div>
                                        <div class="field"><label>Linea 3</label><input type="text" data-field="row_three" value="{{ $item['row_three'] ?? '' }}"></div>
                                        <div class="field"><label>Linea 4</label><input type="text" data-field="row_four" value="{{ $item['row_four'] ?? '' }}"></div>
                                        <div class="field"><label>Linea 5</label><input type="text" data-field="row_five" value="{{ $item['row_five'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            @include('admin.pages.partials.save')
        </form>

        <template id="casillas-stat-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Metrica</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Valor</label><input type="text" data-field="value"></div>
                    <div class="field"><label>Label</label><input type="text" data-field="label"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="casillas-card-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Tarjeta</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text"></textarea></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="casillas-size-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Tamano</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field"><label>Etiqueta destacada</label><input type="text" data-field="badge"></div>
                    <div class="field"><label>Categoria</label><input type="text" data-field="category"></div>
                    <div class="field"><label>Dimensiones</label><input type="text" data-field="dimensions"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text"></textarea></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="casillas-requirement-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Tarjeta</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field"><label>Linea 1</label><input type="text" data-field="row_one"></div>
                    <div class="field"><label>Linea 2</label><input type="text" data-field="row_two"></div>
                    <div class="field"><label>Linea 3</label><input type="text" data-field="row_three"></div>
                    <div class="field"><label>Linea 4</label><input type="text" data-field="row_four"></div>
                    <div class="field"><label>Linea 5</label><input type="text" data-field="row_five"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>
    </div>
@endsection
