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
$sources = $fitness->users_dataSources->listUsersDataSources('me');

foreach ($sources->getDataSource() as $source) {
    echo $source->getDataStreamId() . " (" . $source->getDataType()->getName() . ")
";
}
