<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Exception; // Added this
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Response;

class FirebaseJwtMiddleware
{
    use ApiResponse;
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->error('Token not provided', 401);
        }

        try {
            $key = new Key(env('JWT_SECRET'), 'HS256');
            $decoded = JWT::decode($token, $key);

            $role = $decoded->role ?? null;
            if (!$role) {
                return $this->error('Role not found in token', 403);
            }
            $request->attributes->add([
                'user_data' => $decoded,
                'user_role' => $role
            ]);
        } catch (Exception $e) {
            return $this->error('Invalid token', 401);
        }

        return $next($request);
    }
}
