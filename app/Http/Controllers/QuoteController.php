<?php

namespace App\Http\Controllers;

use App\Services\QuoteService;

class QuoteController extends Controller
{
    public function __construct(protected QuoteService $quoteService) {}

    public function daily()
    {
        try {
            $data = $this->quoteService->getDailyQuote();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}