<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __construct(protected RecommendationService $recommendationService)
    {
    }

    public function generate(Request $request)
    {
        $request->validate([
            'goal' => 'required|in:lose,gain,maintain,muscle_gain',
        ]);

        try {
            $data = $this->recommendationService->generate(auth()->id(), $request->goal);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}