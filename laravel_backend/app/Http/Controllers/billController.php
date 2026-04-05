<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;
use PDF;

class billController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        try {
            $query = Bill::with(['visit.appointment.patientCase.patient']);

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
        try {
            $data = $request->all();
            $charges = $data['charges'];
            $discount = $data['discount_amount'];
            $tax = $data['tax_amount'];
            $insurance = $data['insurance_coverage'];
            $billAmount = ($charges - $insurance - $discount) + $tax;

            $bill = Bill::create([
                'visit_id' => $data['visit_id'],
                'bill_date' => now(),
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
            return $this->success($bill, 'Bill generated successfully.');
        } catch (Exception $e) {
            Log::error('Error generating bill: ' . $e->getMessage());
            return $this->error('Bill data not found.');
        }
    }

    public function show($id)
    {
        try {
            $bill = Bill::with(['visit.appointment.patientCase.patient'])->findOrFail($id);
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
        public function generatePDF($id)
{
    try {
        $bill = Bill::with(['visit.appointment.patientCase.patient'])->findOrFail($id);

        $pdf = PDF::loadView('bill_pdf', compact('bill'));

        //For Download
        //return $pdf->download('bill_'.$bill->bill_number.'.pdf');

        // For browser preview
         return $pdf->stream('bill_'.$bill->bill_number.'.pdf');

    } catch (Exception $e) {
        return $this->error('Failed to generate PDF.');
    }
}
}


        


