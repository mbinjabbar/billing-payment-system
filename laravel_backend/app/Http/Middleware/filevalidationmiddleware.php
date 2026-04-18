<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class filevalidationmiddleware
{
    use ApiResponse;
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasFile('cheque_file')) {
            return $this->error('No file uploaded', 422);
        }

        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $maxSizeKB = 5120;

        $file = $request->file('cheque_file');

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return $this->error('Invalid file type. Allowed: PDF, JPG, PNG, DOCX', 422);
        }

        if ($file->getSize() > $maxSizeKB * 1024) {
            return $this->error('File size exceeds the maximum limit of 5MB', 422);
        }

        return $next($request);
    }
}
