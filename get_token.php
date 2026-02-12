<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;

$user = User::first();
if (!$user) {
    echo "ERREUR : Pas d'utilisateur.
";
    exit(1);
}

// Supprimer les anciens tokens pour être propre
$user->tokens()->delete();
$token = $user->createToken('test_token')->plainTextToken;

echo $token;
