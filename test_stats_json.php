<?php
use App\Models\User;
use App\Http\Controllers\ActivityController;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::first();
$controller = new ActivityController();
$request = Request::create('/api/stats', 'GET', ['date' => '2026-02-13']);
$request->setUserResolver(fn() => $user);

$response = $controller->stats($request, app(App\Services\GoogleFitService::class));
header('Content-Type: application/json');
echo json_encode($response->getData(), JSON_PRETTY_PRINT);
