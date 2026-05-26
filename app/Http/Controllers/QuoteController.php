<?php

namespace App\Http\Controllers;

use App\Services\QuoteService;

class QuoteController extends Controller
{
    public function __construct(protected QuoteService $quoteService)
    {
    }

    public function daily()
    {
        $data = $this->quoteService->getDailyQuote();
        return $this->successResponse($data, 'Daily quote retrieved');
    }
}