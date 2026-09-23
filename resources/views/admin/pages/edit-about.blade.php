@extends('layouts.admin')

@php
    $heroGallery = $editorData['hero_gallery'];
    $missionVision = $editorData['mission_vision'];
    $history = $editorData['history'];
    $principles = $editorData['principles'];
    $organigram = $editorData['organigram'];
    $objectives = $editorData['objectives'];
@endphp

@section('content')
<div class="admin-shell stack" x-data="{ tab: @js(request('tab', 'design_text')), go(section) { this.tab = section; } }">
    @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="notice notice-error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    @include('admin.pages.partials.header')

    <form id="page-edit-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" data-editor-form enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="editor-layout">
            <aside class="editor-sidebar">
                <div class="editor-nav">
                    <h3>Secciones</h3>
                    <p>Selecciona el bloque que quieres editar.</p>
                    <div class="editor-nav-list">
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'design_text' }" @click="go('design_text')"><strong>Página</strong><span>Nombre, enlace, buscadores y estado</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'backgrounds' }" @click="go('backgrounds')"><strong>Fondos</strong><span>Carrusel superior</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'story' }" @click="go('story')"><strong>Historia</strong><span>Misión, visión e historia</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'principles' }" @click="go('principles')"><strong>Principios</strong><span>Valores y tarjetas</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'organigram' }" @click="go('organigram')"><strong>Organigrama</strong><span>Bloque institucional</span></button>
                        <button type="button" class="editor-nav-button" :class="{ 'active': tab === 'objectives' }" @click="go('objectives')"><strong>Objetivos</strong><span>Lista estrategica</span></button>
                    </div>
                </div>
            </aside>

            <div class="editor-main">
                <section class="section-card" x-show="tab === 'design_text'">
                    <div class="section-header">
                        <div>
                            <div class="section-eyebrow">Esta página</div>
                            <h3 class="section-title">Datos de esta página</h3>
                            <p class="section-copy">Edita el nombre, el enlace, la información para buscadores y el estado de esta página.</p><div class="field-help">El logo, los colores, el encabezado y el pie se administran desde Configuración global.</div>
                        </div>
                        <div class="section-metrics"><span class="pill pill-off">Buscadores</span><span class="pill pill-off">Estado</span></div>
                    </div>
                    <div class="design-grid">
                        <div class="subpanel span-8">
                            <h4>Informacion principal</h4>
                            <div class="grid grid-2">
                                <div class="field"><label>Nombre interno</label><input type="text" name="name" value="{{ old('name', $page->name) }}" required></div>
                                <div class="field"><label>Dirección de la página</label><input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required></div>
                                <div class="field"><label>Título en buscadores</label><input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"></div>
                                <div class="field"><label>Descripción en buscadores</label><textarea name="meta_description" class="field-small">{{ old('meta_description', $page->meta_description) }}</textarea></div>
                            </div>
                        </div>
                        <div class="subpanel span-4">
                            <h4>Estado del sitio</h4>
                            <label style="display:flex; gap:10px; align-items:center; font-weight:800; margin-top:16px;"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>Pagina activa</label>
                            <div class="image-frame" style="margin-top:16px;">
                                <strong>Layout compartido</strong>
                                <p style="margin-top:10px;">El encabezado y el pie visibles en el sitio se leen desde Configuración global.</p>
                            </div>
                        </div>
                        
                        
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'backgrounds'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Fondos</div><h3 class="section-title">Carrusel superior</h3><p class="section-copy">Carrusel visual puro para imagenes o videos, sin textos superpuestos en sitio web.</p></div>
                        <span class="pill pill-off">{{ count($heroGallery['items']) }} slide(s)</span>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>imágenes o videos del carrusel</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar slide</button></div>
                        <div class="stack" data-collection data-base="hero_gallery[items]" data-template="hero-template">
                            <div data-rows>
                                @foreach ($heroGallery['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ ($item['media_type'] ?? 'image') === 'video' ? 'Video' : 'Imagen' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field">
                                                <label>Tipo de medio</label>
                                                <select data-field="media_type">
                                                    <option value="image" {{ ($item['media_type'] ?? 'image') === 'image' ? 'selected' : '' }}>Imagen</option>
                                                    <option value="video" {{ ($item['media_type'] ?? '') === 'video' ? 'selected' : '' }}>Video</option>
                                                </select>
                                            </div>
                                            <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" data-field="duration_seconds" value="{{ $item['duration_seconds'] ?? 5 }}"></div>
                                            <div class="field"><label>Enlace del medio</label><input type="text" data-field="media_url" value="{{ $item['media_url'] ?? ($item['image'] ?? '') }}"></div>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Subir imagen o video</label><input type="file" data-field="media_file" accept="image/*,video/mp4,video/webm"></div>
                                            <div class="field"><label>Portada del video</label><input type="file" data-field="poster_file" accept="image/*"></div>
                                        </div>
                                        <div class="field" style="margin-top:12px;"><label>Enlace portada (opcional para video)</label><input type="text" data-field="poster_image" value="{{ $item['poster_image'] ?? '' }}"></div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'story'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Historia institucional</div><h3 class="section-title">Misión, visión e historia</h3><p class="section-copy">Edita el bloque conceptual y el carrusel visual de historia.</p></div>
                    </div>
                    <div class="design-grid">
                        <div class="subpanel span-6">
                            <h4>Misión y visión</h4>
                            <div class="grid grid-2">
                                <div class="field"><label>Título mision</label><input type="text" name="mission_vision[mission_title]" value="{{ old('mission_vision.mission_title', $missionVision['settings']['mission_title'] ?? 'Misión') }}"></div>
                                <div class="field"><label>Título visión</label><input type="text" name="mission_vision[vision_title]" value="{{ old('mission_vision.vision_title', $missionVision['settings']['vision_title'] ?? 'Vision') }}"></div>
                                <div class="field"><label>Texto mision</label><textarea name="mission_vision[mission_text]" class="field-small">{{ old('mission_vision.mission_text', $missionVision['settings']['mission_text'] ?? '') }}</textarea></div>
                                <div class="field"><label>Texto visión</label><textarea name="mission_vision[vision_text]" class="field-small">{{ old('mission_vision.vision_text', $missionVision['settings']['vision_text'] ?? '') }}</textarea></div>
                            </div>
                        </div>
                        <div class="subpanel span-6">
                            <h4>Texto principal de historia</h4>
                            <div class="grid grid-2">
                                <div class="field"><label>Ceja</label><input type="text" name="history[kicker]" value="{{ old('history.kicker', $history['settings']['kicker'] ?? '') }}"></div>
                                <div class="field"><label>Título</label><input type="text" name="history[title]" value="{{ old('history.title', $history['settings']['title'] ?? '') }}"></div>
                                <div class="field" style="grid-column:1 / -1;"><label>Texto principal</label><textarea name="history[text]" class="field-small">{{ old('history.text', $history['settings']['text'] ?? '') }}</textarea></div>
                                <div class="field"><label>Título fallback</label><input type="text" name="history[carousel_title]" value="{{ old('history.carousel_title', $history['settings']['carousel_title'] ?? '') }}"></div>
                                <div class="field"><label>Texto fallback</label><input type="text" name="history[carousel_text]" value="{{ old('history.carousel_text', $history['settings']['carousel_text'] ?? '') }}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>imágenes o videos de historia</h4><p>carrusel visual puro para imagenes o videos, sin textos encima en sitio web.</p></div><button type="button" class="button button-secondary" data-add-row>Agregar slide</button></div>
                        <div class="stack" data-collection data-base="history[items]" data-template="history-template">
                            <div data-rows>
                                @foreach ($history['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ ($item['media_type'] ?? 'image') === 'video' ? 'Video' : 'Imagen' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field">
                                                <label>Tipo de medio</label>
                                                <select data-field="media_type">
                                                    <option value="image" {{ ($item['media_type'] ?? 'image') === 'image' ? 'selected' : '' }}>Imagen</option>
                                                    <option value="video" {{ ($item['media_type'] ?? '') === 'video' ? 'selected' : '' }}>Video</option>
                                                </select>
                                            </div>
                                            <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" data-field="duration_seconds" value="{{ $item['duration_seconds'] ?? 6 }}"></div>
                                            <div class="field"><label>Enlace del medio</label><input type="text" data-field="media_url" value="{{ $item['media_url'] ?? ($item['image'] ?? '') }}"></div>
                                        </div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Subir imagen o video</label><input type="file" data-field="media_file" accept="image/*,video/mp4,video/webm"></div>
                                            <div class="field"><label>Portada del video</label><input type="file" data-field="poster_file" accept="image/*"></div>
                                        </div>
                                        <div class="field" style="margin-top:12px;"><label>Enlace portada (opcional para video)</label><input type="text" data-field="poster_image" value="{{ $item['poster_image'] ?? '' }}"></div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'principles'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Valores</div><h3 class="section-title">Principios institucionales</h3><p class="section-copy">Tarjetas con icono, titulo y descripcion.</p></div>
                        <span class="pill pill-off">{{ count($principles['items']) }} principios</span>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-2">
                            <div class="field"><label>Título de seccion</label><input type="text" name="principles[title]" value="{{ old('principles.title', $principles['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Subtitulo</label><input type="text" name="principles[subtitle]" value="{{ old('principles.subtitle', $principles['settings']['subtitle'] ?? '') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Tarjetas de principios</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar principio</button></div>
                        <div class="stack" data-collection data-base="principles[items]" data-template="principle-template">
                            <div data-rows>
                                @foreach ($principles['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ $item['title'] ?? 'Principio' }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-2" style="margin-top:12px;">
                                            <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? '' }}"></div>
                                            <div class="field"><label>Título</label><input type="text" data-field="title" value="{{ $item['title'] ?? '' }}"></div>
                                        </div>
                                        <div class="field" style="margin-top:12px;"><label>Texto</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'organigram'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Institucional</div><h3 class="section-title">Bloque de organigrama</h3><p class="section-copy">Controla el titulo externo de la seccion y el medio visual del organigrama, sin textos encima del visual.</p></div>
                    </div>
                    <div class="design-grid">
                        <div class="subpanel span-7">
                            <h4>Contenido del bloque</h4>
                            <div class="grid grid-2">
                                <div class="field"><label>Título de seccion</label><input type="text" name="organigram[title]" value="{{ old('organigram.title', $organigram['settings']['title'] ?? '') }}"></div>
                                <div class="field"><label>Subtitulo</label><input type="text" name="organigram[subtitle]" value="{{ old('organigram.subtitle', $organigram['settings']['subtitle'] ?? '') }}"></div>
                                <div class="field">
                                    <label>Tipo de medio</label>
                                    <select name="organigram[media_type]">
                                        <option value="image" {{ old('organigram.media_type', $organigram['settings']['media_type'] ?? 'image') === 'image' ? 'selected' : '' }}>Imagen</option>
                                        <option value="video" {{ old('organigram.media_type', $organigram['settings']['media_type'] ?? '') === 'video' ? 'selected' : '' }}>Video</option>
                                    </select>
                                </div>
                                <div class="field"><label>Enlace del medio</label><input type="text" name="organigram[media_url]" value="{{ old('organigram.media_url', $organigram['settings']['media_url'] ?? ($organigram['settings']['image'] ?? '')) }}"></div>
                                <div class="field" style="grid-column:1 / -1;"><label>Enlace portada (opcional para video)</label><input type="text" name="organigram[poster_image]" value="{{ old('organigram.poster_image', $organigram['settings']['poster_image'] ?? '') }}"></div>
                            </div>
                        </div>
                        <div class="subpanel span-5">
                            <h4>Archivo visual</h4>
                            <div class="field"><label>Subir imagen o video del organigrama</label><input type="file" name="organigram[media_file]" accept="image/*,video/mp4,video/webm"></div>
                            <div class="field" style="margin-top:12px;"><label>Subir portada del video</label><input type="file" name="organigram[poster_file]" accept="image/*"></div>
                            @if (!empty($organigram['settings']['media_url']) || !empty($organigram['settings']['image']))
                                <div class="image-frame" style="margin-top:16px;">
                                    <img src="{{ $organigram['settings']['poster_image'] ?? ($organigram['settings']['media_url'] ?? $organigram['settings']['image']) }}" alt="Organigrama actual" style="width:100%; border-radius:18px;">
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="section-card" x-show="tab === 'objectives'">
                    <div class="section-header">
                        <div><div class="section-eyebrow">Planeacion</div><h3 class="section-title">Objetivos estrategicos</h3><p class="section-copy">Lista editable de metas institucionales con texto principal y marcador visual.</p></div>
                        <span class="pill pill-off">{{ count($objectives['items']) }} objetivo(s)</span>
                    </div>
                    <div class="subpanel">
                        <div class="grid grid-2">
                            <div class="field"><label>Título de seccion</label><input type="text" name="objectives[title]" value="{{ old('objectives.title', $objectives['settings']['title'] ?? '') }}"></div>
                            <div class="field"><label>Subtitulo</label><input type="text" name="objectives[subtitle]" value="{{ old('objectives.subtitle', $objectives['settings']['subtitle'] ?? '') }}"></div>
                        </div>
                    </div>
                    <div class="subpanel">
                        <div class="toolbar"><div><h4>Objetivos institucionales</h4></div><button type="button" class="button button-secondary" data-add-row>Agregar objetivo</button></div>
                        <div class="stack" data-collection data-base="objectives[items]" data-template="objective-template">
                            <div data-rows>
                                @foreach ($objectives['items'] as $item)
                                    <div class="repeater-card" data-row>
                                        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>{{ \Illuminate\Support\Str::limit($item['text'] ?? 'Objetivo', 60) }}</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
                                        <div class="grid grid-3" style="margin-top:12px;">
                                            <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="{{ $item['icon'] ?? 'target' }}"></div>
                                            <div class="field" style="grid-column: span 2;"><label>Texto</label><textarea class="field-small" data-field="text">{{ $item['text'] ?? '' }}</textarea></div>
                                        </div>
                                        <input type="hidden" data-field="id" value="{{ $item['id'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                @include('admin.pages.partials.save')
            </div>
        </div>
    </form>
</div>

<template id="hero-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Slide</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-3" style="margin-top:12px;">
            <div class="field">
                <label>Tipo de medio</label>
                <select data-field="media_type">
                    <option value="image" selected>Imagen</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" data-field="duration_seconds" value="5"></div>
            <div class="field"><label>Enlace del medio</label><input type="text" data-field="media_url"></div>
        </div>
        <div class="grid grid-2" style="margin-top:12px;">
            <div class="field"><label>Subir imagen o video</label><input type="file" data-field="media_file" accept="image/*,video/mp4,video/webm"></div>
            <div class="field"><label>Portada del video</label><input type="file" data-field="poster_file" accept="image/*"></div>
        </div>
        <div class="field" style="margin-top:12px;"><label>Enlace portada (opcional para video)</label><input type="text" data-field="poster_image"></div>
        <input type="hidden" data-field="id">
    </div>
</template>

<template id="history-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Historia</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-3" style="margin-top:12px;">
            <div class="field">
                <label>Tipo de medio</label>
                <select data-field="media_type">
                    <option value="image" selected>Imagen</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div class="field"><label>Duración (segundos)</label><input type="number" min="1" max="300" data-field="duration_seconds" value="6"></div>
            <div class="field"><label>Enlace del medio</label><input type="text" data-field="media_url"></div>
        </div>
        <div class="grid grid-2" style="margin-top:12px;">
            <div class="field"><label>Subir imagen o video</label><input type="file" data-field="media_file" accept="image/*,video/mp4,video/webm"></div>
            <div class="field"><label>Portada del video</label><input type="file" data-field="poster_file" accept="image/*"></div>
        </div>
        <div class="field" style="margin-top:12px;"><label>Enlace portada (opcional para video)</label><input type="text" data-field="poster_image"></div>
        <input type="hidden" data-field="id">
    </div>
</template>

<template id="principle-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Principio</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-2" style="margin-top:12px;">
            <div class="field"><label>Ícono</label><input type="text" data-field="icon"></div>
            <div class="field"><label>Título</label><input type="text" data-field="title"></div>
        </div>
        <div class="field" style="margin-top:12px;"><label>Texto</label><textarea class="field-small" data-field="text"></textarea></div>
        <input type="hidden" data-field="id">
    </div>
</template>

<template id="objective-template">
    <div class="repeater-card" data-row>
        <div class="toolbar"><div class="actions"><span class="drag-handle" data-drag>::</span><strong>Objetivo</strong></div><button type="button" class="button button-danger" data-remove-row>Eliminar</button></div>
        <div class="grid grid-3" style="margin-top:12px;">
            <div class="field"><label>Ícono</label><input type="text" data-field="icon" value="target"></div>
            <div class="field" style="grid-column: span 2;"><label>Texto</label><textarea class="field-small" data-field="text"></textarea></div>
        </div>
        <input type="hidden" data-field="id">
    </div>
</template>
@endsection
