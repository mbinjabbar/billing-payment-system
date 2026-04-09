<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Document;
use App\Models\insuranceFirm;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;
use PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


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
                        ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
                });
            });

            $bills = $query->latest('bill_date')->paginate(10);

            return $this->success($bills, 'Bills retrieved successfully');
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
            $insurance = $data['insurance_coverage'];
            $billAmount = ($charges - $insurance - $discount) + $tax;

             $exists= Bill::where('visit_id', $request->visit_id)->exists();
             if ($exists) { 
            return response()->json([
            'success' => false,
            'message' => 'A bill has already been generated for this visit.'], 422); }
 

            $bill = Bill::create([
                'visit_id' => $data['visit_id'],
                'bill_date' => now(),
                'insurance_firm_id' => $data['insurance_firm_id'],
                'created_by' => $data['created_by'],
                'procedure_codes' => $data['procedure_codes'],
                'charges' => $charges,
                'insurance_coverage' => $insurance,
                'discount_amount' => $discount,
                'tax_amount' => $tax,
                'bill_amount' => $billAmount,
                'outstanding_amount' => $billAmount,
                'paid_amount' => $data['paid_amount'],
                'status' => $data['status'],
                'due_date' => $data['due_date'],
                'notes' => $data['notes']
            ]);
            
             $isCarAccident = $bill->visit->appointment->patientCase->car_accident;
             $view = $isCarAccident ? 'NF2_pdf' : 'Standard_pdf';
             $prefix = $isCarAccident ? 'NF2_' : 'Standard_';

            $bill->load('visit.appointment.patientCase.patient', 'visit.appointment.patientCase.nf2Detail', 'insurance_firm');

            $pdf = PDF::loadView($view, compact('bill'));

            $fileName = $prefix . $bill->bill_number . '.pdf';
            $path = 'bills/' . $fileName;
        
            Storage::put($path, $pdf->output());
            $fileSize = Storage::size($path);
            $filetype = Storage::mimeType($path);
            $maxSize = 5 * 1024 * 1024; 
            if ($fileSize > $maxSize) {
            Storage::delete($path);
             return $this->error('File size exceeds the 5MB limit.');
           }

            $bill->update([
                'generated_document_path' => $path
            ]);

        Document::create([
            'bill_id' => $bill->id,
            'payment_id' => null, 
            'document_type' => $data['document_type'] ?? 'Invoice',
            'file_name' => $fileName,
            'file_type' => $filetype,
            'file_path' => $path,
            'file_size' => $fileSize,
            'upload_date' => now(),
            'uploaded_by' => $data['created_by'],
            'version' => 1
        ]);        
        DB::commit();
        return $this->success($bill, ($isCarAccident ? 'NF2' : 'Standard') . ' Bill generated successfully.');
        } catch (Exception $e) {
              DB::rollBack();
            Log::error('Error generating bill: ' . $e->getMessage());
            return $this->error('bill data not found');
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

            $bill->bill_amount = ($bill->charges - $bill->insurance_coverage - $bill->discount_amount) + $bill->tax_amount;
            $bill->outstanding_amount = $bill->bill_amount - $bill->paid_amount;

            if ($bill->outstanding_amount <= 0) {
                $bill->status = 'Paid';
            } elseif ($bill->outstanding_amount > 0) {
                $bill->status = 'Partial';
            }

            $bill->save();
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
}


        


