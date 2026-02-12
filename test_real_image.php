<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use App\Services\GeminiService;

// Simulation de l'utilisateur
$user = User::first();
if (!$user) {
    echo "❌ ERREUR : Aucun utilisateur en base.
";
    exit(1);
}

echo "✅ Utilisateur trouvé : " . $user->name . "
";
echo "⏳ Chargement de l'image de test...
";

// Chemin de l'image fournie par Ronron
$imagePath = "C:\Users\User\Pictures\Screenshots\Capture d'écran 2026-02-12 132843.png";

if (!file_exists($imagePath)) {
    echo "❌ ERREUR : L'image n'existe pas à cet emplacement : $imagePath
";
    echo "⚠️ Je vais créer une image de test rouge pour simuler.
";
    $base64Data = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==";
    $mimeType = "image/png";
} else {
    $imageData = file_get_contents($imagePath);
    $base64Data = base64_encode($imageData);
    $mimeType = mime_content_type($imagePath);
    echo "✅ Image chargée (" . round(strlen($base64Data)/1024) . " Ko)
";
}

echo "🚀 Envoi à Gemini 2.5 Flash...
";

try {
    $service = new GeminiService();
    // Appel direct au service avec décodage interne automatique
    $result = $service->analyzeMeal($user, $base64Data, $mimeType, "Test technique automatique");

    if ($result) {
        echo "
🎉 SUCCÈS ! Réponse de l'IA :
";
        print_r($result);
    } else {
        echo "
❌ ÉCHEC : L'IA a renvoyé null (voir logs).
";
    }

} catch (\Throwable $e) {
    echo "
💥 CRASH FATAL : " . $e->getMessage() . "
";
}
