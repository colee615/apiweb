<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$start = microtime(true);
Illuminate\Support\Facades\DB::beginTransaction();
try {
    $request = Illuminate\Http\Request::create('/admin/analytics', 'GET', ['chart_metric'=>'tracking']);
    $view = app(App\Http\Controllers\AdminAnalyticsController::class)->index($request);
    $data = $view->getData();
    echo json_encode(['ok'=>true,'seconds'=>round(microtime(true)-$start,2),'codes'=>$data['summary']['tracking_unique_codes'],'searches'=>$data['summary']['tracking_searches_in_range'],'trend_total'=>$data['trendSeries']->sum('total'),'hour_total'=>$data['hourlyTracking']->sum('total')]);
} catch (Throwable $e) {
    echo json_encode(['ok'=>false,'type'=>get_class($e),'message'=>$e->getMessage()]);
} finally { Illuminate\Support\Facades\DB::rollBack(); }
