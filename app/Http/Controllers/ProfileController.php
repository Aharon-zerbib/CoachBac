<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Créer ou mettre à jour le profil utilisateur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'height' => 'required|numeric|min:50|max:250',
            'initial_weight' => 'required|numeric|min:20|max:300',
            'activity_level' => 'required|in:sedentary,lightly_active,moderately_active,very_active,extra_active',
            'goal' => 'required|in:lose_weight,maintain_weight,gain_muscle',
            'daily_distance_goal' => 'required|numeric|min:0',
            'preferred_activities' => 'required|array|min:1',
        ]);

        $profile = $request->user()->profile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json($profile, 201);
    }
}
