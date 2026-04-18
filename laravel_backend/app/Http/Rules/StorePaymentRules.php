<?php

namespace App\Http\Rules;

class StorePaymentRules
{
    public function rules()
    {
        return [
            'bill_id' => 'required|integer|exists:bills,id',
            'received_by' => 'required|integer',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|in:Cash,Cheque,Bank Transfer,Credit Card,Debit Card,Insurance,Online Payment',
            'payment_date' => 'required|date',
            'payment_status' => 'required|in:Completed,Pending,Failed',
            'check_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}