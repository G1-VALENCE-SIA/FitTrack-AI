<?php

namespace App\Http\Controllers;

use App\Services\BmiService;
use Illuminate\Http\Request;

class BmiController extends Controller
{
    public function __construct(protected BmiService $bmiService) {}

    public function calculate(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:1', //Kg
            'height' => 'required|numeric|min:1', //Cm
        ]);

        try {
            $data = $this->bmiService->calculate(
                auth()->id(),
                $request->weight,
                $request->height
            );
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function history()
    {
        try {
            $data = $this->bmiService->getHistory(auth()->id());
            return response()->json(array_merge(['user_id' => auth()->id()], $data));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}