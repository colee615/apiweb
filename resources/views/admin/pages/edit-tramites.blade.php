@extends('layouts.admin')

@php
    $theme = $editorData['theme'] ?? [];
    $headerSettings = $editorData['header']['settings'] ?? [];
    $tramites = $editorData['tramites'] ?? [];
    $tramitesSettings = $tramites['settings'] ?? [];
    $items = old('tramites.items', $tramites['items'] ?? []);
@endphp

@section('content')
    <div class="admin-shell stack">
        @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
        @if ($errors->any())
            <div class="notice notice-error">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <form id="page-edit-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Contenido administrable</div>
                        <h3 class="section-title">Información Postal</h3>
                        <p class="section-copy">Administra la información postal, sus descripciones, enlaces y archivos descargables. Las imágenes se muestran como vista previa en el sitio.</p>
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
                    <div class="field" style="display:flex;align-items:end;"><label style="display:flex;gap:10px;align-items:center;margin:0;text-transform:none;letter-spacing:0;font-size:14px;color:#123047;"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}> Publicar esta página</label></div>
                </div>
            </section>

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Página pública</div>
                        <h3 class="section-title">Presentación de Información Postal</h3>
                        <p class="section-copy">El header y el footer se mantienen compartidos con el resto del sitio.</p>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label>Etiqueta superior</label><input type="text" name="tramites[eyebrow]" value="{{ old('tramites.eyebrow', $tramitesSettings['eyebrow'] ?? '') }}"></div>
                    <div class="field"><label>Título principal</label><input type="text" name="tramites[title]" value="{{ old('tramites.title', $tramitesSettings['title'] ?? 'Información Postal') }}"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripción</label><textarea class="field-small" name="tramites[description]">{{ old('tramites.description', $tramitesSettings['description'] ?? '') }}</textarea></div>
                    <div class="field"><label>Placeholder de búsqueda</label><input type="text" name="tramites[search_placeholder]" value="{{ old('tramites.search_placeholder', $tramitesSettings['search_placeholder'] ?? 'Buscar información postal...') }}"></div>
                    <div class="field"><label>Mensaje sin resultados</label><input type="text" name="tramites[empty_text]" value="{{ old('tramites.empty_text', $tramitesSettings['empty_text'] ?? '') }}"></div>
                </div>

                <div class="subpanel">
                    <div class="toolbar">
                        <div><h4>Información Postal publicada</h4><p>Cada registro puede tener una URL externa o un archivo propio. PDF, Word, Excel e imágenes son descargables.</p></div>
                        <button type="button" class="button button-secondary" data-add-row>Agregar información</button>
                    </div>
                    <div class="stack" data-collection data-base="tramites[items]" data-template="tramite-template">
                        <div data-rows>
                            @foreach ($items as $item)
                                @php
                                    $src = $item['src'] ?? '';
                                    $mime = $item['file_mime'] ?? '';
                                    $isImage = str_starts_with((string) $mime, 'image/');
                                    $attachments = is_array($item['attachments'] ?? null) ? $item['attachments'] : [];
                                    if (empty($attachments) && $src) {
                                        $attachments = [[
                                            'src' => $src,
                                            'file_name' => $item['file_name'] ?? '',
                                            'file_mime' => $mime,
                                            'file_extension' => $item['file_extension'] ?? '',
                                        ]];
                                    }
                                @endphp
                                <div class="repeater-card" data-row>
                                    <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Trámite' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                    <div class="grid grid-3" style="margin-top:12px;">
                                        <div class="field"><label>Nombre del trámite</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        <div class="field"><label>Categoría</label><input type="text" data-field="category" value="{{ $item['category'] ?? '' }}" placeholder="Postal, institucional..."></div>
                                        <div class="field"><label>Enlace externo o interno</label><input type="text" data-field="url" value="{{ $item['url'] ?? '' }}" placeholder="/contacto o https://..."></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Descripción breve</label><textarea class="field-small" data-field="description">{{ $item['description'] ?? '' }}</textarea></div>
                                        <div class="field" style="grid-column:1/-1;"><label>Archivos actuales</label>
                                            <input type="hidden" data-field="src" value="{{ $src }}">
                                            <input type="hidden" data-field="file_name" value="{{ $item['file_name'] ?? '' }}">
                                            <input type="hidden" data-field="file_mime" value="{{ $item['file_mime'] ?? '' }}">
                                            <input type="hidden" data-field="file_extension" value="{{ $item['file_extension'] ?? '' }}">
                                            <input type="hidden" data-field="attachments_json" value="{{ json_encode($attachments, JSON_UNESCAPED_UNICODE) }}">
                                            @if ($src)
                                                <div style="display:flex;align-items:center;gap:12px;margin-top:8px;padding:10px;border:1px solid #d8e3f0;border-radius:12px;background:#f8fbff;">
                                                    @if ($isImage)<img src="{{ $src }}" alt="" style="width:76px;height:54px;object-fit:cover;border-radius:8px;">@else<span style="font-size:30px;">📄</span>@endif
                                                    <a href="{{ $src }}" target="_blank" rel="noopener" style="word-break:break-all;">{{ $item['file_name'] ?? 'Abrir archivo' }}</a>
                                                </div>
                                            @else
                                                <span style="display:block;margin-top:8px;color:#6a7f99;">Todavía no hay archivo cargado.</span>
                                            @endif
                                        </div>
                                        @if (count($attachments) > 1)
                                            <div class="field" style="grid-column:1/-1;"><label>Adjuntos del registro</label><small style="display:block;color:#6a7f99;">{{ count($attachments) }} archivos asociados. Puedes agregar más o quitarlos todos.</small></div>
                                        @endif
                                        <div class="field" style="grid-column:1/-1;"><label>Agregar varios archivos (PDF, Word, Excel, imágenes u otros)</label><input type="file" data-field="files" accept="*/*" multiple></div>
                                        <div class="field" style="grid-column:1/-1;"><label style="display:flex;align-items:center;gap:8px;text-transform:none;letter-spacing:0;"><input type="checkbox" data-field="clear_attachments" value="1"> Quitar los archivos actuales</label></div>
                                    </div>
                                    <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <div class="save-dock">
                <div style="flex:1;"><strong style="display:block;margin-bottom:4px;">Guardar Información Postal</strong><p>Los cambios se versionan y se publican en /informacion-postal.</p><div class="field" style="margin-top:12px;"><label>Resumen del cambio</label><input type="text" name="change_summary" value="{{ old('change_summary') }}"></div></div>
                <button type="submit" class="button button-primary">Guardar cambios</button>
            </div>
        </form>

        <template id="tramite-template">
            <div class="repeater-card" data-row>
                <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Información Postal</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                <div class="grid grid-3" style="margin-top:12px;">
                    <div class="field"><label>Nombre del trámite</label><input type="text" data-field="title"></div>
                    <div class="field"><label>Categoría</label><input type="text" data-field="category"></div>
                    <div class="field"><label>Enlace externo o interno</label><input type="text" data-field="url"></div>
                    <div class="field" style="grid-column:1/-1;"><label>Descripción breve</label><textarea class="field-small" data-field="description"></textarea></div>
                    <div class="field" style="grid-column:1/-1;"><label>Agregar varios archivos</label><input type="file" data-field="files" accept="*/*" multiple></div>
                </div>
                <input type="hidden" data-field="src"><input type="hidden" data-field="file_name"><input type="hidden" data-field="file_mime"><input type="hidden" data-field="file_extension"><input type="hidden" data-field="attachments_json"><input type="hidden" data-field="clear_attachments" value="0"><input type="hidden" data-field="id">
            </div>
        </template>
    </div>
@endsection
