<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;

class WeatherController extends Controller
{
    public function __construct(protected WeatherService $weatherService) {}

    public function getWeather(string $city)
    {
        try {
            $data = $this->weatherService->getWeather(auth()->id(), $city);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}