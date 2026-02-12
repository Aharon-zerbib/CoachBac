<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::updateOrCreate(
    ['email' => 'test@example.com'],
    ['name' => 'Tester', 'password' => Hash::make('password')]
);

$user->profile()->updateOrCreate(
    ['user_id' => $user->id],
    [
        'height' => 170,
        'initial_weight' => 70,
        'birth_date' => '1995-01-01',
        'gender' => 'male',
        'activity_level' => 'moderately_active',
        'goal' => 'maintain_weight',
        'daily_distance_goal' => 5,
        'preferred_activities' => ['running']
    ]
);

echo "User test@example.com / password created successfully.
";
