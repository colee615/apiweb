@extends('layouts.admin')

@php
    $hero = $editorData['delivery_hero']['settings'] ?? [];
    $intro = $editorData['delivery_intro']['settings'] ?? [];
    $advantages = $editorData['delivery_advantages'];
    $process = $editorData['delivery_process'];
    $info = $editorData['delivery_info'];
    $cta = $editorData['delivery_cta']['settings'] ?? [];
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
                        <p class="section-copy">Edita el enlace, la información para buscadores y el estado de esta página.</p><div class="field-help">El logo, los colores, el encabezado y el pie se administran desde Configuración global.</div>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="field"><label>Dirección de la página</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}"></div>
                    <div class="field"><label>Nombre de la página</label><input type="text" name="name" value="{{ old('name', $page->name) }}"></div>
                    <div class="field"><label>Título en buscadores</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                    <div class="field"><label>Descripción en buscadores</label><input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"></div>
                    <div class="field" style="display:flex; align-items:end;">
                        <label style="display:flex; gap:10px; align-items:center; margin:0; text-transform:none; letter-spacing:0; font-size:14px; color:#123047;">
                            <input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                            Publicar esta página
                        </label>
                    </div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Portada</div>
                        <h3 class="section-title">Bloque principal</h3>
                        <p class="section-copy">Controla el gran titular, botón de acción y el visual superior.</p>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="field"><label>Título línea 1</label><input type="text" name="delivery_hero[title_primary]" value="{{ old('delivery_hero.title_primary', $hero['title_primary'] ?? '') }}"></div>
                    <div class="field"><label>Título línea 2</label><input type="text" name="delivery_hero[title_secondary]" value="{{ old('delivery_hero.title_secondary', $hero['title_secondary'] ?? '') }}"></div>
                    <div class="field"><label>Texto amarillo</label><input type="text" name="delivery_hero[eyebrow]" value="{{ old('delivery_hero.eyebrow', $hero['eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Subtítulo</label><input type="text" name="delivery_hero[subtitle]" value="{{ old('delivery_hero.subtitle', $hero['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Botón principal</label><input type="text" name="delivery_hero[primary_button_label]" value="{{ old('delivery_hero.primary_button_label', $hero['primary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace botón principal</label><input type="text" name="delivery_hero[primary_button_url]" value="{{ old('delivery_hero.primary_button_url', $hero['primary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Botón secundario</label><input type="text" name="delivery_hero[secondary_button_label]" value="{{ old('delivery_hero.secondary_button_label', $hero['secondary_button_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace botón secundario</label><input type="text" name="delivery_hero[secondary_button_url]" value="{{ old('delivery_hero.secondary_button_url', $hero['secondary_button_url'] ?? '') }}"></div>
                    <div class="field"><label>Ícono principal</label><input type="text" name="delivery_hero[visual_icon]" value="{{ old('delivery_hero.visual_icon', $hero['visual_icon'] ?? '') }}" placeholder="smartphone, package, truck"></div>
                    <div class="field"><label>Icono flotante</label><input type="text" name="delivery_hero[floating_icon]" value="{{ old('delivery_hero.floating_icon', $hero['floating_icon'] ?? '') }}" placeholder="smartphone, mail"></div>
                    <div class="field"><label>Imagen visual actual</label><input type="text" name="delivery_hero[visual_image]" value="{{ old('delivery_hero.visual_image', $hero['visual_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen visual</label><input type="file" name="delivery_hero[visual_image_file]" accept="image/*"></div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Intro</div>
                        <h3 class="section-title">Presentación del servicio</h3>
                        <p class="section-copy">Texto principal, párrafos, etiquetas cortas y visual del segundo bloque.</p>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="field" style="grid-column:1/-1;"><label>Título</label><input type="text" name="delivery_intro[title]" value="{{ old('delivery_intro.title', $intro['title'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto destacado</label><input type="text" name="delivery_intro[highlight_text]" value="{{ old('delivery_intro.highlight_text', $intro['highlight_text'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Párrafo 1</label><textarea class="field-small" name="delivery_intro[paragraph_one]">{{ old('delivery_intro.paragraph_one', $intro['paragraph_one'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Párrafo 2</label><textarea class="field-small" name="delivery_intro[paragraph_two]">{{ old('delivery_intro.paragraph_two', $intro['paragraph_two'] ?? '') }}</textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Frase de cierre</label><input type="text" name="delivery_intro[closing_text]" value="{{ old('delivery_intro.closing_text', $intro['closing_text'] ?? '') }}"></div>
                    <div class="field"><label>Chip 1</label><input type="text" name="delivery_intro[chip_one]" value="{{ old('delivery_intro.chip_one', $intro['chip_one'] ?? '') }}"></div>
                    <div class="field"><label>Chip 2</label><input type="text" name="delivery_intro[chip_two]" value="{{ old('delivery_intro.chip_two', $intro['chip_two'] ?? '') }}"></div>
                    <div class="field"><label>Chip 3</label><input type="text" name="delivery_intro[chip_three]" value="{{ old('delivery_intro.chip_three', $intro['chip_three'] ?? '') }}"></div>
                    <div class="field"><label>Ícono del bloque</label><input type="text" name="delivery_intro[visual_icon]" value="{{ old('delivery_intro.visual_icon', $intro['visual_icon'] ?? '') }}"></div>
                    <div class="field"><label>Texto de la etiqueta</label><input type="text" name="delivery_intro[visual_badge]" value="{{ old('delivery_intro.visual_badge', $intro['visual_badge'] ?? '') }}"></div>
                    <div class="field"><label>Imagen visual actual</label><input type="text" name="delivery_intro[visual_image]" value="{{ old('delivery_intro.visual_image', $intro['visual_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen visual</label><input type="file" name="delivery_intro[visual_image_file]" accept="image/*"></div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Ventajas</div>
                        <h3 class="section-title">Sección azul de beneficios</h3>
                        <p class="section-copy">Tarjetas blancas sobre fondo azul.</p>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="field"><label>Título</label><input type="text" name="delivery_advantages[title]" value="{{ old('delivery_advantages.title', $advantages['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtítulo</label><input type="text" name="delivery_advantages[subtitle]" value="{{ old('delivery_advantages.subtitle', $advantages['settings']['subtitle'] ?? '') }}"></div>
                </div>

                <div class="subpanel">
                    <div class="toolbar">
                        <div>
                            <h4>Tarjetas de ventajas</h4>
                            <p>Edita icono, título, texto y etiqueta opcional.</p>
                        </div>
                        <button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button>
                    </div>
                    <div class="stack" data-collection data-base="delivery_advantages[items]" data-template="ems-card-template">
                        <div class="stack" data-rows>
                            @foreach (old('delivery_advantages.items', $advantages['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar">
                                        <div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div>
                                        <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                    </div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
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
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Proceso</div>
                        <h3 class="section-title">Pasos y trazabilidad</h3>
                        <p class="section-copy">Controla los 3 pasos, puntos de la lista y vista de ejemplo del seguimiento.</p>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="field"><label>Título</label><input type="text" name="delivery_process[title]" value="{{ old('delivery_process.title', $process['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtítulo</label><input type="text" name="delivery_process[subtitle]" value="{{ old('delivery_process.subtitle', $process['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field"><label>Título bloque trazabilidad</label><input type="text" name="delivery_process[feature_title]" value="{{ old('delivery_process.feature_title', $process['settings']['feature_title'] ?? '') }}"></div>
                    <div class="field"><label>Título tarjeta seguimiento</label><input type="text" name="delivery_process[tracker_title]" value="{{ old('delivery_process.tracker_title', $process['settings']['tracker_title'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto bloque trazabilidad</label><textarea class="field-small" name="delivery_process[feature_text]">{{ old('delivery_process.feature_text', $process['settings']['feature_text'] ?? '') }}</textarea></div>
                    <div class="field"><label>Bullet 1</label><input type="text" name="delivery_process[bullet_one]" value="{{ old('delivery_process.bullet_one', $process['settings']['bullet_one'] ?? '') }}"></div>
                    <div class="field"><label>Bullet 2</label><input type="text" name="delivery_process[bullet_two]" value="{{ old('delivery_process.bullet_two', $process['settings']['bullet_two'] ?? '') }}"></div>
                    <div class="field"><label>Bullet 3</label><input type="text" name="delivery_process[bullet_three]" value="{{ old('delivery_process.bullet_three', $process['settings']['bullet_three'] ?? '') }}"></div>
                    <div class="field"><label>Estado central</label><input type="text" name="delivery_process[tracker_status]" value="{{ old('delivery_process.tracker_status', $process['settings']['tracker_status'] ?? '') }}"></div>
                    <div class="field"><label>Texto del estado</label><input type="text" name="delivery_process[tracker_stage]" value="{{ old('delivery_process.tracker_stage', $process['settings']['tracker_stage'] ?? '') }}"></div>
                    <div class="field"><label>Evento 1</label><input type="text" name="delivery_process[timeline_one_title]" value="{{ old('delivery_process.timeline_one_title', $process['settings']['timeline_one_title'] ?? '') }}"></div>
                    <div class="field"><label>Hora 1</label><input type="text" name="delivery_process[timeline_one_time]" value="{{ old('delivery_process.timeline_one_time', $process['settings']['timeline_one_time'] ?? '') }}"></div>
                    <div class="field"><label>Evento 2</label><input type="text" name="delivery_process[timeline_two_title]" value="{{ old('delivery_process.timeline_two_title', $process['settings']['timeline_two_title'] ?? '') }}"></div>
                    <div class="field"><label>Hora 2</label><input type="text" name="delivery_process[timeline_two_time]" value="{{ old('delivery_process.timeline_two_time', $process['settings']['timeline_two_time'] ?? '') }}"></div>
                    <div class="field"><label>Evento 3</label><input type="text" name="delivery_process[timeline_three_title]" value="{{ old('delivery_process.timeline_three_title', $process['settings']['timeline_three_title'] ?? '') }}"></div>
                    <div class="field"><label>Hora 3</label><input type="text" name="delivery_process[timeline_three_time]" value="{{ old('delivery_process.timeline_three_time', $process['settings']['timeline_three_time'] ?? '') }}"></div>
                </div>

                <div class="subpanel">
                    <div class="toolbar">
                        <div><h4>Pasos del proceso</h4><p>Usa el etiqueta para 01, 02, 03.</p></div>
                        <button type="button" class="button button-secondary" data-add-row>Agregar paso</button>
                    </div>
                    <div class="stack" data-collection data-base="delivery_process[items]" data-template="ems-card-template">
                        <div class="stack" data-rows>
                            @foreach (old('delivery_process.items', $process['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar">
                                        <div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Paso' }}</strong></div>
                                        <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                    </div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                        <div class="field"><label>Número / etiqueta</label><input type="text" data-field="badge" value="{{ $item['badge'] ?? '' }}"></div>
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
                        <div class="section-eyebrow">Información</div>
                        <h3 class="section-title">Panel informativo</h3>
                        <p class="section-copy">Bloque crema con tarjetas de dimensiones, cobertura y seguridad.</p>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="field"><label>Título</label><input type="text" name="delivery_info[title]" value="{{ old('delivery_info.title', $info['settings']['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtítulo</label><input type="text" name="delivery_info[subtitle]" value="{{ old('delivery_info.subtitle', $info['settings']['subtitle'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto final</label><input type="text" name="delivery_info[footnote]" value="{{ old('delivery_info.footnote', $info['settings']['footnote'] ?? '') }}"></div>
                </div>

                <div class="subpanel">
                    <div class="toolbar">
                        <div><h4>Tarjetas informativas</h4><p>Hasta tres o más si las necesitas.</p></div>
                        <button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button>
                    </div>
                    <div class="stack" data-collection data-base="delivery_info[items]" data-template="ems-card-template">
                        <div class="stack" data-rows>
                            @foreach (old('delivery_info.items', $info['items'] ?? []) as $item)
                                <div class="repeater-card" data-row>
                                    <div class="toolbar">
                                        <div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Tarjeta' }}</strong></div>
                                        <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                    </div>
                                    <div class="grid grid-2" style="margin-top:12px;">
                                        <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
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
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Contacto y botones</div>
                        <h3 class="section-title">Bloque final amarillo</h3>
                        <p class="section-copy">Descarga, registro y contacto.</p>
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="field"><label>Título principal</label><input type="text" name="delivery_cta[title]" value="{{ old('delivery_cta.title', $cta['title'] ?? '') }}"></div>
                    <div class="field"><label>Subtítulo</label><input type="text" name="delivery_cta[subtitle]" value="{{ old('delivery_cta.subtitle', $cta['subtitle'] ?? '') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Texto de confianza</label><input type="text" name="delivery_cta[trust_text]" value="{{ old('delivery_cta.trust_text', $cta['trust_text'] ?? '') }}"></div>
                    <div class="field"><label>Título app</label><input type="text" name="delivery_cta[app_title]" value="{{ old('delivery_cta.app_title', $cta['app_title'] ?? '') }}"></div>
                    <div class="field"><label>Nota app</label><input type="text" name="delivery_cta[app_note]" value="{{ old('delivery_cta.app_note', $cta['app_note'] ?? '') }}"></div>
                    <div class="field"><label>Texto App Store</label><input type="text" name="delivery_cta[app_store_label]" value="{{ old('delivery_cta.app_store_label', $cta['app_store_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace App Store</label><input type="text" name="delivery_cta[app_store_url]" value="{{ old('delivery_cta.app_store_url', $cta['app_store_url'] ?? '') }}"></div>
                    <div class="field"><label>Imagen actual App Store</label><input type="text" name="delivery_cta[app_store_badge]" value="{{ old('delivery_cta.app_store_badge', $cta['app_store_badge'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen App Store</label><input type="file" name="delivery_cta[app_store_badge_file]" accept="image/*"></div>
                    <div class="field"><label>Texto Google Play</label><input type="text" name="delivery_cta[play_store_label]" value="{{ old('delivery_cta.play_store_label', $cta['play_store_label'] ?? '') }}"></div>
                    <div class="field"><label>Enlace Google Play</label><input type="text" name="delivery_cta[play_store_url]" value="{{ old('delivery_cta.play_store_url', $cta['play_store_url'] ?? '') }}"></div>
                    <div class="field"><label>Imagen actual Google Play</label><input type="text" name="delivery_cta[play_store_badge]" value="{{ old('delivery_cta.play_store_badge', $cta['play_store_badge'] ?? '') }}"></div>
                    <div class="field"><label>Subir imagen Google Play</label><input type="file" name="delivery_cta[play_store_badge_file]" accept="image/*"></div>
                    <div class="field"><label>Título registro</label><input type="text" name="delivery_cta[register_title]" value="{{ old('delivery_cta.register_title', $cta['register_title'] ?? '') }}"></div>
                    <div class="field"><label>Texto registro</label><input type="text" name="delivery_cta[register_text]" value="{{ old('delivery_cta.register_text', $cta['register_text'] ?? '') }}"></div>
                    <div class="field"><label>QR actual</label><input type="text" name="delivery_cta[register_qr_image]" value="{{ old('delivery_cta.register_qr_image', $cta['register_qr_image'] ?? '') }}"></div>
                    <div class="field"><label>Subir QR</label><input type="file" name="delivery_cta[register_qr_file]" accept="image/*"></div>
                    <div class="field"><label>Título contacto</label><input type="text" name="delivery_cta[contact_title]" value="{{ old('delivery_cta.contact_title', $cta['contact_title'] ?? '') }}"></div>
                    <div class="field"><label>WhatsApp texto</label><input type="text" name="delivery_cta[contact_whatsapp_label]" value="{{ old('delivery_cta.contact_whatsapp_label', $cta['contact_whatsapp_label'] ?? '') }}"></div>
                    <div class="field"><label>WhatsApp URL</label><input type="text" name="delivery_cta[contact_whatsapp_url]" value="{{ old('delivery_cta.contact_whatsapp_url', $cta['contact_whatsapp_url'] ?? '') }}"></div>
                    <div class="field"><label>Web texto</label><input type="text" name="delivery_cta[contact_web_label]" value="{{ old('delivery_cta.contact_web_label', $cta['contact_web_label'] ?? '') }}"></div>
                    <div class="field"><label>Web URL</label><input type="text" name="delivery_cta[contact_web_url]" value="{{ old('delivery_cta.contact_web_url', $cta['contact_web_url'] ?? '') }}"></div>
                    <div class="field"><label>Teléfono texto</label><input type="text" name="delivery_cta[contact_phone_label]" value="{{ old('delivery_cta.contact_phone_label', $cta['contact_phone_label'] ?? '') }}"></div>
                    <div class="field"><label>Teléfono URL</label><input type="text" name="delivery_cta[contact_phone_url]" value="{{ old('delivery_cta.contact_phone_url', $cta['contact_phone_url'] ?? '') }}"></div>
                </div>
            </section>

            @include('admin.pages.partials.save')
        </form>
    </div>
@endsection
