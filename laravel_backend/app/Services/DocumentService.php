<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Document;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function getFilteredDocuments(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Document::with('bill.visit.appointment.patientCase.patient', 'payment');

        // Role-based document type filtering
        $role = $filters['role'] ?? null;
        if ($role === 'Biller') {
            $query->whereIn('document_type', ['Invoice', 'NF2 Form']);
        } elseif ($role === 'Payment Poster') {
            $query->whereIn('document_type', ['Invoice', 'Cheque Image', 'Receipt']);
        }
        // Admin gets everything — no filter applied

        // Type filter
        if (!empty($filters['type'])) {
            $query->where('document_type', $filters['type']);
        }

        return $query->latest()->paginate(10);
    }

    public function getDocument(string $type, int $id)
    {
        $query = Document::where('document_type', $type)->latest();

        // Receipt is linked to payment, everything else to bill
        if ($type === 'Receipt') {
            $query->where('payment_id', $id);
        } else {
            $query->where('bill_id', $id);
        }

        return $query->firstOrFail();
    }

    public function getChequeDocument(int $id)
    {
        $document = Document::findOrFail($id);
        if ($document->document_type !== 'Cheque Image') {
            throw new \Exception('Not a cheque document.');
        }
        return $document;
    }

    public function resolveFilePath(Document $document): string
    {
        // Cheque files are in public storage, everything else in local storage
        $basePath = $document->document_type === 'Cheque Image'
            ? storage_path('app/public/' . $document->file_path)
            : storage_path('app/private/' . $document->file_path);

        if (!file_exists($basePath)) {
            throw new \Exception('File not found on disk.');
        }

        return $basePath;
    }

    private function generateAndStoreDocument(
        Bill $bill,
        string $view,
        string $prefix,
        string $type,
        array $settings,
        bool $isUpdate = false
    ) {
        $fileName = $prefix . $bill->bill_number . '.pdf';
        $path = 'bills/' . $fileName;

        $pdf = Pdf::loadView($view, compact('bill', 'settings'));
        Storage::put($path, $pdf->output());

        $fullPath = Storage::path($path);
        $fileSize = filesize($fullPath);
        $mimeType = mime_content_type($fullPath);

        if ($fileSize > (5 * 1024 * 1024)) {
            throw new \Exception("File $fileName exceeds 5MB limit.");
        }

        if ($isUpdate) {
            $document = Document::where('bill_id', $bill->id)
                ->where('document_type', $type)
                ->first();

            if ($document) {
                $document->update([
                    'file_name'   => $fileName,
                    'file_type'   => $mimeType,
                    'file_path'   => $path,
                    'file_size'   => $fileSize,
                    'upload_date' => now(),
                    'uploaded_by' => $bill->created_by,
                    'version'     => $document->version + 1,
                ]);
            } else {
                $this->createDocument($bill, $type, $fileName, $mimeType, $path, $fileSize);
            }
        } else {
            $this->createDocument($bill, $type, $fileName, $mimeType, $path, $fileSize);
        }
        $bill->update(['generated_document_path' => $path]);
    }

    private function createDocument($bill, $type, $fileName, $mimeType, $path, $fileSize)
    {
        Document::create([
            'bill_id'       => $bill->id,
            'document_type' => $type,
            'file_name'     => $fileName,
            'file_type'     => $mimeType,
            'file_path'     => $path,
            'file_size'     => $fileSize,
            'upload_date'   => now(),
            'uploaded_by'   => $bill->created_by,
            'version'       => 1,
        ]);
    }

    // Used on bill update/payment/status change — updates existing Invoice document
    public function generateInvoice(Bill $bill, array $settings)
    {
        $this->generateAndStoreDocument(
            $bill,
            'Invoice_pdf',
            'Invoice_',
            'Invoice',
            $settings,
            true
        );
    }

    // ── PDF Generation ────────────────────────────────────────────────────
    // Used on bill creation — generates Invoice + NF2 (if car accident)
    public function generateBillDocuments(Bill $bill, array $settings)
    {
        $filesToGenerate = [
            ['view' => 'Invoice_pdf', 'prefix' => 'Invoice_', 'type' => 'Invoice']
        ];

        if ($bill->visit->appointment->patientCase->car_accident) {
            $filesToGenerate[] = ['view' => 'NF2_pdf', 'prefix' => 'NF2_', 'type' => 'NF2 Form'];
        }

        foreach ($filesToGenerate as $file) {
            $this->generateAndStoreDocument(
                $bill,
                $file['view'],
                $file['prefix'],
                $file['type'],
                $settings
            );
        }
    }

    public function generateReceipt($payment, array $settings, $isUpdate = false, bool $isRefund = false)
    {
        $suffix = $isRefund ? '_refund' : '';
        $fileName = 'Receipt_' . $payment->payment_number . $suffix . '.pdf';
        $path     = 'bills/' . $fileName;

        $pdf = Pdf::loadView('Receipt_pdf', compact('payment', 'settings'));
        Storage::put($path, $pdf->output());

        $fullPath = Storage::path($path);
        $fileSize = filesize($fullPath);

        $document = Document::where('payment_id', $payment->id)
            ->where('document_type', 'Receipt')
            ->first();

        if ($isUpdate && $document) {
            $document->update([
                'file_size'   => $fileSize,
                'upload_date' => now(),
                'version'     => $document->version + 1,
            ]);
        } else {
            Document::create([
                'bill_id'       => $payment->bill_id,
                'payment_id'    => $payment->id,
                'document_type' => 'Receipt',
                'file_name'     => $fileName,
                'file_type'     => 'application/pdf',
                'file_path'     => $path,
                'file_size'     => $fileSize,
                'upload_date'   => now(),
                'uploaded_by'   => $payment->received_by,
                'version'       => 1,
            ]);
        }
    }

    public function storeChequeDocument(
        Bill $bill,
        Payment $payment,
        array $cheque,
        int $uploadedBy
    ) {
        Document::create([
            'bill_id'       => $bill->id,
            'payment_id'    => $payment->id,
            'document_type' => 'Cheque Image',
            'file_name'     => $cheque['name'],
            'file_type'     => $cheque['type'],
            'file_path'     => $cheque['path'],
            'file_size'     => $cheque['size'],
            'upload_date'   => now(),
            'uploaded_by'   => $uploadedBy,
            'version'       => 1,
        ]);
    }
}
