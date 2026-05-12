<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ExerciseService;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function __construct(protected ExerciseService $exerciseService) {}

    public function search(Request $request)
    {
        if (!$request->query('muscle')) {
            return response()->json([
                'message' => 'Muscle parameter is required. Example: ?muscle=chest'
            ], 400);
        }

        try {
            $data = $this->exerciseService->searchByMuscle($request->query('muscle'));
            return response()->json(array_merge(['input_muscle' => $request->query('muscle')], $data));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}