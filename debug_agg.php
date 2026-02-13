<?php
use App\Models\User;
use Google\Client;
use Google\Service\Fitness;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::first();
$client = new Client();
$client->setClientId(config('services.google.client_id'));
$client->setClientSecret(config('services.google.client_secret'));
$client->setAccessToken($user->google_token);

$fitness = new Fitness($client);
$targetDate = Carbon::now();
$startTime = $targetDate->copy()->startOfDay()->getTimestamp() * 1000;
$endTime = $targetDate->copy()->endOfDay()->getTimestamp() * 1000;

$req = new \Google\Service\Fitness\AggregateRequest();
$req->setAggregateBy([(new \Google\Service\Fitness\AggregateBy())->setDataTypeName('com.google.step_count.delta')]);
$bt = new \Google\Service\Fitness\BucketByTime();
$bt->setDurationMillis(86400000);
$req->setBucketByTime($bt);
$req->setStartTimeMillis($startTime);
$req->setEndTimeMillis($endTime);

$res = $fitness->users_dataset->aggregate('me', $req);
echo "Buckets: " . count($res->getBucket()) . "
";
foreach ($res->getBucket() as $i => $bucket) {
    echo "Bucket $i:
";
    foreach ($bucket->getDataset() as $j => $ds) {
        echo "  Dataset $j Points: " . count($ds->getPoint()) . "
";
        foreach ($ds->getPoint() as $k => $point) {
            echo "    Point $k: " . $point->getValue()[0]->getIntVal() . "
";
        }
    }
}
