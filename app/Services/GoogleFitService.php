<?php

namespace App\Services;

use Google\Client;
use Google\Service\Fitness;
use App\Models\User;
use Carbon\Carbon;

class GoogleFitService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        
        $this->client->addScope([
            Fitness::FITNESS_ACTIVITY_READ,
            Fitness::FITNESS_BODY_READ,
            Fitness::FITNESS_NUTRITION_READ,
            Fitness::FITNESS_SLEEP_READ,
            Fitness::FITNESS_HEART_RATE_READ,
            Fitness::FITNESS_BLOOD_PRESSURE_READ,
            Fitness::FITNESS_LOCATION_READ
        ]);
        
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function getSyncData(User $user, $date = null)
    {
        if (!$user->google_token) return null;

        $targetDate = $date ? Carbon::parse($date) : Carbon::now();

        $token = $user->google_token;
        if (str_contains($token, '{')) {
            $decoded = json_decode($token, true);
            $token = $decoded['access_token'] ?? $token;
        }

        $this->client->setAccessToken($token);

        if ($this->client->isAccessTokenExpired()) {
            if ($user->google_refresh_token) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                $user->update(['google_token' => $newToken['access_token']]);
                $this->client->setAccessToken($newToken['access_token']);
            } else {
                return null;
            }
        }

        $fitness = new Fitness($this->client);
        $startTime = $targetDate->copy()->startOfDay()->getTimestamp() * 1000;
        $endTime = $targetDate->copy()->endOfDay()->getTimestamp() * 1000;
        $nanoStart = $startTime * 1000000;
        $nanoEnd = $endTime * 1000000;

        $healthData = [
            'activity' => ['steps' => 0, 'calories' => 0, 'distance' => 0, 'active_minutes' => 0],
            'body' => ['weight' => null, 'height' => null, 'fat_percentage' => null],
            'vitals' => ['heart_rate' => null, 'blood_pressure' => '12/8', 'temp' => 36.6],
            'nutrition' => ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0],
            'sleep' => ['total_hours' => null],
            'cycle' => ['last_period' => 'Janvier 2026']
        ];

        try {
            // 1. CORPS (Poids et Taille) - Diagnostic confirmé
            $bodyMetrics = [
                'weight' => 'derived:com.google.weight:com.google.android.gms:merge_weight',
                'height' => 'derived:com.google.height:com.google.android.gms:merge_height'
            ];

            $tenYearsAgoNanos = (time() - (10 * 365 * 24 * 3600)) * 1000 * 1000000;
            $futureNanos = (time() + (24 * 3600)) * 1000 * 1000000;

            foreach ($bodyMetrics as $key => $source) {
                try {
                    $dataset = $fitness->users_dataSources_datasets->get('me', $source, "$tenYearsAgoNanos-$futureNanos");
                    $points = $dataset->getPoint();
                    if (!empty($points)) {
                        $lastPoint = end($points);
                        $val = $lastPoint->getValue()[0]->getFpVal();
                        $healthData['body'][$key] = ($key === 'height') ? round($val * 100, 0) : round($val, 1);
                    }
                } catch (\Exception $e) {}
            }

            // 2. ACTIVITÉ (Pas, Calories, Distance) - Méthode Aggregate (Celle qui a donné 83 pas)
            $aggRequest = new \Google\Service\Fitness\AggregateRequest();
            $bt = new \Google\Service\Fitness\BucketByTime();
            $bt->setDurationMillis(86400000);
            $aggRequest->setBucketByTime($bt);
            $aggRequest->setStartTimeMillis($startTime);
            $aggRequest->setEndTimeMillis($endTime);

            // Pas
            try {
                $aggBySteps = new \Google\Service\Fitness\AggregateBy();
                $aggBySteps->setDataTypeName('com.google.step_count.delta');
                $aggRequest->setAggregateBy([$aggBySteps]);
                $res = $fitness->users_dataset->aggregate('me', $aggRequest);
                if (!empty($res->getBucket())) {
                    $healthData['activity']['steps'] = $res->getBucket()[0]->getDataset()[0]->getPoint()[0]->getValue()[0]->getIntVal() ?? 0;
                }
            } catch (\Exception $e) {}

            // Calories
            try {
                $aggByCal = new \Google\Service\Fitness\AggregateBy();
                $aggByCal->setDataTypeName('com.google.calories.burned.delta');
                $aggRequest->setAggregateBy([$aggByCal]);
                $res = $fitness->users_dataset->aggregate('me', $aggRequest);
                if (!empty($res->getBucket())) {
                    $healthData['activity']['calories'] = round($res->getBucket()[0]->getDataset()[0]->getPoint()[0]->getValue()[0]->getFpVal() ?? 0);
                }
            } catch (\Exception $e) {}

            // Distance
            try {
                $aggByDist = new \Google\Service\Fitness\AggregateBy();
                $aggByDist->setDataTypeName('com.google.distance.delta');
                $aggRequest->setAggregateBy([$aggByDist]);
                $res = $fitness->users_dataset->aggregate('me', $aggRequest);
                if (!empty($res->getBucket())) {
                    $healthData['activity']['distance'] = round(($res->getBucket()[0]->getDataset()[0]->getPoint()[0]->getValue()[0]->getFpVal() ?? 0) / 1000, 2);
                }
            } catch (\Exception $e) {}

            // 3. CONSTANTES (Fréquence cardiaque)
            try {
                $heartData = $fitness->users_dataSources_datasets->get('me', 'derived:com.google.heart_rate.bpm:com.google.android.gms:merge_heart_rate_bpm', "$nanoStart-$nanoEnd");
                $points = $heartData->getPoint();
                if (!empty($points)) {
                    $healthData['vitals']['heart_rate'] = round(end($points)->getValue()[0]->getFpVal());
                }
            } catch (\Exception $e) {}

            // 4. SOMMEIL
            try {
                $sleepSessions = $fitness->users_sessions->listUsersSessions('me', [
                    'startTime' => $targetDate->copy()->subDay()->startOfDay()->toRfc3339String(),
                    'endTime' => $targetDate->copy()->endOfDay()->toRfc3339String()
                ]);
                foreach ($sleepSessions->getSession() as $session) {
                    if ($session->getActivityType() == 72) {
                        $duration = ($session->getEndTimeMillis() - $session->getStartTimeMillis()) / 3600000;
                        $healthData['sleep']['total_hours'] = round($duration, 1);
                    }
                }
            } catch (\Exception $e) {}

            return $healthData;
        } catch (\Exception $e) {
            \Log::error("Mega Sync Error: " . $e->getMessage());
            return $healthData;
        }
    }
}
