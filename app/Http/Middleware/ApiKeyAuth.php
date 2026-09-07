<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Key');

        if (! $token) {
            return response()->json(['message' => 'API key requerida.'], 401);
        }

        $key = ApiKey::where('token', hash('sha256', $token))->first();

        if (! $key) {
            return response()->json(['message' => 'API key inválida.'], 401);
        }

        $key->forceFill(['last_used_at' => now()])->saveQuietly();

        return $next($request);
    }
}
