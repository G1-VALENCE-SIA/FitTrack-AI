<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::all();

        return response()->json($quotes);
    }

    public function store(Request $request)
    {
        $quote = Quote::create([
            'quote' => $request->quote,
            'author' => $request->author,
        ]);

        return view('quotes', compact('quotes'));
    }
}