<?php

namespace App\Http\Controllers;

use App\Services\BrapiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function __invoke(Request $request, BrapiService $brapi): JsonResponse
    {
        $symbol = strtoupper(trim($request->input('symbol', '')));

        if (empty($symbol)) {
            return response()->json(['error' => 'Symbol is required'], 422);
        }

        $quotes = $brapi->fetchQuotes([$symbol]);
        $quote = $quotes->get($symbol);

        if ($quote === null) {
            return response()->json(['error' => 'Quote not found'], 404);
        }

        return response()->json($quote);
    }
}
