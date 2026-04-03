<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponse;

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

            $query->when($request->start_date && $request->end_data, function ($q) use ($request) {
                return $q->whereBetween('bill_data', [$request->start_date, $request->end_date]);
            });

            $query->when($request->min_amount && $request->max_amount, function ($q) use ($request) {
                return $q->whereBetween('bill_amount', [$request->min_amount, $request->max_amount]);
            });

            $query->when($request->patient_name, function ($q) use ($request) {
                return $q->whereHas('visit.appointment.patientCase.patient', function ($sub) use ($request) {
                    return $sub->where('first_name', 'like', '%' . $request->patient_name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
                });
            });

            $bills = $query->latest('bill_date')->paginate(2);

            return $this->success(['bills' => $bills], 'Bills retrieved successfully');
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
            $billAmount = ($charges - $discount + $tax) - $insurance;

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
            return $this->success(['bill' => $bill], 'Bill generated successfully.');
        } catch (Exception $e) {
            Log::error('Error generating bill: ' . $e->getMessage());
            return $this->error('Bill data not found.');
        }
    }
}
