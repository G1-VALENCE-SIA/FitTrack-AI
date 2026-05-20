<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\WeatherLog;

class WeatherService
{
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
        $response = Http::timeout(8)->withHeaders([
            'X-RapidAPI-Key' => env('WEATHER_API_KEY'),
            'X-RapidAPI-Host' => env('WEATHER_API_HOST'),
        ])->get(env('WEATHER_BASE_URL'), [
                    'city' => $city,
                    'lang' => 'EN',
                    'units' => 'metric',
                ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch weather data: ' . $response->body());
        }

        $data = $response->json();

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