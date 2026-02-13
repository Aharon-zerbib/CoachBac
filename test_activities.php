<?php
use App\Models\User;
use App\Models\Activity;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(Activity::all() as $a) {
    echo $a->created_at . " - " . $a->distance . " km - " . $a->steps . " steps
";
}
