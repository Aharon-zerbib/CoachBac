<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use App\Models\User;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;

class GeminiService
{
    public function analyzeMeal(User $user, string $base64Data, string $mimeType, ?string $description)
    {
        try {
            $model = config('gemini.model', 'gemini-2.5-flash');
            $prompt = "Tu es un nutritionniste expert. Analyse ce plat pour Ronron ({$user->profile->initial_weight}kg). Note: {$description}. Estime les portions. Réponds UNIQUEMENT en JSON: {\"calories\":0, \"protein\":0, \"carbs\":0, \"fat\":0, \"analysis\":\"...\", \"advice\":\"...\"}";

            $result = Gemini::generativeModel($model)->generateContent([
                $prompt,
                new Blob(mimeType: MimeType::IMAGE_JPEG, data: $base64Data)
            ]);

            $text = $result->text();
            if (preg_match('/\{.*\}/s', $text, $matches)) {
                return json_decode($matches[0], true);
            }
            return json_decode($text, true);
        } catch (\Throwable $e) {
            \Log::error("IA ERROR: " . $e->getMessage());
            return null;
        }
    }

    public function generateDailyAnalysis(User $user)
    {
        $profile = $user->profile;
        if (!$profile) return "Profil incomplet.";
        try {
            $model = config('gemini.model', 'gemini-2.5-flash');
            $prompt = "Fais un feedback rapide et motivant à {$user->name} sur sa journée sportive.";
            $result = Gemini::generativeModel($model)->generateContent($prompt);
            return $result->text();
        } catch (\Throwable $e) {
            return "Continue tes efforts !";
        }
    }
}
