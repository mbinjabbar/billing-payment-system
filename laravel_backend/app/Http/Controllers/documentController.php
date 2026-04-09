<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class documentController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        $documents = Document::with([
        'bill.visit.appointment.patientCase.patient', 
        'uploader'
    ])
    ->when($request->type, function($q) use ($request) {
        return $q->where('document_type', $request->type);
    })
    ->latest()
    ->paginate(10);

    return $this->success($documents, 'Document listing retrieved.');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
