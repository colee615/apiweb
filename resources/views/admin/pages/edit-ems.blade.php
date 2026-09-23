@extends('layouts.admin')

@section('content')
    <div class="admin-shell stack">
        @if (session('status'))<div class="notice notice-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="notice notice-error">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

        @include('admin.pages.partials.header')

        <form id="page-edit-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="stack" data-editor-form enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="EMS">
            <input type="hidden" name="slug" value="ems">
            <input type="hidden" name="is_active" value="0">

            <section class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-eyebrow">Página de servicio</div>
                        <h2 class="section-title">Publicación y buscadores</h2>
                        <p class="section-copy">Administra el estado de EMS y el texto que aparece en los resultados de búsqueda.</p>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div class="field"><label for="ems-meta-title">Título para buscadores</label><input id="ems-meta-title" type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" maxlength="255"></div>
                    <div class="field"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}> Publicar la página EMS</label></div>
                    <div class="field" style="grid-column:1/-1"><label for="ems-meta-description">Descripción para buscadores</label><textarea id="ems-meta-description" name="meta_description" rows="3">{{ old('meta_description', $page->meta_description) }}</textarea></div>
                </div>
            </section>

            @include('admin.pages.partials.ems-editor')
            @include('admin.pages.partials.save')
        </form>
    </div>
@endsection
