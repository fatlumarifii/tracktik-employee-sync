<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'API token is required',
                'error' => 'Missing Authorization header',
            ], 401);
        }

        $validTokens = [
            config('auth.provider1_api_token'),
            config('auth.provider2_api_token'),
        ];

        if (! in_array($token, array_filter($validTokens), true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token',
                'error' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
