<?php

namespace App\Services;

use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\Log as WorkoutLog;
use App\Models\Exercise;
use App\Models\User;

class WorkoutService
{
    public function create(int $userId, string $title, ?string $description, array $exercises = []): array
    {
        $workout = Workout::create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
        ]);

        foreach ($exercises as $item) {
            WorkoutExercise::create([
                'workout_id' => $workout->id,
                'exercise_id' => $item['exercise_id'],
                'sets' => $item['sets'] ?? null,
                'reps' => $item['reps'] ?? null,
                'weight' => $item['weight'] ?? null,
            ]);
        }

        return $this->getWorkout($workout->id);
    }

    public function getWorkout(int $workoutId): array
    {
        $workout = Workout::with(['exercises'])->find($workoutId);

        if (!$workout) {
            throw new \Exception("Workout not found.");
        }

        $data = $workout->toArray();
        $data['exercise_count'] = $workout->exercises->count();
        $data['note'] = $workout->exercises->isEmpty()
            ? 'No exercises attached yet. Create a new workout with exercises included.'
            : null;

        return $data;
    }

    public function logSession(
        int $userId,
        int $workoutId,
        string $date,
        int $duration,
        ?int $caloriesBurned = null
    ): WorkoutLog {
        if (!Workout::find($workoutId)) {
            throw new \Exception("Workout ID {$workoutId} not found.");
        }

        if ($caloriesBurned === null) {
            $user = User::find($userId);
            $weightKg = $user->weight ?? 70;
            $caloriesBurned = (int) round(5 * 3.5 * $weightKg / 200 * $duration);
        }

        return WorkoutLog::create([
            'user_id' => $userId,
            'workout_id' => $workoutId,
            'date' => $date,
            'duration' => $duration,
            'calories_burned' => $caloriesBurned,
        ]);
    }

    public function getLogs(int $userId): array
    {
        return WorkoutLog::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }
}