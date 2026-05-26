<?php

namespace App\Http\Controllers;

use App\Services\WorkoutService;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function __construct(protected WorkoutService $workoutService)
    {
    }

    public function index()
    {
        $workouts = $this->workoutService->getAllWorkouts(auth()->id());
        return $this->successResponse($workouts, 'Workouts retrieved');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_weight' => 'nullable|numeric|min:0',
            'exercises' => 'nullable|array',
            'exercises.*.exercise_id' => 'required|integer|exists:exercises,id',
            'exercises.*.sets' => 'nullable|integer|min:1',
            'exercises.*.reps' => 'nullable|integer|min:1',
        ]);

        $workout = $this->workoutService->create(
            auth()->id(),
            $request->title,
            $request->description,
            $request->exercises ?? [],
            $request->default_weight
        );
        return $this->successResponse($workout, 'Workout created', 201);
    }

    public function show(int $id)
    {
        $data = $this->workoutService->getWorkout($id);
        return $this->successResponse($data, 'Workout retrieved');
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'default_weight' => 'sometimes|nullable|numeric|min:0',
            'exercises' => 'sometimes|nullable|array',
            'exercises.*.exercise_id' => 'required|integer|exists:exercises,id',
            'exercises.*.sets' => 'nullable|integer|min:1',
            'exercises.*.reps' => 'nullable|integer|min:1',
        ]);

        $workout = $this->workoutService->update(auth()->id(), $id, $request->all());
        return $this->successResponse($workout, 'Workout updated successfully');
    }

    public function destroy(int $id)
    {
        $this->workoutService->delete(auth()->id(), $id);
        return $this->successResponse(null, 'Workout deleted successfully');
    }

    public function logSession(Request $request, int $workoutId)
    {
        $request->validate([
            'duration' => 'required|integer|min:1',
        ]);

        $log = $this->workoutService->logSession(
            auth()->id(),
            $workoutId,
            now()->toDateString(),
            $request->duration
        );
        return $this->successResponse($log, 'Session logged', 201);
    }

    public function logs()
    {
        $logs = $this->workoutService->getLogs(auth()->id());
        return $this->successResponse(['user_id' => auth()->id(), 'logs' => $logs], 'Logs retrieved');
    }
}