<?php

namespace App\Services;

use App\Models\Quote;
use App\Traits\ConsumesExternalService;

class QuoteService
{
    use ConsumesExternalService;

    private string $baseUrl;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = config('services.quotes.base_url');
        $this->headers = [
            'X-Api-Key' => config('services.quotes.api_key'),
        ];
    }

    public function getDailyQuote(): array
    {
        $today = now()->toDateString();

        // Check if quote already exists for today
        $existing = Quote::where('date', $today)->first();

        if ($existing) {
            return [
                'quote' => $existing,
                'source' => 'cache'
            ];
        }

        //Fetch Data from API Ninjas
        $data = $this->performRequest(
            'GET',
            $this->baseUrl . '/v1/quotes',
            [],
            [],
            $this->headers
        );

        $quoteData = $data[0] ?? [];

        //Save to DB
        $quote = Quote::create([
            'quote' => $quoteData['quote'] ?? 'Keep pushing!',
            'author' => $quoteData['author'] ?? 'Unknown',
            'date' => $today,
        ]);

        return ['quote' => $quote, 'source' => 'api'];
    }
}