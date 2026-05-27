<?php

namespace App\Services;

use App\Models\Food;
use App\Traits\ConsumesExternalService;

class NutritionService
{
    use ConsumesExternalService;

    private string $baseUrl;
    private array $headers;
    public function __construct()
    {
        $this->baseUrl = config('services.edamam.base_url');
        $this->headers = [
            'X-RapidAPI-Key'  => config('services.edamam.api_key'),
            'X-RapidAPI-Host' => config('services.edamam.api_host'),
        ];
    }

    public function searchFood(string $food): array
    {
        $data = $this->performRequest(
            'GET',
            $this->baseUrl,
            [
                'nutrition-type' => 'cooking',
                'ingr' => '100g ' . $food,
            ],
            [],
            $this->headers
        );
        
        $nutrients = $data['totalNutrients'] ?? [];

        $record = Food::updateOrCreate(
            ['name' => strtolower($food)],
            [
                'api_food_id' => null,
                'name'        => $food,
                'calories'    => (int) ($data['calories'] ?? 0),
                'protein'     => round($nutrients['PROCNT']['quantity'] ?? 0, 2),
                'carbs'       => round($nutrients['CHOCDF']['quantity'] ?? 0, 2),
                'fats'        => round($nutrients['FAT']['quantity'] ?? 0, 2),
            ]
        );

        return [
            'query'      => $food,
            'saved_count' => 1,
            'results'    => [$record],
        ];
    }

    /*
     * Rule-based meal suggestions using foods already stored in the DB.
     * goal: 'lose' | 'gain' | 'maintain'
    */
    public function suggest(string $goal): array
    {
        $foods = Food::all();

        if ($foods->isEmpty()) {
            return [
                'goal'        => $goal,
                'suggestions' => [],
                'note'        => 'No foods in the database yet. Search for foods first using /api/nutrition/search.',
            ];
        }

        switch (strtolower($goal)) {
            case 'lose':
                // Low calorie, high protein, low fat
                $suggestions = $foods->filter(fn($f) =>
                    $f->calories <= 150 && $f->protein >= 5
                )->sortBy('calories');

                $plan = [
                    'strategy'   => 'Calorie deficit — aim for 300–500 kcal below your TDEE daily.',
                    'principles' => ['High protein to preserve muscle', 'Low fat, moderate carbs', 'Avoid processed sugars'],
                    'target_macros' => ['protein' => '30–35%', 'carbs' => '40–45%', 'fats' => '20–25%'],
                ];
                break;

            case 'gain':
                // High calorie, high protein
                $suggestions = $foods->filter(fn($f) =>
                    $f->calories >= 100 && $f->protein >= 8
                )->sortByDesc('calories');

                $plan = [
                    'strategy'   => 'Calorie surplus — aim for 300–500 kcal above your TDEE daily.',
                    'principles' => ['High protein for muscle synthesis', 'High carbs for energy', 'Healthy fats welcome'],
                    'target_macros' => ['protein' => '25–30%', 'carbs' => '45–55%', 'fats' => '20–25%'],
                ];
                break;

            case 'maintain':
                $suggestions = $foods->sortByDesc('protein');

                $plan = [
                    'strategy'   => 'Balanced eating — match your TDEE daily.',
                    'principles' => ['Balanced macros', 'Variety of whole foods', 'Regular meal timing'],
                    'target_macros' => ['protein' => '20–25%', 'carbs' => '45–55%', 'fats' => '25–30%'],
                ];
        }

        return [
            'goal'        => $goal,
            'plan'        => $plan,
            'suggestions' => $suggestions->values()->take(10),
        ];
    }
}