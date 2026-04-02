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
  public function generateBill(Request $request) 
{ 

    try{
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
        'status' => 'Pending',
        'due_date' => $data['due_date'],
        'notes' => $data['notes'] 
    ]);

    return $this->success(['bill' => $bill], 'Bill generated successfully.');
    }
    catch(\Exception $e){
        Log::error('Error generating bill: ' . $e->getMessage());
        return $this->error('visit data not found.');
    }
}
}