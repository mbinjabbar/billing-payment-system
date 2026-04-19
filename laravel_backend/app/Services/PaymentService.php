<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Document;
use App\Models\Payment;

class PaymentService
{
    private function validateBill(Bill $bill, $amount)
    {
        if (in_array($bill->status, ['Cancelled', 'Written Off', 'Paid'])) {
            throw new \Exception("Cannot post payment against {$bill->status} bill.");
        }

        if ($amount > $bill->outstanding_amount) {
            throw new \Exception("Amount exceeds outstanding balance.");
        }
    }

    private function updateBillAfterPayment(Bill $bill, $amount)
    {
        $bill->paid_amount += $amount;

        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;

        $bill->bill_amount =
            ($bill->charges - $insurance - $bill->discount_amount)
            + $bill->tax_amount;

        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

        $bill->status = $bill->outstanding_amount <= 0 ? 'Paid' : 'Partial';

        $bill->save();
    }

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
    public function getFilteredPayments(array $filters)
    {
        $query = Payment::with('bill.visit.appointment.patientCase.patient', 'receiver')
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

    public function createPayment(array $data, $file = null)
    {
        $bill = Bill::findOrFail($data['bill_id']);

        $this->validateBill($bill, $data['amount_paid']);

        $cheque = $this->handleChequeUpload($file);

        $payment = Payment::create(array_merge($data, [
            'cheque_file_path' => $cheque['path'] ?? null,
        ]));

        if ($payment->payment_status === 'Completed') {
            $this->updateBillAfterPayment($bill, $data['amount_paid']);
        }

        return [$payment, $bill, $cheque];
    }

    public function recalculateBill(Bill $bill, float $newAmountPaid, float $oldAmountPaid)
    {
        $difference = $newAmountPaid - $oldAmountPaid;

        $bill->paid_amount += $difference;

        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;

        $bill->bill_amount =
            ($bill->charges - $insurance - $bill->discount_amount)
            + $bill->tax_amount;

        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

        $bill->status = $bill->outstanding_amount <= 0 ? 'Paid' : 'Partial';

        $bill->save();

        return $bill;
    }

    public function reversePaymentImpact(Bill $bill, Payment $payment)
    {
        $bill->paid_amount -= $payment->amount_paid;

        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;

        $bill->bill_amount =
            ($bill->charges - $insurance - $bill->discount_amount)
            + $bill->tax_amount;

        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

        if ($bill->paid_amount <= 0) {
            $bill->status = 'Pending';
        } elseif ($bill->outstanding_amount > 0) {
            $bill->status = 'Partial';
        } else {
            $bill->status = 'Paid';
        }

        $bill->save();
    }

    public function refundPayment(Payment $payment, ?float $refundAmount = null)
    {
        $bill = $payment->bill;

        if ($payment->payment_status !== 'Completed') {
            throw new \Exception('Only Completed payments can be refunded');
        }

        $refundAmount = $refundAmount ?? $payment->amount_paid;

        if ($refundAmount > $payment->amount_paid) {
            throw new \Exception('Refund amount cannot exceed original payment');
        }

        // mark refunded
        $payment->update([
            'payment_status' => 'Refunded'
        ]);

        // reverse bill
        $bill->paid_amount -= $refundAmount;
        $insurance = ($bill->charges * $bill->insurance_coverage) / 100;
        $bill->bill_amount = ($bill->charges - $insurance - $bill->discount_amount) + $bill->tax_amount;
        $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;
        $bill->status = $bill->paid_amount <= 0 ? 'Pending' : ($bill->outstanding_amount > 0 ? 'Partial' : 'Paid');

        $bill->save();

        return [$payment, $bill];
    }
}
