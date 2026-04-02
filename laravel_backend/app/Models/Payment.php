<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid'  => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($payment) {
            $payment->payment_number = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
        });
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
