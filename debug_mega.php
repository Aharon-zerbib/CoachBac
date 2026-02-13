<?php
use App\Models\User;
use App\Services\GoogleFitService;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'amzyt770@gmail.com')->first();
$service = app(GoogleFitService::class);

echo "Analyse Mega-Sync pour: " . $user->name . "
";
$data = $service->getSyncData($user);
print_r($data);
