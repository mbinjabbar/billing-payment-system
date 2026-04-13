<?php 

namespace App\Http\Middleware;

use Closure;
use Exception; // Added this
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class FirebaseJwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        
        Log::info($token); 

        if (!$token) {
            Log::error("No token provided"); 
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $key = new Key(env('JWT_SECRET'), 'HS256');
            $decoded = JWT::decode($token, $key);
        
    $role = $decoded->role ?? null; 
    if (!$role) {
        return response()->json(['error' => 'Role not found in token'], 403);
    }
     Log::info($role);

    // Pass it to the request attributes so your Controllers can see it
    $request->attributes->add([
        'user_data' => $decoded,
        'user_role' => $role
    ]);
    Log::info($role);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
