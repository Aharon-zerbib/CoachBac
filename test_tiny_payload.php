<?php
$token = "5|BNP9eeQs7JAKX9H6OgC5QvKRTQ0EUWkZsBG96TWrfee9f5c3";
// Image pixel pour faire un payload minuscule
$base64 = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==";

$payload = json_encode([
    'image_data' => $base64,
    'description' => 'Test payload minuscule'
]);

$ch = curl_init("http://127.0.0.1:8000/api/meals");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json",
    "Accept: application/json"
]);

echo "🚀 Envoi requête minuscule...
";
$response = curl_exec($ch);
echo "Response: $response
";
curl_close($ch);
