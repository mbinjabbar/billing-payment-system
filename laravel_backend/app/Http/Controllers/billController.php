<?php

namespace App\Http\Controllers;

use App\Exports\BillsExport;
use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Document;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Setting;

class billController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Bill::with('visit.appointment.patientCase.patient', 'insurance_firm');

            $query->when($request->status, function ($q) use ($request) {
                return $q->where('status', $request->status);
            });

            $query->when($request->start_date && $request->end_date, function ($q) use ($request) {
                return $q->whereBetween('bill_date', [$request->start_date, $request->end_date]);
            });

            $query->when($request->min_amount && $request->max_amount, function ($q) use ($request) {
                return $q->whereBetween('bill_amount', [$request->min_amount, $request->max_amount]);
            });

            $query->when($request->patient_name, function ($q) use ($request) {
                return $q->whereHas('visit.appointment.patientCase.patient', function ($sub) use ($request) {
                    return $sub->where('first_name', 'like', '%' . $request->patient_name . '%')
                        ->orWhere('middle_name', 'like', '%' . $request->patient_name . '%')
                        ->orWhere('last_name',   'like', '%' . $request->patient_name . '%');
                });
            });

            // Stats calculated on full dataset before pagination
            $stats = [
                'total_bill_amount'  => (clone $query)->sum('bill_amount'),
                'total_paid_amount'  => (clone $query)->sum('paid_amount'),
                'total_outstanding'  => (clone $query)->sum('outstanding_amount'),
                'total_bills'        => (clone $query)->count(),
                'pending_count'      => (clone $query)->where('status', 'Pending')->count(),
                'partial_count'      => (clone $query)->where('status', 'Partial')->count(),
                'paid_count'         => (clone $query)->where('status', 'Paid')->count(),
            ];

            $bills = $query->latest('bill_date')->paginate(10);

            return $this->success($bills, 'Bills retrieved successfully', 200, $stats);
        } catch (Exception $e) {
            return $this->error('Unable to fetch bills.');
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $charges = $data['charges'];
            $discount = $data['discount_amount'];
            $tax = $data['tax_amount'];
            $insurancePercent = $data['insurance_coverage'];
            $insuranceAmount = ($charges * $insurancePercent) / 100;
            $billAmount = ($charges - $insuranceAmount - $discount) + $tax;

            $exists = Bill::where('visit_id', $request->visit_id)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'A bill has already been generated for this visit.'
                ], 422);
            }

            $bill = Bill::create([
                'visit_id' => $data['visit_id'],
                'bill_date' => now(),
                'insurance_firm_id' => $data['insurance_firm_id'],
                'created_by' => $data['created_by'],
                'procedure_codes' => $data['procedure_codes'],
                'charges' => $charges,
                'insurance_coverage' => $insurancePercent,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'bill_amount' => $billAmount,
                'outstanding_amount' => $billAmount,
                'paid_amount' => $data['paid_amount'],
                'status' => $data['status'],
                'due_date' => $data['due_date'],
                'notes' => $data['notes']
            ]);

            $bill->load('visit.appointment.patientCase.patient', 'visit.appointment.patientCase.nf2Detail', 'insurance_firm');
            $isCarAccident = $bill->visit->appointment->patientCase->car_accident;


            $filesToGenerate = [];
            $filesToGenerate[] = ['view' => 'Invoice_pdf', 'prefix' => 'Invoice_', 'type' => 'Invoice'];

            if ($isCarAccident) {
                $filesToGenerate[] = ['view' => 'NF2_pdf', 'prefix' => 'NF2_', 'type' => 'NF2 Form'];
            }

            foreach ($filesToGenerate as $file) {
                $settings = Setting::all()->pluck('value', 'key')->toArray();
                $pdf = PDF::loadView($file['view'], compact('bill', 'settings'));
                $fileName = $file['prefix'] . $bill->bill_number . '.pdf';
                $path = 'bills/' . $fileName;

                Storage::put($path, $pdf->output());
                $fileSize = Storage::size($path);

                if ($fileSize > (5 * 1024 * 1024)) {
                    throw new Exception("File $fileName exceeds 5MB limit.");
                }


                Document::create([
                    'bill_id' => $bill->id,
                    'document_type' => $file['type'],
                    'file_name' => $fileName,
                    'file_type' => Storage::mimeType($path),
                    'file_path' => $path,
                    'file_size' => $fileSize,
                    'upload_date' => now(),
                    'uploaded_by' => $data['created_by'],
                    'version' => 1
                ]);

                $bill->update(['generated_document_path' => $path]);
            }

            DB::commit();
            $msg = $isCarAccident ? 'Standard and NF2 Bills generated.' : 'Standard Bill generated.';
            return $this->success($bill, $msg);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage());
            return $this->error('Failed to generate bill: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $bill = Bill::with(['visit.appointment.patientCase.patient', 'insurance_firm'])->findOrFail($id);
            return $this->success($bill, 'Bill detail fetched successfully.');
        } catch (Exception $e) {
            return $this->error('Bill data not found.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $bill = Bill::findOrFail($id);

            if (in_array($bill->status, ['Cancelled', 'Written Off'])) {
                return $this->error('Cannot edit a bill that is Cancelled or Written Off.', 403);
            }
            // $user = $request->get('auth_user');

            // if($user->role === 'Payment Poster') {
            //     return $this->error("Payment poster cannot edit billing details", 403);
            // }

            $bill->fill($request->only([
                'procedure_codes',
                'charges',
                'insurance_coverage',
                'discount_amount',
                'tax_amount',
                'notes',
                'due_date'
            ]));

            $insuranceAmount = ($bill->charges * $bill->insurance_coverage) / 100;
            $bill->bill_amount = ($bill->charges - $insuranceAmount - $bill->discount_amount) + $bill->tax_amount;
            $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

            if ($bill->outstanding_amount <= 0) {
                $bill->status = 'Paid';
            } elseif ($bill->outstanding_amount > 0) {
                $bill->status = 'Partial';
            }

            $bill->save();

            $bill->load('visit.appointment.patientCase.patient', 'insurance_firm');

            $pdf = Pdf::loadView('Invoice_pdf', compact('bill'));

            $fileName = 'Invoice_' . $bill->bill_number . '.pdf';
            $path = 'bills/' . $fileName;

            Storage::put($path, $pdf->output());

            $fileSize = Storage::size($path);
            // Update or Create Document
            // ---------------------------
            $document = Document::where('bill_id', $bill->id)
                ->where('document_type', 'Invoice')
                ->first();

            if ($document) {
                $version = $document->version + 1;

                $document->update([
                    'file_name' => $fileName,
                    'file_type' => Storage::mimeType($path),
                    'file_path' => $path,
                    'file_size' => $fileSize,
                    'upload_date' => now(),
                    'uploaded_by' => auth()->id() ?? $bill->created_by,
                    'version' => $version
                ]);
            } else {
                Document::create([
                    'bill_id' => $bill->id,
                    'document_type' => 'Invoice',
                    'file_name' => $fileName,
                    'file_type' => Storage::mimeType($path),
                    'file_path' => $path,
                    'file_size' => $fileSize,
                    'upload_date' => now(),
                    'uploaded_by' => auth()->id() ?? $bill->created_by,
                    'version' => 1
                ]);
            }
            return $this->success($bill, 'Bill updated and recalculated successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to update bill.');
        }
    }

    public function destroy($id)
    {
        try {
            $bill = Bill::findOrFail($id);
            $bill->delete();
            return $this->success(null, 'Bill has been soft-deleted successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to delete the bill.');
        }
    }
    public function downloadPDF($id)
    {
        try {
            $bill = Bill::with(['visit.appointment.patientCase.patient'])->findOrFail($id);
            $filePath = storage_path('app/private/' . $bill->generated_document_path);
            if (!file_exists($filePath)) {
                return $this->error('PDF file not found.');
            }
            return response()->download($filePath);
        } catch (Exception $e) {
            return $this->error('Failed to generate PDF.');
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(
                new BillsExport($request->all()),
                'bills.xlsx'
            );
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $bill = Bill::findOrFail($id);
            $request->validate([
                'status' => 'required|in:Cancelled,Written Off'
            ]);
            $bill->status = $request->status;
            $bill->save();
            return $this->success($bill, 'Bill status updated successfully.');
        } catch (Exception $e) {
            return $this->error('Failed to update bill status.');
        }
    }
}
