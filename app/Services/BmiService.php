<?php

namespace App\Services;

use App\Models\BmiRecord;
use App\Traits\ConsumesExternalService;

class BmiService
{
    use ConsumesExternalService;

    private string $baseUrl;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('services.bmi.base_url');

        $this->headers = [
            'X-RapidAPI-Key' => config('services.bmi.api_key'),
            'X-RapidAPI-Host' => config('services.bmi.host'),
        ];
    }

    public function calculate(int $userId, float $weight, float $height): array
    {
        // API expects height in METERS — user sends cm, so convert
        $heightInMeters = round($height / 100, 2);

        $data = $this->performRequest(
            'GET',
            $this->baseUrl,
            [
                'weight' => $weight,
                'height' => $heightInMeters,
            ],
            [],
            $this->headers
        );

        // fallback if API doesn't return expected fields
        $bmiValue = $data['bmi'] ?? round($weight / ($heightInMeters ** 2), 2);
        $category = $data['health'] ?? $this->classifyBmi($bmiValue);

        //save to DB
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