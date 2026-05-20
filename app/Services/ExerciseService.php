<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Exercise;

class ExerciseService
{
    protected array $muscleMap = [
        'chest' => 'pectorals',
        'arms' => 'biceps',
        'shoulders' => 'delts',
        'abs' => 'abdominals',
        'legs' => 'quadriceps',
        'back' => 'lats',
        'glutes' => 'glutes',
    ];

    public function searchByMuscle(string $muscle): array
    {
        $target = $this->muscleMap[strtolower($muscle)] ?? strtolower($muscle);

        $response = Http::withHeaders([
            'X-RapidAPI-Key' => env('EXERCISEDB_API_KEY'),
            'X-RapidAPI-Host' => env('EXERCISEDB_API_HOST'),
        ])->get(env('EXERCISEDB_BASE_URL') . '/exercises/target/' . $target);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch from ExerciseDB: ' . $response->body());
        }

        $results = $response->json();
        $this->saveExercises($results, $target);

        // Return DB records instead of raw API response so local id is visible
        $saved = Exercise::where('muscle_group', $target)->get();

        return [
            'mapped_muscle' => $target,
            'saved_count' => $saved->count(),
            'results' => $saved,
        ];
    }

    private function saveExercises(array $exercises, string $target): void
    {
        foreach ($exercises as $exercise) {
            Exercise::updateOrCreate(
                ['api_exercise_id' => $exercise['id'] ?? null],
                [
                    'name' => $exercise['name'] ?? null,
                    'body_part' => $exercise['bodyPart'] ?? null,
                    'muscle_group' => $target,
                    'equipment' => $exercise['equipment'] ?? null,
                    'instructions' => is_array($exercise['instructions'] ?? null)
                        ? implode("\n", $exercise['instructions'])
                        : null,
                ]
            );
        }
    }
}