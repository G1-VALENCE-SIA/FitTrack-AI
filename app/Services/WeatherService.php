<?php

namespace App\Services;

use App\Models\WeatherLog;
use App\Traits\ConsumesExternalService;

class WeatherService
{
    use ConsumesExternalService;

    private string $baseUrl;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('services.weather.base_url');
        $this->headers = [
            'X-RapidAPI-Key' => config('services.weather.api_key'),
            'X-RapidAPI-Host' => config('services.weather.host'),
        ];
    }

    public function getWeather(int $userId, string $city): array
    {
        [$temperature, $condition, $latitude, $longitude] = $this->fetchWeatherByCity($city);

        WeatherLog::create([
            'user_id' => $userId,
            'city' => $city,
            'temperature' => $temperature,
            'weather_condition' => $condition,
        ]);

        return [
            'city' => $city,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'temperature' => $temperature . '°C',
            'condition' => $condition,
            'suggestion' => $this->workoutSuggestion($condition),
        ];
    }

    private function fetchWeatherByCity(string $city): array
    {
        $data = $this->performRequest(
            'GET',
            $this->baseUrl,
            [
                'city' => $city,
                'lang' => 'EN',
                'units' => 'metric',
            ],
            [],
            $this->headers
        );

        $temp = $data['main']['temp'] ?? null;
        $condition = $data['weather'][0]['main'] ?? null;
        $lat = $data['coord']['lat'] ?? null;
        $lon = $data['coord']['lon'] ?? null;

        if ($temp === null || $condition === null) {
            throw new \Exception("Unexpected response from weather API for '{$city}'.");
        }

        $tempCelsius = round(($temp - 32) * 5 / 9, 2);

        return [$tempCelsius, $condition, $lat, $lon];
    }
    private function workoutSuggestion(?string $condition): string
    {
        $indoorConditions = ['Rain', 'Drizzle', 'Thunderstorm', 'Snow', 'Extreme'];

        if (!$condition)
            return 'Check conditions before heading out.';

        return in_array($condition, $indoorConditions)
            ? 'Bad weather detected — recommended: Indoor HIIT or gym workout.'
            : 'Good weather — recommended: Outdoor run, cycling, or sports.';
    }
}