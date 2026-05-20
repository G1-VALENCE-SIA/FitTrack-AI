<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Quote;

class QuoteService
{
    public function getDailyQuote(): array
    {
        $today = now()->toDateString();
        $existing = Quote::where('date', $today)->first();

        if ($existing) {
            return ['quote' => $existing, 'source' => 'cache'];
        }

        // API Ninjas
        $response = Http::withHeaders([
            'X-Api-Key' => env('QUOTES_API_KEY'),
        ])->get(env('QUOTES_BASE_URL'), [
                    'category' => 'fitness',
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch quote: ' . $response->body());
        }

        $data = $response->json();
        $quoteData = $data[0] ?? [];

        $quote = Quote::create([
            'quote' => $quoteData['quote'] ?? 'Keep pushing!',
            'author' => $quoteData['author'] ?? 'Unknown',
            'date' => $today,
        ]);

        return ['quote' => $quote, 'source' => 'api'];
    }
}