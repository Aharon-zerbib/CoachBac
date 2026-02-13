<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleFitService;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/fitness.activity.read',
                'https://www.googleapis.com/auth/fitness.location.read',
                'https://www.googleapis.com/auth/fitness.body.read',
                'https://www.googleapis.com/auth/fitness.nutrition.read',
                'https://www.googleapis.com/auth/fitness.sleep.read',
                'https://www.googleapis.com/auth/fitness.heart_rate.read',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'select_account consent'
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $updateData = [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'google_token' => $googleUser->token,
            ];

            if ($googleUser->refreshToken) {
                $updateData['google_refresh_token'] = $googleUser->refreshToken;
            }
            
            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),
            ], $updateData);

            // Créer un token Sanctum pour le frontend
            $token = $user->createToken('auth_token')->plainTextToken;

            // On redirige vers une route temporaire qui va stocker le token dans le localStorage
            // ou on le passe via un paramètre de requête (moins sécure mais plus simple pour ce cas)
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/dashboard?token=' . $token);

        } catch (\Exception $e) {
            \Log::error("Google Auth Error: " . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/login?error=auth_failed');
        }
    }

    public function disconnect()
    {
        $user = auth()->user();
        $user->update([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
        ]);

        return response()->json(['message' => 'Compte Google déconnecté avec succès']);
    }
}
