<?php

namespace App\Console\Commands;

use App\Models\SitePage;
use App\Models\SitePageVersion;
use App\Support\ContentSecurity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupCmsAssets extends Command
{
    protected $signature = 'cms:cleanup-assets
        {--delete : Elimina los archivos huerfanos encontrados}
        {--path=cms : Carpeta base dentro del disco public a revisar}';

    protected $description = 'Detecta y opcionalmente elimina archivos huerfanos del CMS que ya no estan en uso.';

    public function handle(): int
    {
        $basePath = trim((string) $this->option('path'), '/');

        if ($basePath === '') {
            $this->error('La carpeta base no puede estar vacia.');
            return self::FAILURE;
        }

        $referenced = $this->collectReferencedAssets();
        $existingFiles = collect(Storage::disk('public')->allFiles($basePath))
            ->filter(fn (string $path) => $path !== '')
            ->values();

        $orphans = $existingFiles
            ->reject(fn (string $path) => in_array($path, $referenced, true))
            ->values();

        $this->info("Carpeta analizada: {$basePath}");
        $this->line('Archivos referenciados: ' . count($referenced));
        $this->line('Archivos encontrados: ' . $existingFiles->count());
        $this->line('Archivos huerfanos: ' . $orphans->count());
        $this->newLine();

        if ($orphans->isEmpty()) {
            $this->info('No se encontraron archivos huerfanos.');
            return self::SUCCESS;
        }

        $this->warn('Archivos candidatos a limpieza:');
        foreach ($orphans as $path) {
            $this->line(' - ' . $path);
        }

        if (! $this->option('delete')) {
            $this->newLine();
            $this->comment('Modo seguro: no se elimino nada.');
            $this->comment("Si el listado se ve bien, ejecuta: php artisan cms:cleanup-assets --delete");
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($orphans as $path) {
            if (Storage::disk('public')->exists($path) && Storage::disk('public')->delete($path)) {
                $deleted++;
            }
        }

        $this->newLine();
        $this->info("Limpieza completada. Archivos eliminados: {$deleted}");

        return self::SUCCESS;
    }

    protected function collectReferencedAssets(): array
    {
        $paths = [];

        SitePage::query()
            ->with([
                'sections' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'sections.items' => function ($query) {
                    $query->orderBy('sort_order');
                },
            ])
            ->get()
            ->each(function (SitePage $page) use (&$paths) {
                $this->collectAssetPathsFromArray($page->theme ?? [], $paths);

                foreach ($page->sections ?? [] as $section) {
                    $this->collectAssetPathsFromArray($section->settings ?? [], $paths);

                    foreach ($section->items ?? [] as $item) {
                        $this->collectAssetPathsFromArray($item->data ?? [], $paths);
                    }
                }
            });

        SitePageVersion::query()
            ->get()
            ->each(function (SitePageVersion $version) use (&$paths) {
                $snapshot = $version->snapshot ?? [];

                $this->collectAssetPathsFromArray($snapshot['theme'] ?? [], $paths);

                foreach ($snapshot['sections'] ?? [] as $section) {
                    $this->collectAssetPathsFromArray($section['settings'] ?? [], $paths);

                    foreach ($section['items'] ?? [] as $item) {
                        $this->collectAssetPathsFromArray($item['data'] ?? [], $paths);
                    }
                }
            });

        return array_values(array_unique(array_filter($paths)));
    }

    protected function collectAssetPathsFromArray(array $data, array &$paths): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->collectAssetPathsFromArray($value, $paths);
                continue;
            }

            if (! in_array($key, ContentSecurity::ASSET_KEYS, true)) {
                continue;
            }

            $path = $this->normalizePublicStoragePath($value);

            if ($path) {
                $paths[] = $path;
            }
        }
    }

    protected function normalizePublicStoragePath(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        $path = parse_url($value, PHP_URL_PATH) ?: $value;
        $path = '/' . ltrim($path, '/');

        if (! str_starts_with($path, '/storage/')) {
            return null;
        }

        return ltrim(substr($path, strlen('/storage/')), '/');
    }
}
