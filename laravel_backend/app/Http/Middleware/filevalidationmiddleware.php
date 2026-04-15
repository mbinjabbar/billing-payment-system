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
        $allowedMimes = [
            'application/pdf',                                        
            'image/jpeg',                                              
            'image/png',                                                
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' 
        ];
        $maxSizeKB = 5120; // 5MB in KB
        
         if (!$request->hasFile('cheque_file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
 
        $file = $request->file('cheque_file');
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json(['error' => 'Invalid file type. Allowed types: PDF, JPG, PNG, DOCX'], 400);
        }
        if ($file->getSize() > $maxSizeKB * 1024) {
            return response()->json(['error' => 'File size exceeds the maximum limit of 5MB'], 400);
        }

        return $next($request);
    }
}


