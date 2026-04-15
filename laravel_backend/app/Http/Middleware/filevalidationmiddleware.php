<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class filevalidationmiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasFile('cheque_file')) {
            return $next($request);
        }

        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $maxSizeKB = 5120; // 5MB in Kb

        $file = $request->file('cheque_file');

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file type. Allowed: PDF, JPG, PNG, DOCX'
            ], 422);
        }

        if ($file->getSize() > $maxSizeKB * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'File size exceeds the maximum limit of 5MB'
            ], 422);
        }

        return $next($request);
    }
}
