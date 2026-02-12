<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MealController extends Controller
{
    public function store(Request $request, GeminiService $geminiService)
    {
        // --- BOOSTER LE SERVEUR ---
        @ini_set('memory_limit', '512M');
        @set_time_limit(120); // 2 minutes max
        @ini_set('display_errors', '0');
        error_reporting(0);

        try {
            $request->validate([
                'image_data' => 'required|string',
                'description' => 'nullable|string',
            ]);

            // Extraction robuste du base64 (on accepte tout type d'image)
            if (preg_match('/^data:(image\/\w+);base64,/', $request->image_data, $matches)) {
                $mime = $matches[1];
                $base64 = substr($request->image_data, strpos($request->image_data, ',') + 1);
            } else {
                return response()->json(['message' => 'Format image invalide'], 400);
            }

            // APPEL IA (on passe le relais au service blindé)
            $analysis = $geminiService->analyzeMeal($request->user(), $base64, $mime, $request->description);

            if (!$analysis) {
                return response()->json(['message' => 'L\'IA a mis trop de temps à répondre.'], 504);
            }

            // SAUVEGARDE (L'image est jetée ici, on ne garde que le texte)
            $meal = $request->user()->meals()->create([
                'description' => $request->description,
                'calories' => $analysis['calories'] ?? 0,
                'protein' => $analysis['protein'] ?? 0,
                'carbs' => $analysis['carbs'] ?? 0,
                'fat' => $analysis['fat'] ?? 0,
                'ai_analysis' => ($analysis['analysis'] ?? '') . "\n\nConseil : " . ($analysis['advice'] ?? ''),
            ]);

            return response()->json($meal, 201);

        } catch (\Throwable $e) {
            Log::error("CRASH NUTRITION : " . $e->getMessage());
            return response()->json(['message' => 'Erreur technique serveur'], 500);
        }
    }

    public function index(Request $request)
    {
        return response()->json($request->user()->meals()->latest()->get());
    }
}
