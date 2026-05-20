<?php

namespace App\Http\Controllers;

use App\Services\WorkoutService;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function __construct(protected WorkoutService $workoutService)
    {
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exercises' => 'nullable|array',
            'exercises.*.exercise_id' => 'required|integer|exists:exercises,id',
            'exercises.*.sets' => 'nullable|integer|min:1',
            'exercises.*.reps' => 'nullable|integer|min:1',
            'exercises.*.weight' => 'nullable|numeric|min:0',
        ]);

        try {
            $workout = $this->workoutService->create(
                auth()->id(),
                $request->title,
                $request->description,
                $request->exercises ?? []
            );
            return response()->json(['message' => 'Workout created', 'workout' => $workout], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id)
    {
        $data = $this->workoutService->getWorkout($id);
        return response()->json($data);
    }

    public function logSession(Request $request, int $workoutId)
    {
        $request->validate([
            'date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'calories_burned' => 'nullable|integer|min:0',
        ]);

        try {
            $log = $this->workoutService->logSession(
                auth()->id(),
                $workoutId,
                $request->date,
                $request->duration,
                $request->calories_burned
            );
            return response()->json(['message' => 'Session logged', 'log' => $log], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function logs()
    {
        try {
            $logs = $this->workoutService->getLogs(auth()->id());
            return response()->json(['user_id' => auth()->id(), 'logs' => $logs]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}