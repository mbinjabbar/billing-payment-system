<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bill;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{ 
    use SoftDeletes;
        protected $fillable = [
            'bill_id',
            'payment_number',
            'amount_paid',
            'payment_mode',
            'check_number',
            'bank_name',
            'transaction_reference',
            'payment_date',
            'payment_status',
            'cheque_file_path',
            'notes',
            'received_by',
        ];
    
        protected static function booted()
    {
        static::creating(function ($payment) {       
        $payment->payment_number = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
        });
    }
 public function bill() {
        return $this->belongsTo(Bill::class);
    }
}
