<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ExerciseService;
use Illuminate\Http\Request;
use App\Models\Exercise;

class ExerciseController extends Controller
{
    public function __construct(protected ExerciseService $exerciseService)
    {
    }

    public function search(Request $request)
    {
        if (!$request->query('muscle')) {
            return $this->errorResponse('Muscle parameter is required. Example: ?muscle=chest', 400);
        }

        $data = $this->exerciseService->searchByMuscle($request->query('muscle'));
        return $this->successResponse($data, 'Exercises retrieved');
    }

    public function show(int $id)
    {
        $exercise = $this->exerciseService->getById($id);
        return $this->successResponse($exercise, 'Exercise retrieved');
    }
}