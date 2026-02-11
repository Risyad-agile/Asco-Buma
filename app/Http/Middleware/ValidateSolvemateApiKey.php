<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateSolvemateApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('Authorization');
        $validKey = 'Bearer ' . env('SOLVEMATE_API_KEY');

        if ($apiKey !== $validKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
