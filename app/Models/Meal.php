<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'description',
        'calories',
        'protein',
        'carbs',
        'fat',
        'ai_analysis',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
