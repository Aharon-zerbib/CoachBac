<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable(); // Chemin de la photo
            $table->text('description')->nullable(); // Description utilisateur
            $table->integer('calories')->nullable(); // Estimation IA
            $table->integer('protein')->nullable(); // en grammes
            $table->integer('carbs')->nullable(); // en grammes
            $table->integer('fat')->nullable(); // en grammes
            $table->text('ai_analysis')->nullable(); // Commentaire complet de l'IA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
