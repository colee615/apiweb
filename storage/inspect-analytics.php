<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $events = App\Models\AnalyticsEvent::query()->select('event_name', 'metadata->source as source')->selectRaw('COUNT(*) as total')->groupBy('event_name','metadata->source')->orderByDesc('total')->limit(30)->get();
    $metadata = App\Models\AnalyticsEvent::query()->whereNotNull('metadata')->latest('occurred_at')->limit(100)->get(['metadata'])->flatMap(fn($e)=>array_keys($e->metadata ?? []))->unique()->values();
    echo json_encode(['events'=>$events, 'metadata_keys'=>$metadata], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) { echo 'Could not inspect analytics: '.get_class($error); }
