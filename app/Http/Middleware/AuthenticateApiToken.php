<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Er ontbreekt een API-token.'], 401);
        }

        try {
            $payload = \Firebase\JWT\JWT::decode($token, new Key(config('app.key'), 'HS256'));
        } catch (\Throwable) {
            return response()->json(['message' => 'Het API-token is ongeldig of verlopen.'], 401);
        }

        $user = User::find($payload->sub ?? null);

        if (! $user) {
            return response()->json(['message' => 'De gebruiker van dit token bestaat niet meer.'], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
