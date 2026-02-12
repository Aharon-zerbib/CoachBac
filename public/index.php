<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// --- REPARATION RADICALE CORS & OUTPUT ---
// On vide tout buffer de texte qui pourrait corrompre le JSON
// --- REPARATION RADICALE WINDOWS/HERD ---
$tmpDir = dirname(__DIR__) . '/storage/app/tmp';
if (!is_dir($tmpDir)) @mkdir($tmpDir, 0777, true);
@ini_set('upload_tmp_dir', $tmpDir);
@ini_set('sys_temp_dir', $tmpDir);
// ----------------------------------------

ob_start();

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
