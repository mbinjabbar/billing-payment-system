<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidateRequest
{
    public function handle(Request $request, Closure $next, string $rulesClass)
    {
        $rules = (new $rulesClass)->rules();

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => collect($validator->errors()->all())->join(', '),
            ], 422);
        }

        return $next($request);
    }
}