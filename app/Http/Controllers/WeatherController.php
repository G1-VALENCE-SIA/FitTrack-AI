<?php

namespace App\Http\Controllers;

use App\Services\WeatherService;

class WeatherController extends Controller
{
    public function __construct(protected WeatherService $weatherService)
    {
    }

    public function getWeather(string $city)
    {
        $data = $this->weatherService->getWeather(auth()->id(), $city);
        return $this->successResponse($data, 'Weather retrieved');
    }
}