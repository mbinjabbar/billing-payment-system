<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
public function handle(Request $request, Closure $next, string $role): Response 
{
    $userRole = $request->attributes->get('user_role');

    if (!$userRole || $userRole !== $role) {
        return response()->json([
            'success' => false,
            'message' => "Role " . ($userRole ?? 'Guest') . " is not authorized to access this route"
        ], 403);
    }

    return $next($request);
}
}
