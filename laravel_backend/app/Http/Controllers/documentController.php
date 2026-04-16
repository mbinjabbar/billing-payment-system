<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class documentController extends Controller
{
    use ApiResponse;
    public function __construct(private DocumentService $documentService){}

    public function index(Request $request)
    {
        try {
            $filters   = $request->only(['role', 'type']);
            $documents = $this->documentService->getFilteredDocuments($filters);
            return $this->success($documents, 'Documents retrieved successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to fetch documents.');
        }
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

    public function downloadReceipt($paymentId)
    {
        try {
            $document = Document::where('payment_id', $paymentId)
                ->where('document_type', 'Receipt')
                ->latest()
                ->firstOrFail();

            $filePath = storage_path('app/private/' . $document->file_path);

            if (!file_exists($filePath)) {
                return $this->error('Receipt file not found.');
            }

            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('Receipt not found.');
        }
    }
}
