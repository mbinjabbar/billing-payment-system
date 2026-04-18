<?php

namespace App\Http\Rules;

class StoreBillRules
{
    public function rules()
    {
        return [
            'visit_id' => 'required|integer|exists:visits,id',
            'created_by' => 'required|integer',
            'insurance_firm_id' => 'nullable|integer|exists:insurance_firms,id',
            'procedure_codes' => 'required|array|min:1',
            'procedure_codes.*.code' => 'required|string',
            'procedure_codes.*.name' => 'required|string',
            'procedure_codes.*.standard_charge' => 'required|numeric|min:0',
            'charges' => 'required|numeric|min:0',
            'insurance_coverage' => 'required|numeric|min:0|max:100',
            'discount_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'bill_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:Draft,Pending',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}