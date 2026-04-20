<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use App\Models\Payment;
use Exception;
use App\Models\Bill;
use App\Traits\ApiResponse;
use App\Exports\PaymentsExport;
use App\Services\BillService;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\DocumentService;
use App\Services\PaymentService;
use App\Services\SettingService;
use Illuminate\Support\Facades\DB;
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

    // Get all payments with filters
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
            return $this->error('An error occurred while fetching payments');
        }
    }

    // Create new payment (and optional cheque + documents)
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            // create payment via service
            [$payment, $bill, $cheque] = $this->paymentService->createPayment(
                $data,
                $request->file('cheque_file')
            );

            // store cheque document if uploaded
            if ($cheque) {
                $this->documentService->storeChequeDocument(
                    $bill,
                    $payment,
                    $cheque,
                    $data['received_by']
                );
            }

            // generate invoice + receipt only when payment is completed
            if ($request->payment_status === PaymentStatus::COMPLETED->value) {
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

            DB::commit();
            return $this->success($payment, 'Payment created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create payment.');
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::with(
                'bill.visit.appointment.patientCase.patient',
                'receiver'
            )->findOrFail($id);

            return $this->success($payment, 'Payment retrieved successfully');
        } catch (Exception $e) {
            return $this->error('Payment not found');
        }
    }

    // Update payment (only pending/failed allowed)
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);

            // restrict editing to only pending or failed payments
            if (!in_array($payment->payment_status, PaymentStatus::ALLOWED_EDIT)) {
                DB::rollBack();
                return $this->error('Only Pending or Failed payments can be edited.', 422);
            }

            $bill = Bill::findOrFail($request->bill_id ?? $payment->bill_id);

            $oldAmountPaid = $payment->amount_paid;
            $newAmountPaid = $request->amount_paid;

            // prevent overpayment
            if ($newAmountPaid > ($bill->outstanding_amount + $oldAmountPaid)) {
                DB::rollBack();
                return $this->error('Amount paid cannot exceed outstanding amount.', 400);
            }

            // store old cheque before overwrite
            $oldChequePath = $payment->cheque_file_path;

            $filePath = $payment->cheque_file_path;
            $name = $type = $fileSize = null;

            // handle new cheque upload
            if ($request->hasFile('cheque_file')) {
                $file = $request->file('cheque_file');

                $name     = $file->getClientOriginalName();
                $type     = $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $filePath = $file->store('cheque_files', 'public');

                $payment->cheque_file_path = $filePath;
            }

            // update payment fields
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

            // update bill only if payment is completed
            if ($request->payment_status === PaymentStatus::COMPLETED->value) {
                $bill->paid_amount += $newAmountPaid;
                $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

                $this->billService->resolveBillStatus($bill);
                $bill->save();

                $bill->load('visit.appointment.patientCase.patient', 'insurance_firm', 'payments');
                $payment->load('bill.visit.appointment.patientCase.patient', 'receiver');

                $settings = $this->settingService->getSettings();

                $this->documentService->generateInvoice($bill, $settings);
                $this->documentService->generateReceipt($payment, $settings, true);
            }

            // handle cheque file replacement
            if ($request->hasFile('cheque_file')) {
                if ($oldChequePath) {
                    Storage::disk('public')->delete($oldChequePath);
                }

                $this->documentService->storeChequeDocument(
                    $bill,
                    $payment,
                    [
                        'name' => $name,
                        'type' => $type,
                        'path' => $filePath,
                        'size' => $fileSize
                    ],
                    $payment->received_by
                );
            }

            DB::commit();
            return $this->success($payment, 'Payment updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to update payment.');
        }
    }

    // Export payments to Excel
    public function export(Request $request)
    {
        try {
            return Excel::download(
                new PaymentsExport($request->all()),
                'payments.xlsx'
            );
        } catch (Exception $e) {
            return $this->error('Failed to export payments.');
        }
    }

    // Delete payment (only if not completed or refunded)
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);

            if ($payment->payment_status === PaymentStatus::COMPLETED->value) {
                DB::rollBack();
                return $this->error('Cannot delete a Completed payment. Use Refund instead.', 422);
            }

            if ($payment->payment_status === PaymentStatus::REFUNDED->value) {
                DB::rollBack();
                return $this->error('Cannot delete a Refunded payment.', 422);
            }

            $payment->delete();

            DB::commit();
            return $this->success(null, 'Payment deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('An error occurred while deleting the payment.');
        }
    }

    // Refund payment and regenerate bill + documents
    public function refund(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);

            [$payment, $bill] = $this->paymentService->refundPayment(
                $payment,
                $request->refund_amount
            );

            $settings = $this->settingService->getSettings();

            $payment->load([
                'bill.visit.appointment.patientCase.patient',
                'receiver'
            ]);

            // generate refund receipt
            $this->documentService->generateReceipt(
                $payment,
                $settings,
                false,
                true
            );

            // regenerate invoice after refund
            $bill->load('visit.appointment.patientCase.patient', 'insurance_firm', 'payments');

            $this->documentService->generateInvoice($bill, $settings);

            DB::commit();
            return $this->success($payment, 'Payment refunded and bill updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error('Failed to refund payment.');
        }
    }
}