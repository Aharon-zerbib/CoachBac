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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->decimal('height', 5, 2); // en cm
            $table->decimal('initial_weight', 5, 2); // en kg
            $table->enum('activity_level', [
                'sedentary',
                'lightly_active',
                'moderately_active',
                'very_active',
                'extra_active'
            ]);
            $table->enum('goal', [
                'lose_weight',
                'maintain_weight',
                'gain_muscle'
            ]);
            $table->integer('daily_calorie_target')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
