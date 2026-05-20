<?php

namespace App\Services;

use App\Models\BmiRecord;
use App\Models\WeatherLog;
use App\Models\Log as WorkoutLog;
use App\Models\Recommendation;

class RecommendationService
{
    public function generate(int $userId, string $goal): array
    {
        $suggestions = [];

        // Rule 1: BMI-based recommendation
        $latestBmi = BmiRecord::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestBmi) {
            $suggestions[] = match (true) {
                $latestBmi->bmi_value >= 30 =>
                ['suggestion' => 'Your BMI indicates obesity. Recommend: Daily cardio (30 min) + calorie deficit diet. Focus on low-impact exercises like swimming or walking.', 'source_api' => 'bmi'],
                $latestBmi->bmi_value >= 25 =>
                ['suggestion' => 'Your BMI indicates overweight. Recommend: 3-4x per week cardio (HIIT or jogging) + reduce carb intake.', 'source_api' => 'bmi'],
                $latestBmi->bmi_value < 18.5 =>
                ['suggestion' => 'Your BMI indicates underweight. Recommend: Strength training 3x per week + high-protein, calorie-surplus meals.', 'source_api' => 'bmi'],
                default =>
                ['suggestion' => 'Your BMI is in the normal range. Recommend: Maintain with balanced workouts — mix of cardio and strength training.', 'source_api' => 'bmi'],
            };
        }

        // Rule 2: Weather-based recommendation
        $latestWeather = WeatherLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestWeather) {
            $indoorConditions = ['Rain', 'Drizzle', 'Thunderstorm', 'Snow'];
            if (in_array($latestWeather->weather_condition, $indoorConditions)) {
                $suggestions[] = [
                    'suggestion' => "Weather in {$latestWeather->city} is {$latestWeather->weather_condition}. Recommend: Indoor HIIT, yoga, or home strength training today.",
                    'source_api' => 'weather'
                ];
            } else {
                $suggestions[] = [
                    'suggestion' => "Weather in {$latestWeather->city} is {$latestWeather->weather_condition} at {$latestWeather->temperature}°C. Great day for outdoor run or cycling!",
                    'source_api' => 'weather'
                ];
            }
        }

        // Rule 3: Inactivity reminder (no workout log in 3 days)
        $lastLog = WorkoutLog::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->first();

        if (!$lastLog || now()->diffInDays($lastLog->date) >= 3) {
            $suggestions[] = [
                'suggestion' => 'You have not logged a workout in 3+ days. Time to get moving! Even a 20-minute walk counts.',
                'source_api' => 'logs'
            ];
        }

        // Rule 4: Goal-based recommendation
        $goalSuggestion = match ($goal) {
            'muscle_gain' => 'Your goal is muscle gain. Recommend: Strength training 4x per week (compound lifts — squat, deadlift, bench, row) + high-protein diet (1.6–2.2g protein per kg bodyweight).',
            'lose' => 'Your goal is weight loss. Recommend: 3–4x cardio per week + calorie deficit of 300–500 kcal/day. Track your meals using the nutrition tracker.',
            'gain' => 'Your goal is weight gain. Recommend: Calorie surplus of 300–500 kcal/day with strength training 3x per week.',
            'maintain' => 'Your goal is maintenance. Recommend: Balanced mix of cardio and strength training 3x per week with consistent calorie intake.',
            default => 'No specific goal recommendation available.',
        };

        $suggestions[] = ['suggestion' => $goalSuggestion, 'source_api' => 'goal'];

        // Save using updateOrCreate to avoid duplicate entries
        $saved = [];
        foreach ($suggestions as $item) {
            $saved[] = Recommendation::updateOrCreate(
                [
                    'user_id' => $userId,
                    'source_api' => $item['source_api'],
                ],
                [
                    'suggestion' => $item['suggestion'],
                ]
            );
        }

        return [
            'user_id' => $userId,
            'count' => count($saved),
            'recommendations' => $saved,
        ];
    }
}