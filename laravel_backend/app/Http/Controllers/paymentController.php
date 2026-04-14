<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Bill;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Document;
use App\Exports\PaymentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;


class paymentController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        try {
            $query = Payment::with('bill.visit.appointment.patientCase.patient');
            $query->when($request->filled('bill_id'), function ($q) use ($request) {
                $q->where('bill_id', $request->bill_id);
            });
            $query->when($request->filled('payment_mode'), function ($q) use ($request) {
                $q->where('payment_mode', $request->payment_mode);
            });

            $query->when($request->filled('payment_status'), function ($q) use ($request) {
                $q->where('payment_status', $request->payment_status);
            });
            $query->when(
                $request->filled('from_date') && $request->filled('to_date'),
                function ($q) use ($request) {
                    $q->whereBetween('payment_date', [
                        $request->from_date,
                        $request->to_date
                    ]);
                }
            );

            $payments = $query->latest()->paginate(10);
            return $this->success($payments, 'Payments retrieved successfully');
        } catch (Exception $e) {
            \Log::error('PAYMENT LIST ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {

            $data = $request->all();
            $bill = Bill::findOrFail($data['bill_id']);

            if (in_array($bill->status, ['Cancelled', 'Written Off', 'Paid'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot post payment against a ' . $bill->status . ' bill.'
                ], 422);
            }

            $name = null;
            $type = null;
            $filePath = null;


            if ($request->hasFile('cheque_file')) {
                $file = $request->file('cheque_file');
                $name = $file->getClientOriginalName();
                $type = $file->getClientOriginalExtension();
                $filePath = $file->store('cheque_files', 'public');
                $data['cheque_file_path'] = $filePath;
            }

            if ($data['amount_paid'] > $bill->outstanding_amount) {
                return response()->json(['message' => 'Amount paid cannot exceed outstanding amount'], 400);
            }
            $payment = Payment::create([
                'bill_id' => $data['bill_id'],
                'received_by' => $data['received_by'],
                'amount_paid' => $data['amount_paid'],
                'payment_mode' => $data['payment_mode'],
                'check_number' => $data['check_number'],
                'bank_name' => $data['bank_name'],
                'transaction_reference' => $data['transaction_reference'],
                'payment_date' => $data['payment_date'],
                'payment_status' => $data['payment_status'],
                'cheque_file_path'      => $filePath,
                'notes' => $data['notes'] ?? null,
            ]);
            $bill->paid_amount += $data['amount_paid'];
            $insuranceAmount = ($bill->charges * $bill->insurance_coverage) / 100;
            $bill->bill_amount = ($bill->charges - $insuranceAmount - $bill->discount_amount) + $bill->tax_amount;
            $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;
            if ($bill->outstanding_amount <= 0) {
                $bill->status = 'Paid';
            } elseif ($bill->outstanding_amount > 0) {
                $bill->status = 'Partial';
            }
            $bill->save();

            $settings = Setting::all()->pluck('value', 'key')->toArray();

            $payment->load([
                'bill.visit.appointment.patientCase.patient',
                'receiver'
            ]);

            $receiptPdf      = Pdf::loadView('Receipt_pdf', compact('payment', 'settings'));
            $receiptFileName = 'Receipt_' . $payment->payment_number . '.pdf';
            $receiptPath     = 'bills/' . $receiptFileName;

            $invoicePdf      = Pdf::loadView('Invoice_pdf', compact('bill'));
            $invoiceFileName = 'Invoice_' . $bill->bill_number . '.pdf';
            $invoicePath     = 'bills/' . $invoiceFileName;

            Storage::put($invoicePath, $invoicePdf->output());
            Storage::put($receiptPath, $receiptPdf->output());

            $document = Document::where('payment_id', $payment->id)
                ->where('document_type', 'Receipt')
                ->first();

            Document::create([
                'bill_id'       => $bill->id,
                'document_type' => 'Invoice',
                'file_name'     => $invoiceFileName,
                'file_type'     => 'application/pdf',
                'file_path'     => $invoicePath,
                'file_size'     => Storage::size($invoicePath),
                'upload_date'   => now(),
                'uploaded_by'   => $data['received_by'],
                'version'       => 1,
            ]);


                if ($document) {
                $document->update([
                'bill_id'       => $bill->id,
                'payment_id'    => $payment->id,
                'document_type' => 'Receipt',
                'file_name'     => $receiptFileName,
                'file_type'     => 'application/pdf',
                'file_path'     => $receiptPath,
                'file_size'     => Storage::size($receiptPath),
                'upload_date'   => now(),
                'uploaded_by'   => $data['received_by'],
                'version'       => 1,
            ]);
                } else {
                    Document::create([
                        'bill_id'       => $bill->id,
                        'payment_id'    => $payment->id,
                        'document_type' => 'Receipt',
                        'file_name'     => $receiptFileName,
                        'file_type'     => 'application/pdf',
                        'file_path'     => $receiptPath,
                        'file_size'     => Storage::size($receiptPath),
                        'upload_date'   => now(),
                        'uploaded_by'   => $data['received_by'],
                        'version'       => 1,
                    ]);
                }

            if ($request->hasFile('cheque_file')) {
                Document::create([
                    'bill_id'       => $bill->id,
                    'payment_id'    => $payment->id,
                    'document_type' => 'Cheque Image',
                    'file_name'     => $name,
                    'file_type'     => $type,
                    'file_path'     => $filePath,
                    'file_size'     => $file->getSize(),
                    'upload_date'   => now(),
                    'uploaded_by'   => $data['received_by'],
                    'version'       => 1
                ]);
            }

            return $this->success($payment, 'Payment Created and Bill updated successfully');
        } catch (Exception $e) {

            return $this->error($e->getMessage());
        }
    }


    public function show($id)
    {
        try {
            $payment = Payment::findOrFail($id);
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
            $bill = Bill::findOrFail($request->bill_id);

            $filePath = $payment->cheque_file_path;
            $name = null;
            $type = null;
            $fileSize = null;


            if ($request->hasFile('cheque_file')) {
                $file = $request->file('cheque_file');
                $name = $file->getClientOriginalName();
                $type = $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $filePath = $file->store('cheque_files', 'public');
            }


            $oldAmountPaid = $payment->amount_paid;
            $newAmountPaid = $request->amount_paid;
            if ($newAmountPaid > ($bill->outstanding_amount + $oldAmountPaid)) {
                return response()->json(['message' => 'Amount paid cannot exceed outstanding amount'], 400);
            }

            $payment->fill($request->only([
                'bill_id',
                'received_by',
                'amount_paid',
                'payment_mode',
                'check_number',
                'bank_name',
                'transaction_reference',
                'payment_date',
                'payment_status',
                'notes'
            ]));

            if ($request->hasFile('cheque_file')) {
                $payment->cheque_file_path = $filePath;
            }
            $payment->save();
            $difference = $newAmountPaid - $oldAmountPaid;
            $bill->increment('paid_amount', $difference);
            $bill->refresh();
            $insuranceAmount = ($bill->charges * $bill->insurance_coverage) / 100;
            $bill->bill_amount = ($bill->charges - $insuranceAmount - $bill->discount_amount) + $bill->tax_amount;
            $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;
            if ($bill->outstanding_amount <= 0) {
                $bill->status = 'Paid';
            } else {
                $bill->status = 'Partial';
            }
            $bill->save();

            $invoicePdf      = Pdf::loadView('Invoice_pdf', compact('bill'));
            $invoiceFileName = 'Invoice_' . $bill->bill_number . '.pdf';
            $invoicePath     = 'bills/' . $invoiceFileName; 
            $receiptPdf      = Pdf::loadView('Receipt_pdf', compact('payment'));
            $receiptFileName = 'Receipt_' . $payment->payment_number . '.pdf';
            $receiptPath     = 'bills/' . $receiptFileName;

            Storage::put($invoicePath, $invoicePdf->output()); 
            Storage::put($receiptPath, $receiptPdf->output());

               Document::create([
                'bill_id'       => $bill->id,
                'document_type' => 'Invoice',
                'file_name'     => $invoiceFileName,
                'file_type'     => 'application/pdf',
                'file_path'     => $invoicePath,
                'file_size'     => Storage::size($invoicePath),
                'upload_date'   => now(),
                'uploaded_by'   => $payment['received_by'],
                'version'       => 1,
            ]);
            
            Document::create([
                'bill_id'       => $bill->id,
                'payment_id'    => $payment->id,
                'document_type' => 'Receipt',
                'file_name'     => $receiptFileName,
                'file_type'     => 'application/pdf',
                'file_path'     => $receiptPath,
                'file_size'     => Storage::size($receiptPath),
                'upload_date'   => now(),
                'uploaded_by'   => $payment['received_by'],
                'version'       => 1,
            ]);

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
                    'uploaded_by'   => $data['received_by'],
                    'version'       => 1
                ]);
            }
            return $this->success($payment, 'Payment and Bill updated successfully');
        } catch (Exception $e) {
            \Log::error('UPDATE PAYMENT FAILED', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return $this->error($e->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(
                new PaymentsExport($request->all()),
                'payments.xlsx'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $bill    = Bill::findOrFail($payment->bill_id);

            $bill->paid_amount -= $payment->amount_paid;
            $insuranceAmount    = ($bill->charges * $bill->insurance_coverage) / 100;
            $bill->bill_amount  = ($bill->charges - $insuranceAmount - $bill->discount_amount) + $bill->tax_amount;
            $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

            if ($bill->paid_amount <= 0) {
                $bill->status = 'Pending';
            } elseif ($bill->outstanding_amount > 0) {
                $bill->status = 'Partial';
            } else {
                $bill->status = 'Paid';
            }

            $bill->save();
            $payment->delete();

            return $this->success(null, 'Payment deleted and bill updated successfully.');
        } catch (Exception $e) {
            return $this->error('An error occurred while deleting the payment.');
        }
    }
}
