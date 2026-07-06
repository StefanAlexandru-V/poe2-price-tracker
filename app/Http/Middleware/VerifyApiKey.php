<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$key) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $validKeys = array_filter(explode(',', config('services.api_keys', '')));

        if (empty($validKeys) || !in_array($key, $validKeys, true)) {
            return response()->json(['error' => 'Invalid API key'], 403);
        }

        return $next($request);
    }
}
