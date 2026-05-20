<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\BmiRecord;

class BmiService
{
    public function calculate(int $userId, float $weight, float $height): array
    {
        // API expects height in METERS — user sends cm, so convert
        $heightInMeters = round($height / 100, 2);

        $response = Http::withHeaders([
            'X-RapidAPI-Key' => env('BMI_API_KEY'),
            'X-RapidAPI-Host' => env('BMI_API_HOST'),
        ])->get(env('BMI_BASE_URL'), [
                    'weight' => $weight,        // kg
                    'height' => $heightInMeters // meters e.g. 1.75
                ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch from BMI API: ' . $response->body());
        }

        $data = $response->json();

        // body-mass-index-bmi-calculator returns: bmi, health, healthy_bmi_range
        $bmiValue = $data['bmi'] ?? round($weight / ($heightInMeters ** 2), 2);
        $category = $data['health'] ?? $this->classifyBmi($bmiValue);

        $record = BmiRecord::create([
            'user_id' => $userId,
            'bmi_value' => $bmiValue,
            'category' => $category,
        ]);

        return [
            'bmi_value' => $bmiValue,
            'category' => $category,
            'healthy_bmi_range' => $data['healthy_bmi_range'] ?? '18.5 - 25',
            'record' => $record,
        ];
    }

    public function getHistory(int $userId): array
    {
        $records = BmiRecord::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $history = $records->toArray();

        // Progress: compare the two most recent records
        $progress = null;
        if ($records->count() >= 2) {
            $latest = $records->get(0);
            $previous = $records->get(1);
            $diff = round($latest->bmi_value - $previous->bmi_value, 2);

            $progress = [
                'previous_bmi' => $previous->bmi_value,
                'current_bmi' => $latest->bmi_value,
                'change' => $diff,
                'trend' => $diff < 0 ? 'improving' : ($diff > 0 ? 'worsening' : 'stable'),
                'previous_date' => $previous->created_at,
                'current_date' => $latest->created_at,
            ];
        }

        return [
            'history' => $history,
            'progress' => $progress,
        ];
    }

    private function classifyBmi(float $bmi): string
    {
        return match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25.0 => 'Normal',
            $bmi < 30.0 => 'Overweight',
            default => 'Obese',
        };
    }
}