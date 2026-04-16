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
use Illuminate\Support\Facades\Storage;

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
                'to_date',
                'limit'
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


            if ($cheque) {
                $this->documentService->storeChequeDocument(
                    $bill,
                    $payment,
                    $cheque,
                    $data['received_by']
                );
            }

            if ($request->payment_status === 'Completed') {
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
 
            // Only Pending or Failed payments can be edited
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
 
            // ── Step 1: Save old cheque path BEFORE anything overwrites it ────
            $oldChequePath = $payment->cheque_file_path;
            $filePath      = $payment->cheque_file_path;
            $name = $type = $fileSize = null;
 
            // ── Step 2: Store new cheque file on disk if uploaded ─────────────
            if ($request->hasFile('cheque_file')) {
                $file     = $request->file('cheque_file');
                $name     = $file->getClientOriginalName();
                $type     = $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $filePath = $file->store('cheque_files', 'public');
                $payment->cheque_file_path = $filePath;
            }
 
            // ── Step 3: Update payment fields and save to DB ──────────────────
            $payment->fill($request->only([
                'amount_paid',
                'payment_mode',
                'check_number',
                'bank_name',
                'transaction_reference',
                'payment_date',
                'payment_status',
                'notes',
            ]));
            $payment->save();
 
            // ── Step 4: Update bill + regenerate PDFs only if Completed ───────
            if ($request->payment_status === 'Completed') {
                $bill->paid_amount        += $newAmountPaid;
                $bill->outstanding_amount  = $bill->bill_amount - $bill->paid_amount;
                $this->billService->resolveBillStatus($bill);
                $bill->save();
 
                $bill->load('visit.appointment.patientCase.patient', 'insurance_firm', 'payments');
                $payment->load('bill.visit.appointment.patientCase.patient', 'receiver');
 
                $settings = $this->settingService->getSettings();
                $this->documentService->generateInvoice($bill, $settings);
                $this->documentService->generateReceipt($payment, $settings, true);
            }
 
            // ── Step 5: Handle cheque document AFTER payment saved ────────────
            if ($request->hasFile('cheque_file')) {
                // Delete old file from disk using saved old path
                if ($oldChequePath) {
                    Storage::disk('public')->delete($oldChequePath);
                }
 
                // storeChequeDocument soft deletes old record internally and creates new
                $this->documentService->storeChequeDocument(
                    $bill,
                    $payment,
                    ['name' => $name, 'type' => $type, 'path' => $filePath, 'size' => $fileSize],
                    $payment->received_by
                );
            }
 
            return $this->success($payment, 'Payment updated successfully.');
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

            if ($payment->payment_status === 'Completed') {
                return $this->error('Cannot delete a Completed payment. Use Refund instead.', 422);
            }

            if ($payment->payment_status === 'Refunded') {
                return $this->error('Cannot delete a Refunded payment.', 422);
            }

            $payment->delete();

            return $this->success(null, 'Payment deleted successfully.');
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
