<?php

namespace App\Http\Controllers;

use App\Services\BmiService;
use Illuminate\Http\Request;

class BmiController extends Controller
{
    public function __construct(protected BmiService $bmiService)
    {
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:1',
            'height' => 'required|numeric|min:1',
        ]);

        $data = $this->bmiService->calculate(auth()->id(), $request->weight, $request->height);
        return $this->successResponse($data, 'BMI calculated successfully');
    }

    public function history()
    {
        $data = $this->bmiService->getHistory(auth()->id());
        return $this->successResponse($data, 'BMI history retrieved');
    }
}