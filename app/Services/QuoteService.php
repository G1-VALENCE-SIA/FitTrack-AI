<?php

namespace App\Services;

use App\Models\Quote;
use Illuminate\Support\Facades\Http;

class QuoteService
{
    public function getDailyQuote()
    {
        $today = now()->toDateString();

        $existingQuote = Quote::where('quote_date', $today)->first();

        if ($existingQuote) {
            return $existingQuote;
        }

        $response = Http::get('https://zenquotes.io/api/random');

        if ($response->successful()) {

            $data = $response->json()[0];

            $quote = Quote::create([
                'quote' => $data['q'],
                'author' => $data['a'],
                'quote_date' => $today,
            ]);

            return $quote;
        }

        return null;
    }
}