@extends('layouts.admin')

@php
    $hero = $editorData['encomienda_hero']['settings'] ?? [];
    $intro = $editorData['encomienda_intro'];
    $features = $editorData['encomienda_features'];
    $faq = $editorData['encomienda_faq'];
    $cta = $editorData['encomienda_cta'];
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
                        <h3 class="section-title">Configuracion base de Encomienda</h3>
                        <p class="section-copy">SEO, tema de colores, logo y estado de publicacion.</p>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label>Slug</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}"></div>
                    <div class="field"><label>Nombre de la pagina</label><input type="text" name="name" value="{{ old('name', $page->name) }}"></div>
                    <div class="field"><label>Titulo SEO</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                    <div class="field"><label>Descripcion SEO</label><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"></div>
                    <div class="field"><label>Color principal</label><input type="text" name="theme[primary_color]" value="{{ old('theme.primary_color', $theme['primary_color'] ?? '#0d47b5') }}"></div>
                    <div class="field"><label>Color secundario</label><input type="text" name="theme[secondary_color]" value="{{ old('theme.secondary_color', $theme['secondary_color'] ?? '#2a4268') }}"></div>
                    <div class="field"><label>Color acento</label><input type="text" name="theme[accent_color]" value="{{ old('theme.accent_color', $theme['accent_color'] ?? '#ffc61a') }}"></div>
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
                <div class="section-header"><div><div class="section-eyebrow">Hero</div><h3 class="section-title">Portada azul principal</h3></div></div>
                <div class="grid grid-2">
                    <div class="field" style="grid-column:1/-1;"><label>Badge superior</label><input type="text" name="encomienda_hero[badge]" value="{{ old('encomienda_hero.badge', $hero['badge'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 blanco</label><input type="text" name="encomienda_hero[title_line_one_white]" value="{{ old('encomienda_hero.title_line_one_white', $hero['title_line_one_white'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 1 amarillo</label><input type="text" name="encomienda_hero[title_line_one_yellow]" value="{{ old('encomienda_hero.title_line_one_yellow', $hero['title_line_one_yellow'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 blanco</label><input type="text" name="encomienda_hero[title_line_two_white]" value="{{ old('encomienda_hero.title_line_two_white', $hero['title_line_two_white'] ?? '') }}"></div>
                    <div class="field"><label>Titulo linea 2 amarillo</label><input type="text" name="encomienda_hero[title_line_two_yellow]" value="{{ old('encomienda_hero.title_line_two_yellow', $hero['title_line_two_yellow'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Subtitulo</label><textarea class="field-small" name="encomienda_hero[subtitle]">{{ old('encomienda_hero.subtitle', $hero['subtitle'] ?? '') }}</textarea></div>
                    <div class="field"><label>Boton principal</label><input type="text" name="encomienda_hero[primary_button_label]" value="{{ old('encomienda_hero.primary_button_label', $hero['primary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton principal</label><input type="text" name="encomienda_hero[primary_button_url]" value="{{ old('encomienda_hero.primary_button_url', $hero['primary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Boton secundario</label><input type="text" name="encomienda_hero[secondary_button_label]" value="{{ old('encomienda_hero.secondary_button_label', $hero['secondary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton secundario</label><input type="text" name="encomienda_hero[secondary_button_url]" value="{{ old('encomienda_hero.secondary_button_url', $hero['secondary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Icono visual</label><input type="text" name="encomienda_hero[visual_icon]" value="{{ old('encomienda_hero.visual_icon', $hero['visual_icon'] ?? '') }}"></div>
                    <div class="field"><label>Imagen visual actual</label><input type="text" name="encomienda_hero[visual_image]" value="{{ old('encomienda_hero.visual_image', $hero['visual_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen visual</label><input type="file" name="encomienda_hero[visual_image_file]" accept="image/*"></div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Intro</div><h3 class="section-title">Bloque de relanzamiento</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="encomienda_intro[title]" value="{{ old('encomienda_intro.title', $intro['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Icono fallback</label><input type="text" name="encomienda_intro[visual_icon]" value="{{ old('encomienda_intro.visual_icon', $intro['settings']['visual_icon'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 1</label><textarea class="field-small" name="encomienda_intro[paragraph_one]">{{ old('encomienda_intro.paragraph_one', $intro['settings']['paragraph_one'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Parrafo 2</label><textarea class="field-small" name="encomienda_intro[paragraph_two]">{{ old('encomienda_intro.paragraph_two', $intro['settings']['paragraph_two'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Frase destacada</label><input type="text" name="encomienda_intro[quote]" value="{{ old('encomienda_intro.quote', $intro['settings']['quote'] ?? '') }}"></div>
                    <div class="field"><label>Imagen actual</label><input type="text" name="encomienda_intro[image]" value="{{ old('encomienda_intro.image', $intro['settings']['image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen</label><input type="file" name="encomienda_intro[image_file]" accept="image/*"></div>
                    <div class="field"><label>Badge label</label><input type="text" name="encomienda_intro[badge_label]" value="{{ old('encomienda_intro.badge_label', $intro['settings']['badge_label'] ?? '') }}"></div>
                    <div class="field"><label>Badge valor</label><input type="text" name="encomienda_intro[badge_value]" value="{{ old('encomienda_intro.badge_value', $intro['settings']['badge_value'] ?? '') }}"></div>
                    <div class="field"><label>Badge sufijo</label><input type="text" name="encomienda_intro[badge_suffix]" value="{{ old('encomienda_intro.badge_suffix', $intro['settings']['badge_suffix'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Estadisticas inferiores</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar estadistica</button></div>
                    <div class="stack" data-collection data-base="encomienda_intro[items]" data-template="encomienda-stat-template">
                        <div class="stack" data-rows>
                            @foreach (old('encomienda_intro.items', $intro['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['label'] ?? 'Estadistica' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
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
                <div class="section-header"><div><div class="section-eyebrow">Ventajas</div><h3 class="section-title">Tarjetas azules</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="encomienda_features[title]" value="{{ old('encomienda_features.title', $features['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="encomienda_features[subtitle]" value="{{ old('encomienda_features.subtitle', $features['settings']['subtitle'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Tarjetas de valor</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button></div>
                    <div class="stack" data-collection data-base="encomienda_features[items]" data-template="encomienda-card-template">
                        <div class="stack" data-rows>
                            @foreach (old('encomienda_features.items', $features['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
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

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">Guias</div><h3 class="section-title">Acordeon de requisitos</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="encomienda_faq[title]" value="{{ old('encomienda_faq.title', $faq['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="encomienda_faq[subtitle]" value="{{ old('encomienda_faq.subtitle', $faq['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Tip inferior</label><textarea class="field-small" name="encomienda_faq[tip_text]">{{ old('encomienda_faq.tip_text', $faq['settings']['tip_text'] ?? '') }}</textarea></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Preguntas</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar pregunta</button></div>
                    <div class="stack" data-collection data-base="encomienda_faq[items]" data-template="encomienda-card-template">
                        <div class="stack" data-rows>
                            @foreach (old('encomienda_faq.items', $faq['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Pregunta' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field"><label>Pregunta</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Respuesta</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header"><div><div class="section-eyebrow">CTA</div><h3 class="section-title">Bloque final de conversion</h3></div></div>
                <div class="grid grid-2">
                    <div class="field"><label>Titulo</label><input type="text" name="encomienda_cta[title]" value="{{ old('encomienda_cta.title', $cta['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtitulo</label><input type="text" name="encomienda_cta[subtitle]" value="{{ old('encomienda_cta.subtitle', $cta['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Boton 1</label><input type="text" name="encomienda_cta[button_one_label]" value="{{ old('encomienda_cta.button_one_label', $cta['settings']['button_one_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton 1</label><input type="text" name="encomienda_cta[button_one_url]" value="{{ old('encomienda_cta.button_one_url', $cta['settings']['button_one_url'] ?? '') }}"></div>
                    <div class="field"><label>Boton 2</label><input type="text" name="encomienda_cta[button_two_label]" value="{{ old('encomienda_cta.button_two_label', $cta['settings']['button_two_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton 2</label><input type="text" name="encomienda_cta[button_two_url]" value="{{ old('encomienda_cta.button_two_url', $cta['settings']['button_two_url'] ?? '') }}"></div>
                    <div class="field"><label>Boton 3</label><input type="text" name="encomienda_cta[button_three_label]" value="{{ old('encomienda_cta.button_three_label', $cta['settings']['button_three_label'] ?? '') }}"></div>
                    <div class="field"><label>URL boton 3</label><input type="text" name="encomienda_cta[button_three_url]" value="{{ old('encomienda_cta.button_three_url', $cta['settings']['button_three_url'] ?? '') }}"></div>
                    <div class="field"><label>Texto enorme de fondo</label><input type="text" name="encomienda_cta[watermark_text]" value="{{ old('encomienda_cta.watermark_text', $cta['settings']['watermark_text'] ?? '') }}"></div>
                    <div class="field"><label>Texto inferior</label><input type="text" name="encomienda_cta[footnote]" value="{{ old('encomienda_cta.footnote', $cta['settings']['footnote'] ?? '') }}"></div>
                </div>
                <div class="subpanel">
                    <div class="toolbar"><div><h4>Chips de contacto</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar chip</button></div>
                    <div class="stack" data-collection data-base="encomienda_cta[items]" data-template="encomienda-contact-template">
                        <div class="stack" data-rows>
                            @foreach (old('encomienda_cta.items', $cta['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['text'] ?? 'Chip' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Icono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field"><label>Texto</label><input type="text" data-field="text" value="{{ $item['text'] ?? '' }}"></div>
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
                    <strong style="display:block; margin-bottom:4px;">Guardar pagina Encomienda</strong>
                    <p>Header y footer se preservan; aqui administras el contenido completo de esta landing.</p>
                    <div class="field" style="margin-top:12px;"><label>Resumen del cambio</label><input type="text" name="change_summary" value="{{ old('change_summary') }}"></div>
                </div>
                <button type="submit" class="button button-primary">Guardar cambios del diseno</button>
            </div>
        </form>

        <template id="encomienda-stat-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Estadistica</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Valor</label><input type="text" data-field="value"></div>
                    <div class="field"><label>Label</label><input type="text" data-field="label"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="encomienda-card-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Tarjeta</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Icono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Titulo</label><input type="text" data-field="title"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto</label><textarea class="field-small" data-field="text"></textarea></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>

        <template id="encomienda-contact-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Chip</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div class="field"><label>Icono</label><input type="text" data-field="icon"></div>
                    <div class="field"><label>Texto</label><input type="text" data-field="text"></div>
                </div>
                <input type="hidden" data-field="id">
            </div>
        </template>
    </div>
@endsection
