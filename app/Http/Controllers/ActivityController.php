<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Services\GeminiService;
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
    /**
     * Statistiques globales de l'utilisateur.
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $activities = $user->activities()->whereDate('created_at', now())->get();

        $totalDistance = $activities->sum('distance');
        $totalDuration = $activities->sum('duration'); // en secondes
        $totalSteps = $activities->sum('steps');
        
        // Calcul simple des calories : ~60 kcal par km (moyenne)
        $calories = round($totalDistance * 60);

        return response()->json([
            'today' => [
                'distance' => $totalDistance,
                'duration' => $totalDuration,
                'steps' => $totalSteps,
                'calories' => $calories,
            ],
            'goals' => [
                'distance' => $user->profile->daily_distance_goal ?? 5,
                'steps' => 10000, // Objectif standard
            ]
        ]);
    }
}
