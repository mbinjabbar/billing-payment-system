<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Exception;
use App\Models\Bill;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Document;
use App\Exports\PaymentsExport;
use App\Services\BillService;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\DocumentService;
use App\Services\PaymentService;
use App\Services\SettingService;

class paymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PaymentService $paymentService,
        private DocumentService $documentService,
        private SettingService $settingService,
        private BillService $billService
    ) {}
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'bill_id',
                'payment_mode',
                'payment_status',
                'from_date',
                'to_date'
            ]);

            $payments = $this->paymentService->getFilteredPayments($filters);
            return $this->success($payments, 'Payments retrieved successfully');
        } catch (Exception $e) {
            Log::error('PAYMENT LIST ERROR', ['message' => $e->getMessage()]);
            return $this->error('An error occurred while fetching payments');
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            [$payment, $bill, $cheque] = $this->paymentService->createPayment(
                $data,
                $request->file('cheque_file')
            );

            $settings = $this->settingService->getSettings();

            $bill->load(
                'visit.appointment.patientCase.patient',
                'insurance_firm',
                'payments'
            );

            $payment->load([
                'bill.visit.appointment.patientCase.patient',
                'receiver'
            ]);

            $this->documentService->generateInvoice($bill, $settings);
            $this->documentService->generateReceipt($payment, $settings);

            if ($cheque) {
                $this->documentService->storeChequeDocument(
                    $bill,
                    $payment,
                    $cheque,
                    $data['received_by']
                );
            }

            return $this->success($payment, 'Payment created successfully');
        } catch (\Exception $e) {
            Log::error('STORE PAYMENT FAILED', ['error' => $e->getMessage()]);
            return $this->error($e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::with('bill.visit.appointment.patientCase.patient')->findOrFail($id);
            return $this->success($payment, 'Payment retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Payment not found');
        }
    }

public function update(Request $request, $id)
{
    try {
        $payment = Payment::findOrFail($id);

        if (!in_array($payment->payment_status, ['Pending', 'Failed'])) {
            return $this->error('Only Pending or Failed payments can be edited.', 422);
        }

        $bill          = Bill::findOrFail($request->bill_id ?? $payment->bill_id);
        $oldAmountPaid = $payment->amount_paid;
        $newAmountPaid = $request->amount_paid;

        // Overpayment guard
        if ($newAmountPaid > ($bill->outstanding_amount + $oldAmountPaid)) {
            return $this->error('Amount paid cannot exceed outstanding amount.', 400);
        }

        // Handle cheque file upload
        $filePath = $payment->cheque_file_path;
        $name = $type = $fileSize = null;
        if ($request->hasFile('cheque_file')) {
            $file     = $request->file('cheque_file');
            $name     = $file->getClientOriginalName();
            $type     = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
            $filePath = $file->store('cheque_files', 'public');
            $payment->cheque_file_path = $filePath;
        }

        // Update payment fields
        $payment->fill($request->only([
            'amount_paid', 'payment_mode', 'check_number',
            'bank_name', 'transaction_reference',
            'payment_date', 'payment_status', 'notes',
        ]));
        $payment->save();

        // Only update bill if payment is becoming Completed
        if ($request->payment_status === 'Completed') {
            $bill->paid_amount        += $newAmountPaid;
            $bill->outstanding_amount  = $bill->bill_amount - $bill->paid_amount;
            $this->billService->resolveBillStatus($bill);
            $bill->save();
        }

        // Load relationships for PDFs
        $bill->load('visit.appointment.patientCase.patient', 'insurance_firm', 'payments');
        $payment->load('bill.visit.appointment.patientCase.patient', 'receiver');

        $settings = $this->settingService->getSettings();
        $this->documentService->generateInvoice($bill, $settings);
        $this->documentService->generateReceipt($payment, $settings, true);

        // Cheque Image — create new if file replaced
        if ($request->hasFile('cheque_file')) {
            Document::create([
                'bill_id'       => $bill->id,
                'payment_id'    => $payment->id,
                'document_type' => 'Cheque Image',
                'file_name'     => $name,
                'file_type'     => $type,
                'file_path'     => $filePath,
                'file_size'     => $fileSize,
                'upload_date'   => now(),
                'uploaded_by'   => $payment->received_by,
                'version'       => 1,
            ]);
        }

        return $this->success($payment, 'Payment and bill updated successfully.');
    } catch (Exception $e) {
        Log::error('UPDATE PAYMENT FAILED', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
        return $this->error($e->getMessage());
    }
}

    public function export(Request $request)
    {
        try {
            return Excel::download(new PaymentsExport($request->all()), 'payments.xlsx');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $bill    = Bill::findOrFail($payment->bill_id);

            // Reverse the payment amount
            $this->paymentService->reversePaymentImpact($bill, $payment);

            // Soft delete the payment
            $payment->delete();

            // ── Regenerate Invoice PDF to reflect removed payment ─────────────
            $bill->load('visit.appointment.patientCase.patient', 'insurance_firm', 'payments');
            $settings = $this->settingService->getSettings();

            $this->documentService->generateInvoice($bill, $settings);


            return $this->success(null, 'Payment deleted and bill updated successfully.');
        } catch (Exception $e) {
            Log::error('DELETE PAYMENT FAILED', ['error' => $e->getMessage()]);
            return $this->error('An error occurred while deleting the payment.');
        }
    }

    // refund method
    public function refund(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);

            [$payment, $bill] = $this->paymentService->refundPayment(
                $payment,
                $request->refund_amount
            );

            $settings = $this->settingService->getSettings();

            // Load payment with relationships for receipt PDF
            $payment->load([
                'bill.visit.appointment.patientCase.patient',
                'receiver'
            ]);

            // Generate Refund Receipt PDF
            $this->documentService->generateReceipt(
                $payment,
                $settings,
                false,
                true
            );

            // Regenerate invoice PDF
            $bill->load('visit.appointment.patientCase.patient', 'insurance_firm', 'payments');

            $this->documentService->generateInvoice($bill, $settings);

            return $this->success($payment, 'Payment refunded and bill updated successfully.');
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
