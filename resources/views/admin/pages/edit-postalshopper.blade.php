@extends('layouts.admin')

@php
    $hero = $editorData['postalshopper_hero'];
    $intro = $editorData['postalshopper_intro'];
    $steps = $editorData['postalshopper_steps'];
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

        <form id="page-edit-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">General</div>
                        <h3 class="section-title">Configuracion base de Postal Shopper</h3>
                        <p class="section-copy">SEO, colores, logo y estado de publicacion.</p>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label>Slug</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}"></div>
                    <div class="field"><label>Nombre de la pagina</label><input type="text" name="name" value="{{ old('name', $page->name) }}"></div>
                    <div class="field"><label>Titulo SEO</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                    <div class="field"><label>Descripcion SEO</label><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"></div>
                    <div class="field"><label>Color principal</label><input type="text" name="theme[primary_color]" value="{{ old('theme.primary_color', $theme['primary_color'] ?? '#0d47b5') }}"></div>
                    <div class="field"><label>Color secundario</label><input type="text" name="theme[secondary_color]" value="{{ old('theme.secondary_color', $theme['secondary_color'] ?? '#2a4268') }}"></div>
                    <div class="field"><label>Color acento</label><input type="text" name="theme[accent_color]" value="{{ old('theme.accent_color', $theme['accent_color'] ?? '#ffcc18') }}"></div>
                    <div class="field"><label>Logo actual</label><input type="text" name="theme[logo_url]" value="{{ old('theme.logo_url', $theme['logo_url'] ?? '') }}"></div>
                    <div class="field"><label>Subir logo</label><input type="file" name="theme[logo_file]" accept="image/*"></div>
                    <div class="field" style="display:flex; align-items:end;">
                        <label style="display:flex; gap:10px; align-items:center; margin:0; text-transform:none; letter-spacing:0; font-size:14px; color:#123047;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                            Publicar esta pagina
                        </label>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Hero</div><h3 class="section-title">Portada principal</h3></div></div>
                <div class="grid grid-2">
                    <div class="field" style="grid-column:1/-1;"><label>Badge</label><input type="text" name="postalshopper_hero[badge]" value="{{ old('postalshopper_hero.badge', $hero['settings']['badge'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 blanca</label><input type="text" name="postalshopper_hero[title_line_one_white]" value="{{ old('postalshopper_hero.title_line_one_white', $hero['settings']['title_line_one_white'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 amarilla</label><input type="text" name="postalshopper_hero[title_line_one_yellow]" value="{{ old('postalshopper_hero.title_line_one_yellow', $hero['settings']['title_line_one_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 blanca</label><input type="text" name="postalshopper_hero[title_line_two_white]" value="{{ old('postalshopper_hero.title_line_two_white', $hero['settings']['title_line_two_white'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 amarilla</label><input type="text" name="postalshopper_hero[title_line_two_yellow]" value="{{ old('postalshopper_hero.title_line_two_yellow', $hero['settings']['title_line_two_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 3 amarilla</label><input type="text" name="postalshopper_hero[title_line_three_yellow]" value="{{ old('postalshopper_hero.title_line_three_yellow', $hero['settings']['title_line_three_yellow'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto destacado</label><input type="text" name="postalshopper_hero[lead_text]" value="{{ old('postalshopper_hero.lead_text', $hero['settings']['lead_text'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Subtitulo</label><textarea class="field-small" name="postalshopper_hero[subtitle]">{{ old('postalshopper_hero.subtitle', $hero['settings']['subtitle'] ?? '') }}</textarea></div>
                    <div class="field"><label>Boton principal</label><input type="text" name="postalshopper_hero[primary_button_label]" value="{{ old('postalshopper_hero.primary_button_label', $hero['settings']['primary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton principal</label><input type="text" name="postalshopper_hero[primary_button_url]" value="{{ old('postalshopper_hero.primary_button_url', $hero['settings']['primary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Boton secundario</label><input type="text" name="postalshopper_hero[secondary_button_label]" value="{{ old('postalshopper_hero.secondary_button_label', $hero['settings']['secondary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton secundario</label><input type="text" name="postalshopper_hero[secondary_button_url]" value="{{ old('postalshopper_hero.secondary_button_url', $hero['settings']['secondary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Pais origen</label><input type="text" name="postalshopper_hero[map_origin_country]" value="{{ old('postalshopper_hero.map_origin_country', $hero['settings']['map_origin_country'] ?? '') }}"></div>
                    <div class="field"><label>Ciudad origen</label><input type="text" name="postalshopper_hero[map_origin_city]" value="{{ old('postalshopper_hero.map_origin_city', $hero['settings']['map_origin_city'] ?? '') }}"></div>
                    <div class="field"><label>Pais destino</label><input type="text" name="postalshopper_hero[map_destination_country]" value="{{ old('postalshopper_hero.map_destination_country', $hero['settings']['map_destination_country'] ?? '') }}"></div>
                    <div class="field"><label>Ciudad destino</label><input type="text" name="postalshopper_hero[map_destination_city]" value="{{ old('postalshopper_hero.map_destination_city', $hero['settings']['map_destination_city'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Pie del mapa</label><input type="text" name="postalshopper_hero[map_caption]" value="{{ old('postalshopper_hero.map_caption', $hero['settings']['map_caption'] ?? '') }}"></div>
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
                <div class="section-header"><div><div class="section-eyebrow">Intro</div><h3 class="section-title">Narrativa y marketplaces</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Eyebrow</label><input type="text" name="postalshopper_intro[eyebrow]" value="{{ old('postalshopper_intro.eyebrow', $intro['settings']['eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo</label><input type="text" name="postalshopper_intro[title]" value="{{ old('postalshopper_intro.title', $intro['settings']['title'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 1</label><textarea class="field-small" name="postalshopper_intro[paragraph_one]">{{ old('postalshopper_intro.paragraph_one', $intro['settings']['paragraph_one'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 2</label><textarea class="field-small" name="postalshopper_intro[paragraph_two]">{{ old('postalshopper_intro.paragraph_two', $intro['settings']['paragraph_two'] ?? '') }}</textarea></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Chips de tiendas</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar chip</button></div>
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
                <div class="section-header"><div><div class="section-eyebrow">Proceso</div><h3 class="section-title">Pasos del servicio</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="postalshopper_steps[title]" value="{{ old('postalshopper_steps.title', $steps['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="postalshopper_steps[subtitle]" value="{{ old('postalshopper_steps.subtitle', $steps['settings']['subtitle'] ?? '') }}"></div>
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
                                        <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
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
                <div class="section-header"><div><div class="section-eyebrow">Beneficios</div><h3 class="section-title">Tarjetas y banner final</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="postalshopper_benefits[title]" value="{{ old('postalshopper_benefits.title', $benefits['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="postalshopper_benefits[subtitle]" value="{{ old('postalshopper_benefits.subtitle', $benefits['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Titulo banner</label><input type="text" name="postalshopper_benefits[banner_title]" value="{{ old('postalshopper_benefits.banner_title', $benefits['settings']['banner_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto banner</label><input type="text" name="postalshopper_benefits[banner_text]" value="{{ old('postalshopper_benefits.banner_text', $benefits['settings']['banner_text'] ?? '') }}"></div>
                    <div class="field"><label>Boton banner</label><input type="text" name="postalshopper_benefits[banner_button_label]" value="{{ old('postalshopper_benefits.banner_button_label', $benefits['settings']['banner_button_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton banner</label><input type="text" name="postalshopper_benefits[banner_button_url]" value="{{ old('postalshopper_benefits.banner_button_url', $benefits['settings']['banner_button_url'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de beneficios</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="postalshopper_benefits[items]" data-template="postalshopper-benefit-template">
                        <div class="stack" data-rows>
                            @foreach (old('postalshopper_benefits.items', $benefits['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Beneficio' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
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

            <div class="save-dock">
                <div style="flex:1;">
                    <strong style="display:block; margin-bottom:4px;">Guardar pagina {{ $page->name }}</strong>
                    <p>Header y footer se conservan; aqui administras la landing completa de Postal Shopper.</p>
                    <div class="field" style="margin-top:12px;"><label>Resumen del cambio</label><input type="text" name="change_summary" value="{{ old('change_summary') }}"></div>
                </div>
                <button type="submit" class="button button-primary">Guardar cambios del diseno</button>
            </div>
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

        <template id="postalshopper-step-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Paso</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Numero</label><input type="text" data-field="step"></div>
                    <div class="field"><label>Icono</label><input type="text" data-field="icon"></div>
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
                    <div class="field"><label>Icono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripcion</label><textarea class="field-small" data-field="text"></textarea></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>
    </div>
@endsection
