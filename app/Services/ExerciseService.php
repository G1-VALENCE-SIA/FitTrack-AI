<?php

namespace App\Services;

use App\Models\Exercise;
use App\Traits\ConsumesExternalService;


class ExerciseService
{
    use ConsumesExternalService;

    private string $baseUrl;
    private array $headers;

    // Map common muscle group names to API-specific target muscle names
    protected array $muscleMap = [
        'chest' => 'pectorals',
        'arms' => 'biceps',
        'shoulders' => 'delts',
        'abs' => 'abdominals',
        'legs' => 'quadriceps',
        'back' => 'lats',
        'glutes' => 'glutes',
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.exercise_db.base_url');

        $this->headers = [
            'X-RapidAPI-Key' => config('services.exercise_db.api_key'),
            'X-RapidAPI-Host' => config('services.exercise_db.host'),
        ];
    }

    public function searchByMuscle(string $muscle): array
    {
        $target = $this->muscleMap[strtolower($muscle)] ?? strtolower($muscle);

        $results = $this->performRequest(
            'GET',
            $this->baseUrl . '/exercises/target/' . $target,
            [],
            [],
            $this->headers
        );

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

    public function getById(int $id): Exercise
    {
        $exercise = Exercise::findOrFail($id);
        return $exercise;
    }
}