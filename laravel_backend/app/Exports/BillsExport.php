<?php

namespace App\Exports;

use App\Models\Bill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BillsExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Bill::with('visit.appointment.patientCase.patient');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['patient_name'])) {
            $query->whereHas('visit.appointment.patientCase.patient', function ($q) {
                $q->where('first_name', 'like', '%' . $this->filters['patient_name'] . '%')
                  ->orWhere('last_name',  'like', '%' . $this->filters['patient_name'] . '%');
            });
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('bill_date', [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        if (!empty($this->filters['min_amount']) && !empty($this->filters['max_amount'])) {
            $query->whereBetween('bill_amount', [
                $this->filters['min_amount'],
                $this->filters['max_amount']
            ]);
        }

        return $query->latest('bill_date')->get()->map(function ($bill) {
            return [
                'id'                 => $bill->id,
                'bill_number'        => $bill->bill_number,
                'patient'            => $bill->visit?->appointment?->patientCase?->patient?->full_name ?? '—',
                'bill_date'          => $bill->bill_date,
                'due_date'           => $bill->due_date,
                'charges'            => $bill->charges,
                'insurance_coverage' => $bill->insurance_coverage,
                'discount_amount'    => $bill->discount_amount,
                'tax_amount'         => $bill->tax_amount,
                'bill_amount'        => $bill->bill_amount,
                'paid_amount'        => $bill->paid_amount,
                'outstanding_amount' => $bill->outstanding_amount,
                'status'             => $bill->status,
                'notes'              => $bill->notes,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Bill Number',
            'Patient',
            'Bill Date',
            'Due Date',
            'Charges',
            'Insurance Coverage (%)',
            'Discount',
            'Tax (%)',
            'Bill Amount',
            'Paid Amount',
            'Outstanding Amount',
            'Status',
            'Notes',
        ];
    }
}