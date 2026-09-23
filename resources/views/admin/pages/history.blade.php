@extends('layouts.admin')
@section('content')
@php
    $historySections = $historyData['history_sections'];
    $currentVersionNumber = $page->latest_version ?? optional($versions->first())->version_number;
    $historySectionLabels = collect($historySections)->pluck('label', 'key')->all();
    $historySectionLabels['general'] = 'General';
    $historyActionLabels = [
        'created' => 'Creacion',
        'updated' => 'Actualizacion',
        'deleted' => 'Eliminacion',
        'restored' => 'Restauración',
    ];
    $historyFieldLabels = [
        'slug' => 'Slug',
        'name' => 'Nombre',
        'title' => 'Título',
        'subtitle' => 'Subtítulo',
        'text' => 'Texto',
        'description' => 'Descripción',
        'label' => 'Etiqueta',
        'placeholder' => 'Placeholder',
        'button_label' => 'Texto del botón',
        'tracking_title' => 'Título de rastreo',
        'tracking_text' => 'Texto de rastreo',
        'tracking_label' => 'Etiqueta de rastreo',
        'tracking_placeholder' => 'Placeholder de rastreo',
        'tracking_button' => 'Botón de rastreo',
        'eyebrow' => 'Etiqueta superior',
        'hero_title' => 'Título protagonista',
        'watermark_text' => 'Marca de agua',
        'highlight_text' => 'Texto destacado',
        'paragraph_one' => 'Párrafo 1',
        'paragraph_two' => 'Párrafo 2',
        'paragraph_three' => 'Párrafo 3',
        'primary_button_label' => 'Texto del botón principal',
        'primary_button_url' => 'Enlace del botón principal',
        'secondary_button_label' => 'Texto del botón secundario',
        'secondary_button_url' => 'Enlace del botón secundario',
        'visual_icon' => 'Icono visual',
        'cta_text' => 'Texto de llamada a la acción',
        'stat_label' => 'Etiqueta de métrica',
        'stat_value' => 'Valor de métrica',
        'stat_caption' => 'Leyenda de métrica',
        'badge' => 'Insignia',
        'view_all_label' => 'Texto de ver todo',
        'view_all_url' => 'Enlace de ver todo',
        'app_store_label' => 'Texto App Store',
        'play_store_label' => 'Texto Google Play',
        'app_store_url' => 'Enlace App Store',
        'play_store_url' => 'Enlace Google Play',
        'resource_type' => 'Tipo de acceso',
        'download_url' => 'Archivo instalable',
        'download_name' => 'Nombre del instalador',
        'map_title' => 'Título del mapa',
        'map_text' => 'Texto del mapa',
        'map_button_label' => 'Botón del mapa',
        'maps_url' => 'Google Maps URL',
        'weekday_hours' => 'Horario de lunes a viernes',
        'saturday_hours' => 'Horario de sábado',
        'calculator_title' => 'Título de calculadora',
        'calculator_text' => 'Texto de calculadora',
        'origin_label' => 'Etiqueta de origen',
        'origin_placeholder' => 'Placeholder de origen',
        'destination_label' => 'Etiqueta de destino',
        'destination_placeholder' => 'Placeholder de destino',
        'weight_label' => 'Etiqueta de peso',
        'weight_placeholder' => 'Placeholder de peso',
        'calculate_button_label' => 'Botón de calcular',
        'help_label' => 'Ayuda / contacto',
        'login_label' => 'Inicio de sesión',
        'search_placeholder' => 'Placeholder de búsqueda',
        'language_primary' => 'Idioma principal',
        'language_secondary' => 'Idioma secundario',
        'accessibility_label' => 'Etiqueta de accesibilidad',
        'url' => 'Enlace',
        'src' => 'Imagen o archivo',
        'poster' => 'Portada',
        'poster_image' => 'Imagen principal',
        'poster_title' => 'Título del popup',
        'poster_caption' => 'Pie del popup',
        'icon' => 'Icono',
        'iconImage' => 'Imagen del icono',
        'image' => 'Imagen',
        'background_image' => 'Imagen de fondo',
        'price' => 'Precio',
        'year' => 'Año',
        'series' => 'Serie',
        'dept' => 'Código de departamento',
        'group' => 'Grupo',
        'media_type' => 'Tipo de medio',
        'phone' => 'Teléfono',
        'email' => 'Correo',
        'address' => 'Dirección',
        'seal_logo' => 'Logo inferior del footer',
        'copyright' => 'Copyright',
        'legal_text' => 'Texto legal',
        'help_title' => 'Título de ayuda',
        'company_title' => 'Título de empresa',
        'contact_title' => 'Título de contacto',
        'social_title' => 'Título de redes',
        'social_text' => 'Texto de redes',
        'logo_url' => 'Logo',
        'primary_color' => 'Color principal',
        'secondary_color' => 'Color secundario',
        'accent_color' => 'Color de acento',
        'enabled' => 'Visibilidad',
        'show_once' => 'Mostrar solo una vez',
        'storage_key' => 'Clave de control',
        'settings' => 'Configuración',
        'data' => 'Contenido',
        'theme' => 'Identidad visual',
        'page_meta' => 'Configuración general',
        'is_active' => 'Estado',
        'sort_order' => 'Orden',
        'type' => 'Tipo',
        'key' => 'Clave',
        'left' => 'Posición izquierda',
        'top' => 'Posición superior',
    ];
    $historyIgnoredKeys = ['id', 'page_id', 'section_id', 'item_id', 'created_at', 'updated_at'];
    $historyAssetFields = ['src', 'poster', 'poster_image', 'iconImage', 'image', 'background_image', 'logo_url', 'seal_logo'];
    $isAssocHistoryArray = function (array $value): bool {
        return array_keys($value) !== range(0, count($value) - 1);
    };
    $formatHistoryPath = function (array $segments) use ($historyFieldLabels) {
        $labels = collect($segments)
            ->filter(fn ($segment) => filled($segment))
            ->map(function ($segment) use ($historyFieldLabels) {
                if (is_int($segment)) {
                    return 'Elemento ' . ($segment + 1);
                }

                if (is_string($segment) && str_starts_with($segment, '#')) {
                    return 'Elemento ' . substr($segment, 1);
                }

                return $historyFieldLabels[$segment] ?? ucfirst(str_replace('_', ' ', (string) $segment));
            })
            ->values()
            ->all();

        return implode(' > ', $labels);
    };
    $describeHistoryValue = function ($value, array $segments = []) use ($historyAssetFields) {
        $field = end($segments) ?: null;

        if ($value === null || $value === '') {
            return 'Sin valor';
        }

        if (is_bool($value)) {
            return $value ? 'Activo' : 'Inactivo';
        }

        if (is_array($value)) {
            return count($value) . ' elemento(s)';
        }

        $text = trim((string) $value);

        if (in_array($field, $historyAssetFields, true) || filter_var($text, FILTER_VALIDATE_URL)) {
            $path = parse_url($text, PHP_URL_PATH) ?: $text;
            $filename = basename($path);
            return $filename ? 'Archivo: ' . $filename : 'Archivo o recurso vinculado';
        }

        return \Illuminate\Support\Str::limit($text, 160);
    };
    $buildHistoryDiff = function ($before, $after, array $segments = []) use (&$buildHistoryDiff, $historyIgnoredKeys, $isAssocHistoryArray, $formatHistoryPath, $describeHistoryValue, $historyAssetFields) {
        $changes = [];

        if (is_array($before) || is_array($after)) {
            $beforeArray = is_array($before) ? $before : [];
            $afterArray = is_array($after) ? $after : [];

            if ($isAssocHistoryArray($beforeArray ?: $afterArray)) {
                $keys = collect(array_keys($beforeArray))
                    ->merge(array_keys($afterArray))
                    ->unique()
                    ->reject(fn ($key) => in_array($key, $historyIgnoredKeys, true))
                    ->values();

                foreach ($keys as $key) {
                    $changes = array_merge(
                        $changes,
                        $buildHistoryDiff($beforeArray[$key] ?? null, $afterArray[$key] ?? null, [...$segments, $key])
                    );
                }

                return $changes;
            }

            $max = max(count($beforeArray), count($afterArray));

            for ($index = 0; $index < $max; $index++) {
                $changes = array_merge(
                    $changes,
                    $buildHistoryDiff($beforeArray[$index] ?? null, $afterArray[$index] ?? null, [...$segments, $index])
                );
            }

            return $changes;
        }

        if ($before === $after) {
            return [];
        }

        $field = end($segments) ?: null;
        $isAsset = in_array($field, $historyAssetFields, true);
        $changeType = 'Actualizado';

        if (($before === null || $before === '') && ($after !== null && $after !== '')) {
            $changeType = 'Agregado';
        } elseif (($after === null || $after === '') && ($before !== null && $before !== '')) {
            $changeType = 'Eliminado';
        } elseif ($isAsset) {
            $changeType = 'Imagen o archivo reemplazado';
        } elseif (is_string($before) || is_string($after)) {
            $changeType = 'Texto actualizado';
        }

        return [[
            'label' => $formatHistoryPath($segments),
            'type' => $changeType,
            'before' => $describeHistoryValue($before, $segments),
            'after' => $describeHistoryValue($after, $segments),
        ]];
    };
@endphp

@php
    $selectedSection = request('section', str_starts_with((string) request('tab'), 'history_') ? substr(request('tab'), 8) : '');
    $selected = collect($historySections)->firstWhere('key', $selectedSection);
    $logs = $selected ? $selected['logs'] : $historyData['latest_changes'];
@endphp
<div class="admin-shell stack" id="history-root">
    <header class="admin-topbar">
        <div class="admin-brand"><a class="back-link" href="{{ route('admin.dashboard') }}">← Todas las páginas</a><h1>Historial de cambios</h1><p>{{ $page->name }} · Revisa quién cambió el contenido y recupera una versión anterior.</p></div>
        <a class="button button-primary" href="{{ route('admin.pages.edit', $page) }}">Volver al editor</a>
    </header>
    @if(session('status'))<div class="notice notice-success" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="notice notice-error" role="alert">{{ $errors->first() }}</div>@endif
    <section class="panel panel-body grid grid-2">
        <div class="field"><label for="history-page">Página</label><select id="history-page" onchange="window.location.href = this.value">
            @foreach($pages as $historyPage)<option value="{{ route('admin.pages.edit', ['page' => $historyPage, 'tab' => 'history_overview']) }}" @selected($page->id === $historyPage->id)>{{ $historyPage->name }}</option>@endforeach
        </select></div>
        <form method="GET" class="field"><input type="hidden" name="tab" value="history_overview"><label for="history-section">Filtrar cambios por sección</label><div class="actions"><select id="history-section" name="section" style="flex:1"><option value="">Cambios recientes de todas las secciones</option>@foreach($historySections as $section)<option value="{{ $section['key'] }}" @selected($selectedSection === $section['key'])>{{ $section['label'] }} ({{ $section['count'] }})</option>@endforeach</select><button class="button button-secondary" type="submit">Filtrar</button></div></form>
    </section>
    <div class="history-grid">
        <section class="panel">
            <div class="table-toolbar"><div><strong>Versiones guardadas</strong><p>Últimas {{ $versions->count() }} versiones de esta página.</p></div></div>
            @forelse($versions as $version)
                <article class="history-version">
                    <div class="toolbar"><strong>Versión {{ $version->version_number }}</strong>@if((int) $version->version_number === (int) $currentVersionNumber)<span class="pill pill-ok">Actual</span>@endif</div>
                    <p>{{ $version->created_at?->format('d/m/Y H:i') }} · {{ $version->created_by_name ?: 'Sistema' }}</p>
                    <p>{{ $version->change_summary ?: ($historyActionLabels[$version->action] ?? 'Guardado de contenido') }}</p>
                    @if((int) $version->version_number !== (int) $currentVersionNumber)
                    <form method="POST" action="{{ route('admin.pages.restore', [$page, $version]) }}" onsubmit="return confirm('Se restaurará el contenido completo de esta página. El estado actual permanecerá en el historial. ¿Continuar?')">
                        @csrf<input type="hidden" name="change_summary" value="Restauración desde la versión {{ $version->version_number }}"><button class="button button-ghost" type="submit">Restaurar esta versión</button>
                    </form>
                    @endif
                </article>
            @empty<div class="panel-body empty-note">Las versiones aparecerán al guardar cambios.</div>@endforelse
        </section>
        <section class="panel">
            <div class="table-toolbar"><div><strong>{{ $selected['label'] ?? 'Cambios recientes' }}</strong><p>{{ $selected ? count($logs) : $historyData['total_changes'] }} cambios registrados{{ $selected ? '' : ' · se muestran los últimos 20' }}.</p></div></div>
            @forelse($logs as $log)
                <article class="history-change">
                    <span class="pill pill-off">{{ $historySectionLabels[$log->section_key ?: 'general'] ?? $log->section_key }}</span>
                    <h3>{{ $log->summary ?: 'Contenido actualizado' }}</h3>
                    <p>{{ $log->created_by_name ?: 'Sistema' }} · {{ $log->created_at?->format('d/m/Y H:i') }} · Versión {{ $log->version?->version_number ?? '—' }}</p>
                    @php($changes = $buildHistoryDiff($log->before_state ?? [], $log->after_state ?? []))
                    @if(count($changes))
                    <details class="history-details"><summary>Ver {{ count($changes) }} diferencias</summary><div class="stack" style="margin-top:12px;gap:12px">@foreach($changes as $change)<div><strong>{{ $change['label'] }}</strong><div class="grid grid-2"><div><small class="muted">ANTES</small><div>{{ $change['before'] }}</div></div><div><small class="muted">DESPUÉS</small><div>{{ $change['after'] }}</div></div></div></div>@endforeach</div></details>
                    @endif
                </article>
            @empty<div class="panel-body empty-note">Todavía no hay cambios registrados en esta sección.</div>@endforelse
        </section>
    </div>
</div>
@endsection
