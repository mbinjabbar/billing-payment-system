<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Visit;
use App\Models\Payment;
use App\Models\Document;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\HasMany;

class Bill extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'visit_id',
        'bill_number',
        'bill_date',
        'procedure_codes',
        'charges',
        'insurance_coverage',
        'bill_amount',
        'discount_amount',
        'tax_amount',
        'outstanding_amount',
        'paid_amount',
        'status',
        'generated_document_path',
        'notes',
        'due_date'
    ];

    protected static function booted()
    {
        static::creating(function ($bill) {       
        $bill->bill_number = 'BILL-' . now()->format('Ymd') . '-' . strtoupper(uniqid());
        });
    }

    public function visit() {
        return $this->belongsTo(Visit::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function documents() {
        return $this->hasMany(Document::class);
    }
}
