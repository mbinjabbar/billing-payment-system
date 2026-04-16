<?php

namespace App\Services;

use App\Models\Bill;

class BillService
{
    public function __construct(private SettingService $settingService, private DocumentService $documentService) {}

    // ── Query Builder (private — shared internally) ───────────────────────
    private function buildBillQuery(array $filters)
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
        $query = $this->buildBillQuery($filters);
        $limit = $filters['limit'] ?? null;

        if ($limit) {
            return $query->latest('bill_date')->limit($limit)->get();
        }

        return $query->latest('bill_date')->paginate(10);
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
    public function calculateBillAmount(float $charges, float $insurancePercent, float $discount, float $tax)
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
    public function resolveBillStatus(Bill $bill)
    {
        if ($bill->paid_amount <= 0) {
            $bill->status = 'Pending';
        } elseif ($bill->outstanding_amount <= 0) {
            $bill->status = 'Paid';
        } else {
            $bill->status = 'Partial';
        }
    }

    // ── Update Bill ───────────────────────────────────────────────────────
    public function updateBill(Bill $bill, array $data)
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
    public function updateBillStatus(int $id, string $status)
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
        $this->documentService->generateInvoice($bill, $settings);

        return $bill;
    }
}
