<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Services\QuoteService;

class QuoteController extends Controller
{
    protected $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function daily()
    {
        $quote = $this->quoteService->getDailyQuote();

        return response()->json($quote);
    }

    public function store(Request $request)
    {
        $quote = Quote::create([
            'quote' => $request->quote,
            'author' => $request->author,
            'quote_date' => now()->toDateString(),
        ]);

        return response()->json($quote);
    }
}