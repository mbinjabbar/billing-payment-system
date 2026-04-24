<?php

namespace App\Http\Middleware;

use App\Enums\PaymentMode;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileValidationMiddleware
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): Response
    {
        // skip validation if payment is not cheque
        if ($request->payment_mode !== PaymentMode::CHEQUE->value) {
            return $next($request);
        }

        // cheque must have an uploaded file
        if (!$request->hasFile('cheque_file')) {
            return $this->error('No file uploaded', 422);
        }

        $file = $request->file('cheque_file');

        // allowed types: PDF, JPG, PNG, DOCX
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        // max file size = 5MB
        $maxSizeKB = 5120;

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return $this->error('Invalid file type. Allowed: PDF, JPG, PNG, DOCX', 422);
        }

        if ($file->getSize() > $maxSizeKB * 1024) {
            return $this->error('File size exceeds the maximum limit of 5MB', 422);
        }

        return $next($request);
    }
}