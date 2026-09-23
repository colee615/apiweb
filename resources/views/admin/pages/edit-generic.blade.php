@extends('layouts.admin')

@section('content')
    <div class="admin-shell stack">
        @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="notice notice-error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

        @include('admin.pages.partials.header')

        <form id="page-edit-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" data-editor-form>
            @csrf
            @method('PUT')

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Esta página</div>
                        <h2 class="section-title">Nombre y publicación</h2>
                        <p class="section-copy">El logo, los colores, el encabezado y el pie se administran desde Configuración global. Aquí cambias solo los datos propios de esta página.</p>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label for="page-name">Nombre de la página</label><input id="page-name" type="text" name="name" value="{{ old('name', $page->name) }}" maxlength="160" required></div>
                    <div class="field"><label for="page-slug">Enlace de la página</label><input id="page-slug" type="text" name="slug" value="{{ old('slug', $page->slug) }}" maxlength="120" required></div>
                    <div class="field"><label for="meta-title">Título para buscadores</label><input id="meta-title" type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" maxlength="255"></div>
                    <div class="field"><label for="meta-description">Descripción para buscadores</label><textarea id="meta-description" name="meta_description" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea></div>
                    <div class="field"><label class="checkbox-label"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}> Publicar esta página</label></div>
                </div>
            </section>

            @include('admin.pages.partials.save')
        </form>
    </div>
@endsection
