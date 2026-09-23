@php
    $emsIntro = $editorData['ems_intro'];
    $emsBenefits = $editorData['ems_benefits'];
    $emsNational = $editorData['ems_national'];
    $emsInternational = $editorData['ems_international'];
@endphp
                <section class="section-card">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Servicio EMS</div>
                            <h3 class="section-title">Contenido de EMS</h3>
                            <p class="section-copy">Este bloque concentra la narrativa visual del servicio EMS: cabecera, beneficios, cobertura nacional y alcance internacional.</p>
                        </div>
                        <div class="section-metrics">
                            <span class="pill pill-off">{{ count($emsBenefits['items'] ?? []) }} beneficios</span>
                            <span class="pill pill-off">{{ count($emsNational['items'] ?? []) }} tarjetas nacionales</span>
                            <span class="pill pill-off">{{ count($emsInternational['items'] ?? []) }} tarjetas globales</span>
                        </div>
                    </div>

                    <div class="subpanel">
                        <h4>Cabecera e introducción</h4>
                        <div class="grid grid-3">
                            <div class="field"><label>Etiqueta superior</label><input type="text" name="ems_intro[eyebrow]" value="{{ old('ems_intro.eyebrow', $emsIntro['settings']['eyebrow'] ?? '') }}"></div>
                            <div class="field"><label>Título protagonista</label><input type="text" name="ems_intro[hero_title]" value="{{ old('ems_intro.hero_title', $emsIntro['settings']['hero_title'] ?? '') }}"></div>
                            <div class="field"><label>Marca de agua</label><input type="text" name="ems_intro[watermark_text]" value="{{ old('ems_intro.watermark_text', $emsIntro['settings']['watermark_text'] ?? '') }}"></div>
                            <div class="field" style="grid-column:1/-1;"><label>Título principal</label><input type="text" name="ems_intro[title]" value="{{ old('ems_intro.title', $emsIntro['settings']['title'] ?? '') }}"></div>
                            <div class="field" style="grid-column:1/-1;"><label>Texto destacado</label><input type="text" name="ems_intro[highlight_text]" value="{{ old('ems_intro.highlight_text', $emsIntro['settings']['highlight_text'] ?? '') }}"></div>
                            <div class="field" style="grid-column:1/-1;"><label>Párrafo 1</label><textarea class="field-small" name="ems_intro[paragraph_one]">{{ old('ems_intro.paragraph_one', $emsIntro['settings']['paragraph_one'] ?? '') }}</textarea></div>
                            <div class="field" style="grid-column:1/-1;"><label>Párrafo 2</label><textarea class="field-small" name="ems_intro[paragraph_two]">{{ old('ems_intro.paragraph_two', $emsIntro['settings']['paragraph_two'] ?? '') }}</textarea></div>
                            <div class="field" style="grid-column:1/-1;"><label>Párrafo 3</label><textarea class="field-small" name="ems_intro[paragraph_three]">{{ old('ems_intro.paragraph_three', $emsIntro['settings']['paragraph_three'] ?? '') }}</textarea></div>
                            <div class="field"><label>Texto del botón</label><input type="text" name="ems_intro[primary_button_label]" value="{{ old('ems_intro.primary_button_label', $emsIntro['settings']['primary_button_label'] ?? '') }}"></div>
                            <div class="field"><label>Enlace del botón</label><input type="text" name="ems_intro[primary_button_url]" value="{{ old('ems_intro.primary_button_url', $emsIntro['settings']['primary_button_url'] ?? '') }}"></div>
                            <div class="field"><label>Ícono del bloque</label><input type="text" name="ems_intro[visual_icon]" value="{{ old('ems_intro.visual_icon', $emsIntro['settings']['visual_icon'] ?? '') }}" placeholder="plane, globe, box..."></div>
                            <div class="field"><label>Imagen actual</label><input type="text" name="ems_intro[image]" value="{{ old('ems_intro.image', $emsIntro['settings']['image'] ?? '') }}"></div>
                            <div class="field"><label>Subir imagen</label><input type="file" name="ems_intro[image_file]" accept="image/*"></div>
                        </div>
                        @if (!empty($emsIntro['settings']['image']))
                            <div style="margin-top:14px;">
                                <img src="{{ $emsIntro['settings']['image'] }}" alt="Visual EMS" class="thumb" style="display:block; max-width:260px;">
                            </div>
                        @endif
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Beneficios principales</h4>
                                <p>Tarjetas sobre fondo azul con icono, título y descripción.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar beneficio</button>
                        </div>
                        <div class="grid grid-1" style="margin-bottom:14px;">
                            <div class="field"><label>Título de la sección</label><input type="text" name="ems_benefits[title]" value="{{ old('ems_benefits.title', $emsBenefits['settings']['title'] ?? '') }}"></div>
                        </div>
                        <div class="stack" data-collection data-base="ems_benefits[items]" data-template="ems-card-template">
                            <div data-rows>
                                @forelse (($emsBenefits['items'] ?? []) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Beneficio EMS' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                            <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                            <div class="field"><label>Etiqueta opcional</label><input type="text" data-field="badge" value="{{ $item['badge'] ?? '' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay beneficios configurados todavía.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Cobertura nacional</h4>
                                <p>Tarjetas blancas con el bloque de métrica central.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button>
                        </div>
                        <div class="grid grid-3" style="margin-bottom:14px;">
                            <div class="field"><label>Título</label><input type="text" name="ems_national[title]" value="{{ old('ems_national.title', $emsNational['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Subtítulo</label><input type="text" name="ems_national[subtitle]" value="{{ old('ems_national.subtitle', $emsNational['settings']['subtitle'] ?? '') }}"></div>
                            <div class="field"><label>Etiqueta de métrica</label><input type="text" name="ems_national[stat_label]" value="{{ old('ems_national.stat_label', $emsNational['settings']['stat_label'] ?? '') }}"></div>
                            <div class="field"><label>Valor de métrica</label><input type="text" name="ems_national[stat_value]" value="{{ old('ems_national.stat_value', $emsNational['settings']['stat_value'] ?? '') }}"></div>
                            <div class="field"><label>Leyenda de métrica</label><input type="text" name="ems_national[stat_caption]" value="{{ old('ems_national.stat_caption', $emsNational['settings']['stat_caption'] ?? '') }}"></div>
                        </div>
                        <div class="stack" data-collection data-base="ems_national[items]" data-template="ems-card-template">
                            <div data-rows>
                                @forelse (($emsNational['items'] ?? []) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Cobertura EMS' }}</strong>
                                            </div>
                                            <button type="button" class="button button-danger" data-remove-row>Eliminar</button>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                            <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                            <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                            <div class="field"><label>Etiqueta opcional</label><input type="text" data-field="badge" value="{{ $item['badge'] ?? '' }}"></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @empty
                                    <div class="empty-note">No hay tarjetas nacionales todavía.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="subpanel">
                        <div class="toolbar">
                            <div>
                                <h4>Alcance internacional</h4>
                                <p>Tarjetas sobre fondo azul con badges y botón de acción final.</p>
                            </div>
                            <button type="button" class="button button-secondary" data-add-row>Agregar tarjeta</button>
                        </div>
                        <div class="grid grid-3" style="margin-bottom:14px;">
                            <div class="field"><label>Título</label><input type="text" name="ems_international[title]" value="{{ old('ems_international.title', $emsInternational['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Subtítulo</label><input type="text" name="ems_international[subtitle]" value="{{ old('ems_international.subtitle', $emsInternational['settings']['subtitle'] ?? '') }}"></div>
                            <div class="field"><label>Texto resaltado</label><input type="text" name="ems_international[highlight_text]" value="{{ old('ems_international.highlight_text', $emsInternational['settings']['highlight_text'] ?? '') }}"></div>
                            <div class="field" style="grid-column:1/-1;"><label>Texto botón</label><textarea class="field-small" name="ems_international[cta_text]">{{ old('ems_international.cta_text', $emsInternational['settings']['cta_text'] ?? '') }}</textarea></div>
                            <div class="field"><label>Texto del botón final</label><input type="text" name="ems_international[secondary_button_label]" value="{{ old('ems_international.secondary_button_label', $emsInternational['settings']['secondary_button_label'] ?? '') }}"></div>
                            <div class="field"><label>Enlace del botón final</label><input type="text" name="ems_international[secondary_button_url]" value="{{ old('ems_international.secondary_button_url', $emsInternational['settings']['secondary_button_url'] ?? '') }}"></div>
                        </div>
                        <div class="stack" data-collection data-base="ems_international[items]" data-template="ems-card-template">
                            <div data-rows>
                                @forelse (($emsInternational['items'] ?? []) as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar">
                                            <div class="actions">
                                                <span class="drag-handle" data-drag>::</span>
                                                <strong>{{ $item['title'] ?? 'Tarjeta global EMS' }}</strong>
                                            </div>
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
                                @empty
                                    <div class="empty-note">No hay tarjetas internacionales todavía.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
