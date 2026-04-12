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
        $query = Document::with('bill.visit.appointment.patientCase.patient', 'payment');

        // Filter by role
        $role = $request->query('role');

        if ($role === 'Biller') {
            $query->whereIn('document_type', ['Invoice', 'NF2 Form']);
        } elseif ($role === 'Payment Poster') {
            $query->whereIn('document_type', ['Invoice', 'Cheque Image']);
        }
        // Admin gets everything

        $documents = $query->latest()->paginate(10);
        return $this->success($documents, 'Documents retrieved successfully.');
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

    public function downloadCheque($id)
{
    try {
        $document = Document::findOrFail($id);

        if ($document->document_type !== 'Cheque Image') {
            return $this->error('Not a cheque document.');
        }

        // Cheque files stored in public storage
        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            return $this->error('Cheque file not found.');
        }

        return response()->download($filePath);
    } catch (Exception $e) {
        return $this->error('Failed to download cheque.');
    }
}
}
