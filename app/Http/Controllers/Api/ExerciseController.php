<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ExerciseController extends Controller
{
    public function search(Request $request)
    {
        $muscle = $request->query("muscle");

        if (!$muscle) {
            return response()->json([
                "message" => "Muscle parameter is required. Example: ?muscle=chest"
            ], 400);
        }

        $muscleMap = [
            'chest' => 'pectorals',
            'arms' => 'biceps',
            'triceps' => 'triceps',
            'abs' => 'abdominals',
            'legs' => 'quadriceps',
            'back' => 'lats',
            'glutes' => 'glutes',
        ];

        $target = $muscleMap[strtolower($muscle)] ?? strtolower($muscle);

        $url = env("EXERCISEDB_BASE_URL") . "/exercises/target/" . $target;

        $response = Http::withHeaders([
            "X-RapidAPI-Key" => env("EXERCISEDB_API_KEY"),
            "X-RapidAPI-Host" => env("EXERCISEDB_API_HOST"),
        ])->get($url);

        if ($response->failed()) {
            return response()->json([
                "message" => "Failed to fetch data from ExerciseDB API",
                "error" => $response->body()
            ], 500);
        }

        $results = $response->json();

        // ✅ SAVE TO DATABASE HERE
        foreach ($results as $exercise) {
            \App\Models\Exercise::updateOrCreate(
                ['api_exercise_id' => $exercise['id'] ?? null],
                [
                    'name' => $exercise['name'] ?? null,
                    'body_part' => $exercise['bodyPart'] ?? null,
                    'muscle_group' => $target,
                    'equipment' => $exercise['equipment'] ?? null,
                    'instructions' => is_array($exercise['instructions'] ?? null)
                        ? implode("\n", $exercise['instructions'])
                        : null
                ]
            );
        }

        return response()->json([
            "input_muscle" => $muscle,
            "mapped_muscle" => $target,
            "saved_count" => count($results),
            "results" => $results
        ]);
    }
}