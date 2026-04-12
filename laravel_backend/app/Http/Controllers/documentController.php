<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Traits\ApiResponse;
use Exception;
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
            ->when($request->type, function ($q) use ($request) {
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

    public function downloadInvoice($id)
    {
        try {
            $document = Document::where('bill_id', $id)
                ->where('document_type', 'Invoice')
                ->latest()
                ->firstOrFail();

            $filePath = storage_path('app/private/' . $document->file_path);

            if (!file_exists($filePath)) {
                return $this->error('Invoice PDF not found.');
            }

            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('Invoice document not found.');
        }
    }

    public function downloadNF2($id)
    {
        try {
            $document = Document::where('bill_id', $id)
                ->where('document_type', 'NF2 Form')
                ->latest()
                ->firstOrFail();

            $filePath = storage_path('app/private/' . $document->file_path);

            if (!file_exists($filePath)) {
                return $this->error('NF2 document not found.');
            }

            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('NF2 document not found.');
        }
    }
}
