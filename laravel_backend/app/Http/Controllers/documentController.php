<?php

namespace App\Http\Controllers;

use App\Services\DocumentService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class documentController extends Controller
{
    use ApiResponse;

    public function __construct(private DocumentService $documentService) {}

    // Get list of documents with optional filters
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['role', 'type']);

            $documents = $this->documentService->getFilteredDocuments($filters);

            return $this->success($documents, 'Documents retrieved successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to fetch documents.');
        }
    }

    // Download invoice PDF
    public function downloadInvoice($id)
    {
        try {
            $document = $this->documentService->getDocument('Invoice', $id);
            $filePath = $this->documentService->resolveFilePath($document);

            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('Failed to download invoice.');
        }
    }

    // Download NF2 form
    public function downloadNF2($id)
    {
        try {
            $document = $this->documentService->getDocument('NF2 Form', $id);
            $filePath = $this->documentService->resolveFilePath($document);

            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('Failed to download NF2 form.');
        }
    }

    // Download payment receipt
    public function downloadReceipt($paymentId)
    {
        try {
            $document = $this->documentService->getDocument('Receipt', $paymentId);
            $filePath = $this->documentService->resolveFilePath($document);

            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('Failed to download receipt.');
        }
    }

    // Download cheque document
    public function downloadCheque($id)
    {
        try {
            $document = $this->documentService->getChequeDocument($id);
            $filePath = $this->documentService->resolveFilePath($document);

            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('Failed to download cheque.');
        }
    }
}