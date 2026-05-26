<?php

namespace App\Http\Controllers;

use App\Services\NutritionService;
use Illuminate\Http\Request;

class NutritionController extends Controller
{
    public function __construct(protected NutritionService $nutritionService)
    {
    }

    public function search(Request $request)
    {
        if (!$request->query('food')) {
            return $this->errorResponse('Food parameter is required. Example: ?food=banana', 400);
        }

        $data = $this->nutritionService->searchFood($request->query('food'));
        return $this->successResponse($data, 'Nutrition data retrieved');
    }

    public function suggest(Request $request)
    {
        $goal = $request->query('goal', 'maintain');

        if (!in_array($goal, ['lose', 'gain', 'maintain'])) {
            return $this->errorResponse('Goal must be one of: lose, gain, maintain', 400);
        }

        $data = $this->nutritionService->suggest($goal);
        return $this->successResponse($data, 'Nutrition suggestions retrieved');
    }
}