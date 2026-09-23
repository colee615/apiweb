<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$file = __DIR__.'/app/admin-review.sqlite';
if (file_exists($file)) { fwrite(STDERR, 'Review database already exists'); exit(1); }
touch($file);
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>$file]);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
App\Models\User::create(['name'=>'Equipo editorial','email'=>'review@example.test','password'=>bcrypt('Review-local-2026'),'job_title'=>'Administrador','is_active'=>true]);
for ($day=0; $day<28; $day++) {
    for ($i=0; $i<($day%7+3); $i++) {
        App\Models\AnalyticsEvent::create(['visitor_token'=>'visitor-'.$i,'session_token'=>'session-'.$i,'event_name'=>'page_view','page_path'=>$i%2 ? '/ems' : '/','page_name'=>$i%2 ? 'EMS' : 'Inicio','occurred_at'=>now()->subDays($day)->setTime(10,0)]);
    }
}
echo 'Isolated review database ready';
