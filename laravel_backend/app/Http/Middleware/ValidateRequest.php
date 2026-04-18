<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidateRequest
{
    use ApiResponse;
    public function handle(Request $request, Closure $next, string $rulesClass)
    {
        $rules = (new $rulesClass)->rules();

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error(collect($validator->errors()->all())->join(', '), 422);
        }

        return $next($request);
    }
}