@extends('layouts.admin')

@php
    $hero = $editorData['postalshopper_hero'];
    $destinations = $editorData['postalshopper_destinations'];
    $intro = $editorData['postalshopper_intro'];
    $steps = $editorData['postalshopper_steps'];
    $calculator = $editorData['postalshopper_calculator'];
    $benefits = $editorData['postalshopper_benefits'];
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
                        <h3 class="section-title">Datos de esta página</h3>
                        <p class="section-copy">Información para buscadores y estado de publicación.</p><div class="field-help">El logo, los colores, el encabezado y el pie se administran desde Configuración global.</div>
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
                <div class="section-header"><div><div class="section-eyebrow">Portada</div><h3 class="section-title">Portada principal</h3><label class="field-help" style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="hidden" name="postalshopper_hero[section_active]" value="0"><input type="checkbox" name="postalshopper_hero[section_active]" value="1" {{ old('postalshopper_hero.section_active', $hero['is_active']) ? 'checked' : '' }}> Mostrar esta sección</label></div></div>
                <div class="grid grid-2">
                    <div class="field" style="grid-column:1/-1;"><label>Etiqueta destacada</label><input type="text" name="postalshopper_hero[badge]" value="{{ old('postalshopper_hero.badge', $hero['settings']['badge'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 blanca</label><input type="text" name="postalshopper_hero[title_line_one_white]" value="{{ old('postalshopper_hero.title_line_one_white', $hero['settings']['title_line_one_white'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 amarilla</label><input type="text" name="postalshopper_hero[title_line_one_yellow]" value="{{ old('postalshopper_hero.title_line_one_yellow', $hero['settings']['title_line_one_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 blanca</label><input type="text" name="postalshopper_hero[title_line_two_white]" value="{{ old('postalshopper_hero.title_line_two_white', $hero['settings']['title_line_two_white'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 amarilla</label><input type="text" name="postalshopper_hero[title_line_two_yellow]" value="{{ old('postalshopper_hero.title_line_two_yellow', $hero['settings']['title_line_two_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 3 amarilla</label><input type="text" name="postalshopper_hero[title_line_three_yellow]" value="{{ old('postalshopper_hero.title_line_three_yellow', $hero['settings']['title_line_three_yellow'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto destacado</label><input type="text" name="postalshopper_hero[lead_text]" value="{{ old('postalshopper_hero.lead_text', $hero['settings']['lead_text'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Subtitulo</label><textarea class="field-small" name="postalshopper_hero[subtitle]">{{ old('postalshopper_hero.subtitle', $hero['settings']['subtitle'] ?? '') }}</textarea></div>
                    <div class="field"><label>Texto botón principal</label><input type="text" name="postalshopper_hero[primary_button_label]" value="{{ old('postalshopper_hero.primary_button_label', $hero['settings']['primary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace de respaldo</label><input type="text" name="postalshopper_hero[primary_button_url]" value="{{ old('postalshopper_hero.primary_button_url', $hero['settings']['primary_button_url'] ?? '') }}"><small class="field-help">El botón “Cómo funciona” prioriza el video configurado en Pasos del servicio.</small></div>
                    <div class="field"><label>Boton secundario</label><input type="text" name="postalshopper_hero[secondary_button_label]" value="{{ old('postalshopper_hero.secondary_button_label', $hero['settings']['secondary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton secundario</label><input type="text" name="postalshopper_hero[secondary_button_url]" value="{{ old('postalshopper_hero.secondary_button_url', $hero['settings']['secondary_button_url'] ?? '') }}"><small class="field-help">Por defecto usa el enlace del servicio directo configurado en esta página.</small></div>
                    <div class="field"><label>Imagen de fondo actual</label><input type="text" name="postalshopper_hero[background_image]" value="{{ old('postalshopper_hero.background_image', $hero['settings']['background_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen de fondo</label><input type="file" name="postalshopper_hero[background_image_file]" accept="image/*"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Indicadores inferiores</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar indicador</button></div>
                    <div class="stack" data-collection data-base="postalshopper_hero[items]" data-template="postalshopper-hero-stat-template">
                        <div class="stack" data-rows>
                            @foreach (old('postalshopper_hero.items', $hero['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['value'] ?? 'Indicador' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
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
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Disponibilidad</div>
                        <h3 class="section-title">Envíos directos y cobertura</h3>
                        <p class="section-copy">Administra el servicio directo desde Estados Unidos y la tarjeta informativa que aparece antes de “Cómo funciona”.</p>
                        <label class="field-help" style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="hidden" name="postalshopper_destinations[section_active]" value="0"><input type="checkbox" name="postalshopper_destinations[section_active]" value="1" {{ old('postalshopper_destinations.section_active', $destinations['is_active']) ? 'checked' : '' }}> Mostrar esta sección</label>
                    </div>
                </div>
                <div class="grid grid-2" style="margin-bottom:18px;">
                    <div class="field"><label>Título de la tarjeta informativa</label><input type="text" name="postalshopper_destinations[upcoming_label]" value="{{ old('postalshopper_destinations.upcoming_label', $destinations['settings']['upcoming_label'] ?? 'Nuevos envíos') }}"></div>
                    <div class="field"><label>Estado de la tarjeta informativa</label><input type="text" name="postalshopper_destinations[upcoming_status]" value="{{ old('postalshopper_destinations.upcoming_status', $destinations['settings']['upcoming_status'] ?? 'Muy pronto') }}"></div>
                    <label class="field-help" style="display:flex;align-items:center;gap:8px;"><input type="hidden" name="postalshopper_destinations[upcoming_enabled]" value="0"><input type="checkbox" name="postalshopper_destinations[upcoming_enabled]" value="1" {{ old('postalshopper_destinations.upcoming_enabled', $destinations['settings']['upcoming_enabled'] ?? true) ? 'checked' : '' }}> Mostrar la tarjeta informativa</label>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Servicios</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar servicio</button></div>
                    <div class="stack" data-collection data-base="postalshopper_destinations[items]" data-template="postalshopper-destination-template">
                        <div class="stack" data-rows>
                            @foreach (old('postalshopper_destinations.items', $destinations['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar">
                                        <div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Servicio' }}</strong></div>
                                        <div class="actions">
                                            <div class="field" style="min-width:140px; margin:0;"><label>Estado</label><select data-field="item_active"><option value="1" {{ ($item['item_active'] ?? true) ? 'selected' : '' }}>Visible</option><option value="0" {{ !($item['item_active'] ?? true) ? 'selected' : '' }}>Oculto</option></select></div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                    </div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Servicio / país</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}" placeholder="Estados Unidos"></div>
                                        <div class="field"><label>Bandera (emoji o imagen)</label><input type="text" data-field="flag" value="{{ $item['flag'] ?? '' }}" placeholder="🇺🇸 o /flags/us.svg"></div>
                                        <div class="field"><label>Estado mostrado</label><input type="text" data-field="status" value="{{ $item['status'] ?? '' }}" placeholder="Envío directo / Muy pronto"></div>
                                        <div class="field"><label>Enlace del servicio</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}" placeholder="/contacto o https://... "></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Intro</div><h3 class="section-title">Narrativa y marketplaces</h3><label class="field-help" style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="hidden" name="postalshopper_intro[section_active]" value="0"><input type="checkbox" name="postalshopper_intro[section_active]" value="1" {{ old('postalshopper_intro.section_active', $intro['is_active']) ? 'checked' : '' }}> Mostrar esta sección</label></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Eyebrow</label><input type="text" name="postalshopper_intro[eyebrow]" value="{{ old('postalshopper_intro.eyebrow', $intro['settings']['eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo</label><input type="text" name="postalshopper_intro[title]" value="{{ old('postalshopper_intro.title', $intro['settings']['title'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 1</label><textarea class="field-small" name="postalshopper_intro[paragraph_one]">{{ old('postalshopper_intro.paragraph_one', $intro['settings']['paragraph_one'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 2</label><textarea class="field-small" name="postalshopper_intro[paragraph_two]">{{ old('postalshopper_intro.paragraph_two', $intro['settings']['paragraph_two'] ?? '') }}</textarea></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>etiquetas cortas de tiendas</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar chip</button></div>
                    <div class="stack" data-collection data-base="postalshopper_intro[items]" data-template="postalshopper-chip-template">
                        <div class="stack" data-rows>
                            @foreach (old('postalshopper_intro.items', $intro['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Chip' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Label</label><input type="text" data-field="label" value="{{ $item['label'] ?? '' }}"></div>
                                        <div class="field"><label>Logo actual</label><input type="text" data-field="logo" value="{{ $item['logo'] ?? '' }}" placeholder="/storage/cms/postalshopper/logos/logo.svg"></div>
                                        <div class="field"><label>Subir logo</label><input type="file" data-field="logo_file" accept="image/*" data-preview-input></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Proceso</div><h3 class="section-title">Pasos del servicio</h3><label class="field-help" style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="hidden" name="postalshopper_steps[section_active]" value="0"><input type="checkbox" name="postalshopper_steps[section_active]" value="1" {{ old('postalshopper_steps.section_active', $steps['is_active']) ? 'checked' : '' }}> Mostrar esta sección</label></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="postalshopper_steps[title]" value="{{ old('postalshopper_steps.title', $steps['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="postalshopper_steps[subtitle]" value="{{ old('postalshopper_steps.subtitle', $steps['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Enlace del tutorial (video)</label><input type="text" name="postalshopper_steps[tutorial_url]" value="{{ old('postalshopper_steps.tutorial_url', $steps['settings']['tutorial_url'] ?? '') }}" placeholder="URL del video"><small class="field-help">El botón “Cómo funciona” abre este video. Si está vacío, usa el enlace principal de la portada.</small></div>
                    <div class="field"><label>Texto del tutorial</label><input type="text" name="postalshopper_steps[tutorial_label]" value="{{ old('postalshopper_steps.tutorial_label', $steps['settings']['tutorial_label'] ?? 'Ver tutorial completo') }}"></div>
                    <div class="field"><label>Duración del tutorial</label><input type="text" name="postalshopper_steps[tutorial_duration]" value="{{ old('postalshopper_steps.tutorial_duration', $steps['settings']['tutorial_duration'] ?? '1:30 min') }}"></div>
                    <div class="field"><label>Imagen actual del tutorial</label><input type="text" name="postalshopper_steps[tutorial_image]" value="{{ old('postalshopper_steps.tutorial_image', $steps['settings']['tutorial_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir miniatura del tutorial</label><input type="file" name="postalshopper_steps[tutorial_image_file]" accept="image/*"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de pasos</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar paso</button></div>
                    <div class="stack" data-collection data-base="postalshopper_steps[items]" data-template="postalshopper-step-template">
                        <div class="stack" data-rows>
                            @foreach (old('postalshopper_steps.items', $steps['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Paso' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Numero</label><input type="text" data-field="step" value="{{ $item['step'] ?? '' }}"></div>
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Titulo</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
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
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Tarifas</div>
                        <h3 class="section-title">Calculadora de paquetería</h3>
                        <p class="section-copy">Edita los textos, la imagen y cada tarifa que aparece en el cotizador.</p>
                        <label class="field-help" style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="hidden" name="postalshopper_calculator[section_active]" value="0"><input type="checkbox" name="postalshopper_calculator[section_active]" value="1" {{ old('postalshopper_calculator.section_active', $calculator['is_active']) ? 'checked' : '' }}> Mostrar esta sección</label>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label>Etiqueta superior</label><input type="text" name="postalshopper_calculator[eyebrow]" value="{{ old('postalshopper_calculator.eyebrow', $calculator['settings']['eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Texto del título</label><input type="text" name="postalshopper_calculator[title]" value="{{ old('postalshopper_calculator.title', $calculator['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Texto destacado del título</label><input type="text" name="postalshopper_calculator[title_accent]" value="{{ old('postalshopper_calculator.title_accent', $calculator['settings']['title_accent'] ?? '') }}"></div>
                    <div class="field"><label>Descripción</label><input type="text" name="postalshopper_calculator[description]" value="{{ old('postalshopper_calculator.description', $calculator['settings']['description'] ?? '') }}"></div>
                    <div class="field"><label>Texto para elegir el peso</label><input type="text" name="postalshopper_calculator[weight_title]" value="{{ old('postalshopper_calculator.weight_title', $calculator['settings']['weight_title'] ?? '') }}"></div>
                    <div class="field"><label>Ayuda del peso</label><input type="text" name="postalshopper_calculator[weight_help]" value="{{ old('postalshopper_calculator.weight_help', $calculator['settings']['weight_help'] ?? '') }}"></div>
                    <div class="field"><label>Unidad singular</label><input type="text" name="postalshopper_calculator[weight_unit_singular]" value="{{ old('postalshopper_calculator.weight_unit_singular', $calculator['settings']['weight_unit_singular'] ?? 'libra') }}"></div>
                    <div class="field"><label>Unidad plural</label><input type="text" name="postalshopper_calculator[weight_unit_plural]" value="{{ old('postalshopper_calculator.weight_unit_plural', $calculator['settings']['weight_unit_plural'] ?? 'libras') }}"></div>
                    <div class="field"><label>Moneda</label><input type="text" name="postalshopper_calculator[currency_label]" value="{{ old('postalshopper_calculator.currency_label', $calculator['settings']['currency_label'] ?? '$us') }}" placeholder="$us"></div>
                    <div class="field"><label>Etiqueta del total</label><input type="text" name="postalshopper_calculator[total_label]" value="{{ old('postalshopper_calculator.total_label', $calculator['settings']['total_label'] ?? '') }}"></div>
                    <div class="field"><label>Texto pequeño del personaje</label><input type="text" name="postalshopper_calculator[caption_label]" value="{{ old('postalshopper_calculator.caption_label', $calculator['settings']['caption_label'] ?? '') }}"></div>
                    <div class="field"><label>Texto principal del personaje</label><input type="text" name="postalshopper_calculator[caption_text]" value="{{ old('postalshopper_calculator.caption_text', $calculator['settings']['caption_text'] ?? '') }}"></div>
                    <div class="field"><label>Imagen actual</label><input type="text" name="postalshopper_calculator[image]" value="{{ old('postalshopper_calculator.image', $calculator['settings']['image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen del cotizador</label><input type="file" name="postalshopper_calculator[image_file]" accept="image/*"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto alternativo de la imagen</label><input type="text" name="postalshopper_calculator[image_alt]" value="{{ old('postalshopper_calculator.image_alt', $calculator['settings']['image_alt'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarifas por peso</h4><p class="field-help">Usa punto decimal en el precio, por ejemplo 10.59. Debe quedar al menos una tarifa.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar tarifa</button></div>
                    <div class="stack" data-collection data-base="postalshopper_calculator[items]" data-template="postalshopper-rate-template">
                        <div class="stack" data-rows>
                            @foreach (old('postalshopper_calculator.items', $calculator['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar">
                                        <div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ isset($item['weight']) ? $item['weight'] . ' lb' : 'Tarifa' }}</strong></div>
                                        <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                    </div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Peso (libras)</label><input type="number" min="1" max="1000" step="1" data-field="weight" value="{{ $item['weight'] ?? '' }}"></div>
                                        <div class="field"><label>Precio</label><input type="number" min="0" max="1000000" step="0.01" data-field="price" value="{{ $item['price'] ?? '' }}"></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Beneficios</div><h3 class="section-title">Tarjetas y banner final</h3><label class="field-help" style="display:flex;align-items:center;gap:8px;margin-top:8px;"><input type="hidden" name="postalshopper_benefits[section_active]" value="0"><input type="checkbox" name="postalshopper_benefits[section_active]" value="1" {{ old('postalshopper_benefits.section_active', $benefits['is_active']) ? 'checked' : '' }}> Mostrar esta sección</label></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="postalshopper_benefits[title]" value="{{ old('postalshopper_benefits.title', $benefits['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="postalshopper_benefits[subtitle]" value="{{ old('postalshopper_benefits.subtitle', $benefits['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Titulo banner</label><input type="text" name="postalshopper_benefits[banner_title]" value="{{ old('postalshopper_benefits.banner_title', $benefits['settings']['banner_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto banner</label><input type="text" name="postalshopper_benefits[banner_text]" value="{{ old('postalshopper_benefits.banner_text', $benefits['settings']['banner_text'] ?? '') }}"></div>
                    <div class="field"><label>Boton banner</label><input type="text" name="postalshopper_benefits[banner_button_label]" value="{{ old('postalshopper_benefits.banner_button_label', $benefits['settings']['banner_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace boton banner</label><input type="text" name="postalshopper_benefits[banner_button_url]" value="{{ old('postalshopper_benefits.banner_button_url', $benefits['settings']['banner_button_url'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de beneficios</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="postalshopper_benefits[items]" data-template="postalshopper-benefit-template">
                        <div class="stack" data-rows>
                            @foreach (old('postalshopper_benefits.items', $benefits['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Beneficio' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
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

            @include('admin.pages.partials.save')
        </form>

        <template id="postalshopper-hero-stat-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Indicador</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Valor</label><input type="text" data-field="value"></div>
                    <div class="field"><label>Label</label><input type="text" data-field="label"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="postalshopper-chip-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Chip</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Label</label><input type="text" data-field="label"></div>
                    <div class="field"><label>Logo actual</label><input type="text" data-field="logo" placeholder="/storage/cms/postalshopper/logos/logo.svg"></div>
                    <div class="field"><label>Subir logo</label><input type="file" data-field="logo_file" accept="image/*" data-preview-input></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="postalshopper-destination-template">
            <div class="repeater-card" data-row>
                <div class="toolbar">
                    <div class="actions"><span class="drag-handle" data-drag>::</span><strong>Nuevo servicio</strong></div>
                    <div class="actions">
                        <div class="field" style="min-width:140px; margin:0;"><label>Estado</label><select data-field="item_active"><option value="1" selected>Visible</option><option value="0">Oculto</option></select></div>
                        <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                    </div>
                </div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Servicio / país</label><input type="text" data-field="label" placeholder="Estados Unidos"></div>
                    <div class="field"><label>Bandera (emoji o imagen)</label><input type="text" data-field="flag" placeholder="🇺🇸 o /flags/us.svg"></div>
                    <div class="field"><label>Estado mostrado</label><input type="text" data-field="status" placeholder="Envío directo / Muy pronto"></div>
                    <div class="field"><label>Enlace del servicio</label><input type="text" data-field="url" placeholder="/contacto o https://..."></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="postalshopper-step-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Paso</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Numero</label><input type="text" data-field="step"></div>
                    <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text"></textarea></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="postalshopper-benefit-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Beneficio</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text"></textarea></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="postalshopper-rate-template">
            <div class="repeater-card" data-row>
                <div class="toolbar">
                    <div class="actions"><span class="drag-handle" data-drag>::</span><strong>Nueva tarifa</strong></div>
                    <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                </div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Peso (libras)</label><input type="number" min="1" max="1000" step="1" data-field="weight"></div>
                    <div class="field"><label>Precio</label><input type="number" min="0" max="1000000" step="0.01" data-field="price"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>
    </div>
@endsection
