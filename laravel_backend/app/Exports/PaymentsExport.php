<?php

namespace App\Exports;
use App\Models\Payment;
use Illuminate\Contracts\Support\Responsable;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentsExport implements FromCollection, WithHeadings

{
      protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Payment::query();

        if (!empty($this->filters['bill_id'])) {
            $query->where('bill_id', $this->filters['bill_id']);
        }

        if (!empty($this->filters['payment_mode'])) {
            $query->where('payment_mode', $this->filters['payment_mode']);
        }

        if (!empty($this->filters['payment_status'])) {
            $query->where('payment_status', $this->filters['payment_status']);
        }

        if (!empty($this->filters['from_date']) && !empty($this->filters['to_date'])) {
            $query->whereBetween('payment_date', [
                $this->filters['from_date'],
                $this->filters['to_date']
            ]);
        }

        return $query->get([
            'id',
            'bill_id',
            'amount_paid',
            'payment_mode',
            'payment_status',
            'payment_date',
            'transaction_reference',
            'notes'
        ]);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Bill ID',
            'Amount Paid',
            'Payment Mode',
            'Payment Status',
            'Payment Date',
            'transaction_reference',
            'notes'
        
        ];
    }
}
