<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\GeminiService;
use App\Services\GoogleFitService;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Obtenir une analyse IA de la journée.
     */
    public function dailyAnalysis(Request $request, GeminiService $geminiService)
    {
        $analysis = $geminiService->generateDailyAnalysis($request->user());

        return response()->json([
            'analysis' => $analysis
        ]);
    }

    /**
     * Enregistrer une nouvelle activité.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:course,marche',
            'distance' => 'required|numeric',
            'duration' => 'required|integer',
            'steps' => 'nullable|integer',
            'path_data' => 'nullable|array',
        ]);

        $activity = $request->user()->activities()->create($validated);

        return response()->json($activity, 201);
    }

    /**
     * Liste des activités de l'utilisateur.
     */
    public function index(Request $request)
    {
        $query = $request->user()->activities();
        
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return response()->json($query->latest()->get());
    }

    public function stats(Request $request, GoogleFitService $googleFitService)
    {
        $user = $request->user();
        $date = $request->query('date', now()->format('Y-m-d'));
        $googleData = null;

        if ($user->google_token) {
            $googleData = $googleFitService->getSyncData($user, $date);
        }

        // Si Google Fit ne renvoie rien, on initialise une structure vide propre
        if (!$googleData) {
            $googleData = [
                'activity' => ['steps' => 0, 'calories' => 0, 'distance' => 0, 'active_minutes' => 0],
                'body' => ['weight' => null, 'height' => null],
                'vitals' => ['heart_rate' => null, 'blood_pressure' => '12/8', 'temp' => 36.6],
                'nutrition' => ['calories' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0],
                'sleep' => ['total_hours' => null],
            ];
        }

        // Sécurité pour le poids et la taille depuis le profil
        if (empty($googleData['body']['weight']) || empty($googleData['body']['height'])) {
            $profile = $user->profile;
            if ($profile) {
                $googleData['body']['weight'] = $googleData['body']['weight'] ?? $profile->initial_weight;
                $googleData['body']['height'] = $googleData['body']['height'] ?? $profile->height;
            }
        }

        return response()->json([
            'google_sync' => !empty($user->google_token),
            'health' => $googleData,
            'goals' => [
                'steps' => 10000,
                'calories' => 2500,
                'distance' => optional($user->profile)->daily_distance_goal ?? 5,
            ]
        ]);
    }
}
