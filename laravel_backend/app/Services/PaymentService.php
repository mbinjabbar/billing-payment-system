<?php

namespace App\Services;

use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Models\Bill;
use App\Models\Payment;

class PaymentService
{
    // Validate if bill allows payment posting
    private function validateBill(Bill $bill, $amount)
    {
        // block payments on finalized bills
        if (in_array($bill->status, BillStatus::IS_FINALIZED)) {
            throw new \Exception("Cannot post payment against {$bill->status} bill.");
        }

        // prevent overpayment
        if ($amount > $bill->outstanding_amount) {
            throw new \Exception("Amount exceeds outstanding balance.");
        }
    }

    // Update bill after successful payment
    private function updateBillAfterPayment(Bill $bill, $amount)
    {
        $bill->paid_amount += $amount;

        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;

        $bill->bill_amount =
            ($bill->charges - $insurance - $bill->discount_amount)
            + $bill->tax_amount;

        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

        // determine bill status
        $bill->status = $bill->outstanding_amount <= 0 ? BillStatus::PAID->value : BillStatus::PARTIAL->value;

        $bill->save();
    }

    // Handle cheque file upload (returns file metadata)
    private function handleChequeUpload($file)
    {
        if (!$file) return null;

        return [
            'path' => $file->store('cheque_files', 'public'),
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
        ];
    }

    // Get payments with filters
    public function getFilteredPayments(array $filters)
    {
        $query = Payment::with(
            'bill.visit.appointment.patientCase.patient',
            'receiver'
        )
            ->when(
                $filters['bill_id'] ?? null,
                fn($q, $billId) => $q->where('bill_id', $billId)
            )
            ->when(
                $filters['payment_mode'] ?? null,
                fn($q, $mode) => $q->where('payment_mode', $mode)
            )
            ->when(
                $filters['payment_status'] ?? null,
                fn($q, $status) => $q->where('payment_status', $status)
            )
            ->when(
                !empty($filters['from_date']) && !empty($filters['to_date']),
                fn($q) => $q->whereBetween('payment_date', [
                    $filters['from_date'],
                    $filters['to_date']
                ])
            )
            ->latest();

        return isset($filters['limit'])
            ? $query->limit($filters['limit'])->get()
            : $query->paginate(10);
    }

    // Create payment + optional cheque handling
    public function createPayment(array $data, $file = null)
    {
        $bill = Bill::findOrFail($data['bill_id']);

        $this->validateBill($bill, $data['amount_paid']);

        $cheque = $this->handleChequeUpload($file);

        $payment = Payment::create(array_merge($data, [
            'cheque_file_path' => $cheque['path'] ?? null,
        ]));

        // immediately apply on bill if payment is completed
        if ($payment->payment_status === PaymentStatus::COMPLETED->value) {
            $this->updateBillAfterPayment($bill, $data['amount_paid']);
        }

        return [$payment, $bill, $cheque];
    }

    // Recalculate bill when payment is edited
    public function recalculateBill(Bill $bill, float $newAmountPaid, float $oldAmountPaid)
    {
        $difference = $newAmountPaid - $oldAmountPaid;

        $bill->paid_amount += $difference;

        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;

        $bill->bill_amount =
            ($bill->charges - $insurance - $bill->discount_amount)
            + $bill->tax_amount;

        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

        $bill->status = $bill->outstanding_amount <= 0 ? BillStatus::PAID->value : BillStatus::PARTIAL->value;

        $bill->save();

        return $bill;
    }

    // Reverse payment effect from bill in updates/refunds
    public function reversePaymentImpact(Bill $bill, Payment $payment)
    {
        $bill->paid_amount -= $payment->amount_paid;

        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;

        $bill->bill_amount =
            ($bill->charges - $insurance - $bill->discount_amount)
            + $bill->tax_amount;

        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

        // recalculate status after reversal
        if ($bill->paid_amount <= 0) {
            $bill->status = BillStatus::PENDING->value;
        } elseif ($bill->outstanding_amount > 0) {
            $bill->status = BillStatus::PARTIAL->value;
        } else {
            $bill->status = BillStatus::PAID->value;
        }

        $bill->save();
    }

    // Refund payment and adjust bill
    public function refundPayment(Payment $payment, ?float $refundAmount = null)
    {
        $bill = $payment->bill;

        if ($payment->payment_status !== PaymentStatus::COMPLETED->value) {
            throw new \Exception('Only Completed payments can be refunded');
        }

        $refundAmount = $refundAmount ?? $payment->amount_paid;

        if ($refundAmount > $payment->amount_paid) {
            throw new \Exception('Refund amount cannot exceed original payment');
        }

        $payment->update([
            'payment_status' => PaymentStatus::REFUNDED->value
        ]);

        // adjust bill values after refund
        $bill->paid_amount -= $refundAmount;

        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;

        $bill->bill_amount =
            ($bill->charges - $insurance - $bill->discount_amount)
            + $bill->tax_amount;

        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

        $bill->status = $bill->paid_amount <= 0
            ? BillStatus::PENDING->value
            : ($bill->outstanding_amount > 0 ? BillStatus::PARTIAL->value : BillStatus::PAID->value);

        $bill->save();

        return [$payment, $bill];
    }
}