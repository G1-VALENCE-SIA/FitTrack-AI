<?php

namespace App\Http\Controllers;

use App\Services\NutritionService;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    public function __construct(protected NutritionService $nutritionService) {}

    public function search(Request $request)
    {
        if (!$request->query('food')) {
            return response()->json([
                'message' => 'Food parameter is required. Example: ?food=banana'
            ], 400);
        }

        try {
            $data = $this->nutritionService->searchFood($request->query('food'));
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function suggest(Request $request)
    {
        $goal = $request->query('goal', 'maintain'); // lose | gain | maintain

        if (!in_array($goal, ['lose', 'gain', 'maintain'])) {
            return response()->json([
                'message' => 'Goal must be one of: lose, gain, maintain'
            ], 400);
        }

        try {
            $data = $this->nutritionService->suggest($goal);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}