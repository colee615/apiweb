<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>__DIR__.'/app/admin-review.sqlite']);
Illuminate\Support\Facades\DB::purge('sqlite');
for($i=0;$i<35;$i++) {
    foreach(range(0,$i%4) as $repeat) {
        App\Models\AnalyticsEvent::create(['visitor_token'=>'visitor-'.$i%9,'session_token'=>'session-'.$i%9,'event_name'=>'tracking_search','searched_term'=>'CP'.str_pad((string)$i,9,'0',STR_PAD_LEFT).'BO','metadata'=>['source'=>'hero_tracking'],'page_path'=>'/','occurred_at'=>now()->subHours($i*5+$repeat)]);
    }
}
for($i=0;$i<9;$i++) {
    App\Models\AnalyticsVisitorSession::updateOrCreate(['session_token'=>'session-'.$i], ['visitor_token'=>'visitor-'.$i,'device_type'=>$i%3?'mobile':'desktop','browser'=>$i%2?'Chrome':'Safari','platform'=>$i%3?'Android':'Windows','referrer'=>$i%2?'https://www.google.com/':null,'is_online'=>false,'first_seen_at'=>now()->subDays(14),'last_seen_at'=>now()->subHours(2)]);
}
echo 'Isolated tracking fixtures ready';
