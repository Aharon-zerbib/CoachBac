<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'birth_date',
        'gender',
        'height',
        'initial_weight',
        'activity_level',
        'goal',
        'daily_calorie_target',
        'daily_distance_goal',
        'preferred_activities',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'height' => 'decimal:2',
        'initial_weight' => 'decimal:2',
        'daily_distance_goal' => 'decimal:2',
        'preferred_activities' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
