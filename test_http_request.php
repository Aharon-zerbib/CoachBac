<?php
$token = "5|BNP9eeQs7JAKX9H6OgC5QvKRTQ0EUWkZsBG96TWrfee9f5c3";
$imagePath = "C:\Users\User\Pictures\Screenshots\Capture d'écran 2026-02-12 132843.png";

if (!file_exists($imagePath)) {
    die("ERREUR : Image introuvable.");
}

$base64 = "data:image/png;base64," . base64_encode(file_get_contents($imagePath));

$payload = json_encode([
    'image_data' => $base64,
    'description' => 'Test de bout en bout via script'
]);

$ch = curl_init("http://127.0.0.1:8000/api/meals");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
    "Accept: application/json",
    "Origin: http://localhost:3000" // Simuler le frontend
]);

echo "🚀 Envoi de la requête HTTP (taille payload: " . round(strlen($payload)/1024) . " Ko)...
";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $httpCode
";
if ($error) {
    echo "CURL Error: $error
";
} else {
    echo "Response:
";
    echo $response . "
";
}
