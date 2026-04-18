<?php

namespace App\Http\Rules;

class UpdateBillRules
{
    public function rules()
    {
        return [
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
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}