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
            $document = $this->documentService->getDocument('Invoice', $id);
            $filePath = $this->documentService->resolveFilePath($document);
            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function downloadNF2($id)
    {
        try {
            $document = $this->documentService->getDocument('NF2 Form', $id);
            $filePath = $this->documentService->resolveFilePath($document);
            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function downloadReceipt($paymentId)
    {
        try {
            $document = $this->documentService->getDocument('Receipt', $paymentId);
            $filePath = $this->documentService->resolveFilePath($document);
            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function downloadCheque($id)
    {
        try {
            $document = $this->documentService->getChequeDocument($id);
            $filePath = $this->documentService->resolveFilePath($document);
            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
