<?php
use App\Models\User;
use Google\Client;
use Google\Service\Fitness;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::first();
$client = new Client();
$client->setClientId(config('services.google.client_id'));
$client->setClientSecret(config('services.google.client_secret'));
$client->setAccessToken($user->google_token);

$fitness = new Fitness($client);
$endTime = time() * 1000 * 1000000;

$sources = [
    'derived:com.google.weight:com.google.android.gms:merge_weight',
    'raw:com.google.weight:com.google.android.apps.fitness:user_input',
    'derived:com.google.height:com.google.android.gms:merge_height',
    'raw:com.google.height:com.google.android.apps.fitness:user_input'
];

foreach ($sources as $source) {
    echo "--- Source: $source ---
";
    try {
        $data = $fitness->users_dataSources_datasets->get('me', $source, "0-$endTime");
        echo "Points found: " . count($data->getPoint()) . "
";
        foreach ($data->getPoint() as $point) {
            echo "Value: " . $point->getValue()[0]->getFpVal() . " at " . $point->getEndTimeNanos() . "
";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "
";
    }
}
