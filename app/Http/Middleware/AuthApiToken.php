<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AuthApiToken
{
    /**
     * Handle an incoming request.
     * Check for Authorization: Bearer <token> or api_token query param
     */
    public function handle(Request $request, Closure $next)
    {
        $token = null;

        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        if (! $token && $request->query('api_token')) {
            $token = $request->query('api_token');
        }

        if (! $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // set the user for the request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
