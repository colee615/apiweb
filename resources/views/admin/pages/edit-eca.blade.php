@extends('layouts.admin')

@php
    $hero = $editorData['eca_hero']['settings'] ?? [];
    $intro = $editorData['eca_intro'];
    $rates = $editorData['eca_rates'];
    $coverage = $editorData['eca_coverage'];
    $solutions = $editorData['eca_solutions'];
    $cta = $editorData['eca_cta']['settings'] ?? [];
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
                        <h3 class="section-title">Configuracion base de {{ $page->name }}</h3>
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
                <div class="section-header"><div><div class="section-eyebrow">Portada</div><h3 class="section-title">Bloque principal</h3></div></div>
                <div class="grid grid-2">
                    <div class="field" style="grid-column:1/-1;"><label>Etiqueta destacada</label><input type="text" name="eca_hero[badge]" value="{{ old('eca_hero.badge', $hero['badge'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 azul</label><input type="text" name="eca_hero[title_line_one_blue]" value="{{ old('eca_hero.title_line_one_blue', $hero['title_line_one_blue'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 amarillo</label><input type="text" name="eca_hero[title_line_one_yellow]" value="{{ old('eca_hero.title_line_one_yellow', $hero['title_line_one_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 amarillo</label><input type="text" name="eca_hero[title_line_two_yellow]" value="{{ old('eca_hero.title_line_two_yellow', $hero['title_line_two_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 3 azul</label><input type="text" name="eca_hero[title_line_three_blue]" value="{{ old('eca_hero.title_line_three_blue', $hero['title_line_three_blue'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Subtitulo</label><textarea class="field-small" name="eca_hero[subtitle]">{{ old('eca_hero.subtitle', $hero['subtitle'] ?? '') }}</textarea></div>
                    <div class="field"><label>Boton principal</label><input type="text" name="eca_hero[primary_button_label]" value="{{ old('eca_hero.primary_button_label', $hero['primary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton principal</label><input type="text" name="eca_hero[primary_button_url]" value="{{ old('eca_hero.primary_button_url', $hero['primary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Boton secundario</label><input type="text" name="eca_hero[secondary_button_label]" value="{{ old('eca_hero.secondary_button_label', $hero['secondary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton secundario</label><input type="text" name="eca_hero[secondary_button_url]" value="{{ old('eca_hero.secondary_button_url', $hero['secondary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Ícono del bloque</label><input type="text" name="eca_hero[visual_icon]" value="{{ old('eca_hero.visual_icon', $hero['visual_icon'] ?? '') }}"></div>
                    <div class="field"><label>Imagen visual actual</label><input type="text" name="eca_hero[visual_image]" value="{{ old('eca_hero.visual_image', $hero['visual_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen visual</label><input type="file" name="eca_hero[visual_image_file]" accept="image/*"></div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Intro</div><h3 class="section-title">Problema y solucion</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Eyebrow</label><input type="text" name="eca_intro[eyebrow]" value="{{ old('eca_intro.eyebrow', $intro['settings']['eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo</label><input type="text" name="eca_intro[title]" value="{{ old('eca_intro.title', $intro['settings']['title'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 1</label><textarea class="field-small" name="eca_intro[paragraph_one]">{{ old('eca_intro.paragraph_one', $intro['settings']['paragraph_one'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 2</label><textarea class="field-small" name="eca_intro[paragraph_two]">{{ old('eca_intro.paragraph_two', $intro['settings']['paragraph_two'] ?? '') }}</textarea></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Iconos de segmentos</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar item</button></div>
                    <div class="stack" data-collection data-base="eca_intro[items]" data-template="eca-simple-template">
                        <div class="stack" data-rows>
                            @foreach (old('eca_intro.items', $intro['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Item' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Tarifas</div><h3 class="section-title">Bloque azul de tarifas</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="eca_rates[title]" value="{{ old('eca_rates.title', $rates['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="eca_rates[subtitle]" value="{{ old('eca_rates.subtitle', $rates['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Titulo nota</label><input type="text" name="eca_rates[note_title]" value="{{ old('eca_rates.note_title', $rates['settings']['note_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto nota</label><input type="text" name="eca_rates[note_text]" value="{{ old('eca_rates.note_text', $rates['settings']['note_text'] ?? '') }}"></div>
                    <div class="field"><label>Boton botón</label><input type="text" name="eca_rates[primary_button_label]" value="{{ old('eca_rates.primary_button_label', $rates['settings']['primary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace botón</label><input type="text" name="eca_rates[primary_button_url]" value="{{ old('eca_rates.primary_button_url', $rates['settings']['primary_button_url'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de metricas</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="eca_rates[items]" data-template="eca-stat-template">
                        <div class="stack" data-rows>
                            @foreach (old('eca_rates.items', $rates['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Valor</label><input type="text" data-field="value" value="{{ $item['value'] ?? '' }}"></div>
                                        <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Texto</label><input type="text" data-field="text" value="{{ $item['text'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Cobertura</div><h3 class="section-title">Alcance nacional e internacional</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="eca_coverage[title]" value="{{ old('eca_coverage.title', $coverage['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="eca_coverage[subtitle]" value="{{ old('eca_coverage.subtitle', $coverage['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Titulo nota</label><input type="text" name="eca_coverage[note_title]" value="{{ old('eca_coverage.note_title', $coverage['settings']['note_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto nota</label><input type="text" name="eca_coverage[note_text]" value="{{ old('eca_coverage.note_text', $coverage['settings']['note_text'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de cobertura</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="eca_coverage[items]" data-template="eca-coverage-template">
                        <div class="stack" data-rows>
                            @foreach (old('eca_coverage.items', $coverage['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Eyebrow</label><input type="text" data-field="eyebrow" value="{{ $item['eyebrow'] ?? '' }}"></div>
                                        <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field"><label>Fila 1 label</label><input type="text" data-field="row_one_label" value="{{ $item['row_one_label'] ?? '' }}"></div>
                                        <div class="field"><label>Fila 1 valor</label><input type="text" data-field="row_one_value" value="{{ $item['row_one_value'] ?? '' }}"></div>
                                        <div class="field"><label>Fila 2 label</label><input type="text" data-field="row_two_label" value="{{ $item['row_two_label'] ?? '' }}"></div>
                                        <div class="field"><label>Fila 2 valor</label><input type="text" data-field="row_two_value" value="{{ $item['row_two_value'] ?? '' }}"></div>
                                        <div class="field"><label>Fila 3 label</label><input type="text" data-field="row_three_label" value="{{ $item['row_three_label'] ?? '' }}"></div>
                                        <div class="field"><label>Fila 3 valor</label><input type="text" data-field="row_three_value" value="{{ $item['row_three_value'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Soluciones</div><h3 class="section-title">Sectores y usos corporativos</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="eca_solutions[title]" value="{{ old('eca_solutions.title', $solutions['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="eca_solutions[subtitle]" value="{{ old('eca_solutions.subtitle', $solutions['settings']['subtitle'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de soluciones</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="eca_solutions[items]" data-template="ems-card-template">
                        <div class="stack" data-rows>
                            @foreach (old('eca_solutions.items', $solutions['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                        <div class="field"><label>Etiqueta destacada</label><input type="text" data-field="badge" value="{{ $item['badge'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">CTA final</div><h3 class="section-title">Contacto y cotizacion</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="eca_cta[title]" value="{{ old('eca_cta.title', $cta['title'] ?? '') }}"></div>
                    <div class="field"><label>Texto principal</label><input type="text" name="eca_cta[text]" value="{{ old('eca_cta.text', $cta['text'] ?? '') }}"></div>
                    <div class="field"><label>Label telefono</label><input type="text" name="eca_cta[phone_label]" value="{{ old('eca_cta.phone_label', $cta['phone_label'] ?? '') }}"></div>
                    <div class="field"><label>Valor telefono</label><input type="text" name="eca_cta[phone_value]" value="{{ old('eca_cta.phone_value', $cta['phone_value'] ?? '') }}"></div>
                    <div class="field"><label>Label email</label><input type="text" name="eca_cta[email_label]" value="{{ old('eca_cta.email_label', $cta['email_label'] ?? '') }}"></div>
                    <div class="field"><label>Valor email</label><input type="text" name="eca_cta[email_value]" value="{{ old('eca_cta.email_value', $cta['email_value'] ?? '') }}"></div>
                    <div class="field"><label>Label direccion</label><input type="text" name="eca_cta[address_label]" value="{{ old('eca_cta.address_label', $cta['address_label'] ?? '') }}"></div>
                    <div class="field"><label>Valor direccion</label><input type="text" name="eca_cta[address_value]" value="{{ old('eca_cta.address_value', $cta['address_value'] ?? '') }}"></div>
                    <div class="field"><label>Texto inferior</label><input type="text" name="eca_cta[footnote]" value="{{ old('eca_cta.footnote', $cta['footnote'] ?? '') }}"></div>
                    <div class="field"><label>Titulo QR</label><input type="text" name="eca_cta[qr_title]" value="{{ old('eca_cta.qr_title', $cta['qr_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto QR</label><input type="text" name="eca_cta[qr_text]" value="{{ old('eca_cta.qr_text', $cta['qr_text'] ?? '') }}"></div>
                    <div class="field"><label>Imagen QR actual</label><input type="text" name="eca_cta[qr_image]" value="{{ old('eca_cta.qr_image', $cta['qr_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir QR</label><input type="file" name="eca_cta[qr_image_file]" accept="image/*"></div>
                    <div class="field"><label>Boton</label><input type="text" name="eca_cta[button_label]" value="{{ old('eca_cta.button_label', $cta['button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton</label><input type="text" name="eca_cta[button_url]" value="{{ old('eca_cta.button_url', $cta['button_url'] ?? '') }}"></div>
                </div>
            </section>

            @include('admin.pages.partials.save')
        </form>

        <template id="eca-simple-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Item</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="eca-stat-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Tarjeta</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Valor</label><input type="text" data-field="value"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto</label><input type="text" data-field="text"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="eca-coverage-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Tarjeta</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Eyebrow</label><input type="text" data-field="eyebrow"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Fila 1 label</label><input type="text" data-field="row_one_label"></div>
                    <div class="field"><label>Fila 1 valor</label><input type="text" data-field="row_one_value"></div>
                    <div class="field"><label>Fila 2 label</label><input type="text" data-field="row_two_label"></div>
                    <div class="field"><label>Fila 2 valor</label><input type="text" data-field="row_two_value"></div>
                    <div class="field"><label>Fila 3 label</label><input type="text" data-field="row_three_label"></div>
                    <div class="field"><label>Fila 3 valor</label><input type="text" data-field="row_three_value"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>
    </div>
@endsection
