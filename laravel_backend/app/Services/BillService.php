<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class BillService
{
    public function __construct(private SettingService $settingService) {}

    // ── Query Builder (private — shared internally) ───────────────────────
    private function buildBillQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Bill::with('visit.appointment.patientCase.patient', 'insurance_firm');

        $query->when(
            $filters['status'] ?? null,
            fn($q) => $q->where('status', $filters['status'])
        );

        $query->when(
            !empty($filters['start_date']) && !empty($filters['end_date']),
            fn($q) => $q->whereBetween('bill_date', [$filters['start_date'], $filters['end_date']])
        );

        $query->when(
            !empty($filters['min_amount']) && !empty($filters['max_amount']),
            fn($q) => $q->whereBetween('bill_amount', [$filters['min_amount'], $filters['max_amount']])
        );

        $query->when(
            $filters['patient_name'] ?? null,
            fn($q) => $q->whereHas(
                'visit.appointment.patientCase.patient',
                fn($sub) => $sub
                    ->where('first_name', 'like', '%' . $filters['patient_name'] . '%')
                    ->orWhere('middle_name', 'like', '%' . $filters['patient_name'] . '%')
                    ->orWhere('last_name',   'like', '%' . $filters['patient_name'] . '%')
            )
        );

        return $query;
    }

    // ── Filtered Bills + Stats ────────────────────────────────────────────
    public function getFilteredBills(array $filters)
    {
        return $this->buildBillQuery($filters)->latest('bill_date')->paginate(10);
    }

    public function getBillStats(array $filters): array
    {
        $query = $this->buildBillQuery($filters);

        return [
            'total_bill_amount' => (clone $query)->sum('bill_amount'),
            'total_paid_amount' => (clone $query)->sum('paid_amount'),
            'total_outstanding' => (clone $query)->sum('outstanding_amount'),
            'total_bills'       => (clone $query)->count(),
            'pending_count'     => (clone $query)->where('status', 'Pending')->count(),
            'partial_count'     => (clone $query)->where('status', 'Partial')->count(),
            'paid_count'        => (clone $query)->where('status', 'Paid')->count(),
        ];
    }

    // ── Bill Amount Calculation ───────────────────────────────────────────
    public function calculateBillAmount(float $charges, float $insurancePercent, float $discount, float $tax): float
    {
        $insuranceAmount = ($charges * $insurancePercent) / 100;
        return ($charges - $insuranceAmount - $discount) + $tax;
    }

    public function recalculateBill(Bill $bill): void
    {
        $insuranceAmount          = ($bill->charges * $bill->insurance_coverage) / 100;
        $bill->bill_amount        = ($bill->charges - $insuranceAmount - $bill->discount_amount) + $bill->tax_amount;
        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;
    }

    // ── Bill Status ───────────────────────────────────────────────────────
    private function resolveBillStatus(Bill $bill): void
    {
        if ($bill->paid_amount <= 0) {
            $bill->status = 'Pending';
        } elseif ($bill->outstanding_amount <= 0) {
            $bill->status = 'Paid';
        } else {
            $bill->status = 'Partial';
        }
    }

    // ── PDF Generation ────────────────────────────────────────────────────
    // Used on bill creation — generates Invoice + NF2 (if car accident)
    public function generateBillDocuments(Bill $bill, array $settings): void
    {
        $filesToGenerate = [
            ['view' => 'Invoice_pdf', 'prefix' => 'Invoice_', 'type' => 'Invoice']
        ];

        // Append NF2 — do NOT replace the array
        if ($bill->visit->appointment->patientCase->car_accident) {
            $filesToGenerate[] = ['view' => 'NF2_pdf', 'prefix' => 'NF2_', 'type' => 'NF2 Form'];
        }

        foreach ($filesToGenerate as $file) {
            $pdf      = Pdf::loadView($file['view'], compact('bill', 'settings'));
            $fileName = $file['prefix'] . $bill->bill_number . '.pdf';
            $path     = 'bills/' . $fileName;

            Storage::put($path, $pdf->output());

            $fullPath = Storage::path($path);
            $fileSize = filesize($fullPath);
            $mimeType = mime_content_type($fullPath);

            if ($fileSize > (5 * 1024 * 1024)) {
                throw new \Exception("File $fileName exceeds 5MB limit.");
            }

            Document::create([
                'bill_id'       => $bill->id,
                'document_type' => $file['type'],
                'file_name'     => $fileName,
                'file_type'     => $mimeType,
                'file_path'     => $path,
                'file_size'     => $fileSize,
                'upload_date'   => now(),
                'uploaded_by'   => $bill->created_by,
                'version'       => 1,
            ]);

            $bill->update(['generated_document_path' => $path]);
        }
    }

    // Used on bill update/payment/status change — updates existing Invoice document
    public function generateInvoice(Bill $bill, array $settings): void
    {
        $fileName = 'Invoice_' . $bill->bill_number . '.pdf';
        $path     = 'bills/' . $fileName;

        Storage::put($path, Pdf::loadView('Invoice_pdf', compact('bill', 'settings'))->output());
        
        $fullPath = Storage::path($path);
        $fileSize = filesize($fullPath);
        $mimeType = mime_content_type($fullPath);

        $document = Document::where('bill_id', $bill->id)
            ->where('document_type', 'Invoice')
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
            Document::create([
                'bill_id'       => $bill->id,
                'document_type' => 'Invoice',
                'file_name'     => $fileName,
                'file_type'     => $mimeType,
                'file_path'     => $path,
                'file_size'     => $fileSize,
                'upload_date'   => now(),
                'uploaded_by'   => $bill->created_by,
                'version'       => 1,
            ]);
        }

        $bill->update(['generated_document_path' => $path]);
    }

    // ── Update Bill ───────────────────────────────────────────────────────
    public function updateBill(Bill $bill, array $data): Bill
    {
        $bill->fill([
            'procedure_codes'    => $data['procedure_codes'],
            'charges'            => $data['charges'],
            'insurance_coverage' => $data['insurance_coverage'],
            'discount_amount'    => $data['discount_amount'],
            'tax_amount'         => $data['tax_amount'],
            'notes'              => $data['notes'] ?? null,
            'due_date'           => $data['due_date'] ?? null,
        ]);

        $this->recalculateBill($bill);

        // Draft being submitted → Pending, otherwise resolve from amounts
        if ($bill->status === 'Draft') {
            $bill->status = 'Pending';
        } else {
            $this->resolveBillStatus($bill);
        }

        $bill->save();

        $bill->load(
            'visit.appointment.patientCase.patient',
            'insurance_firm',
            'payments'
        );

        return $bill;
    }

    // ── Update Bill Status (Cancelled / Written Off) ──────────────────────
    public function updateBillStatus(int $id, string $status): Bill
    {
        $bill = Bill::findOrFail($id);

        if ($status === 'Cancelled' && $bill->paid_amount > 0) {
            throw new \Exception('Cannot cancel a bill with payments posted. Use Write Off instead.');
        }

        if ($status === 'Written Off' && $bill->status !== 'Partial') {
            throw new \Exception('Only partially paid bills can be written off.');
        }

        $bill->status = $status;
        $bill->save();

        $bill->load(
            'visit.appointment.patientCase.patient',
            'insurance_firm',
            'payments'
        );

        $settings = $this->settingService->getSettings();
        $this->generateInvoice($bill, $settings);

        return $bill;
    }
}