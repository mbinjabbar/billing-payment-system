<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'visit_id',
        'created_by',
        'insurance_firm_id',
        'bill_date',
        'procedure_codes',
        'charges',
        'insurance_coverage',
        'bill_amount',
        'discount_amount',
        'tax_amount',
        'outstanding_amount',
        'status',
        'generated_document_path',
        'notes',
        'due_date'
    ];

    protected $casts = [
        'procedure_codes' => 'array',
        'bill_date' => 'date',
        'due_date' => 'date',
        'charges'            => 'decimal:2',
        'insurance_coverage' => 'decimal:2',
        'bill_amount'        => 'decimal:2',
        'discount_amount'    => 'decimal:2',
        'tax_amount'         => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'paid_amount'        => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($bill) {
            $bill->bill_number = 'B-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -5));
        });

        static::deleting(function ($bill) {
            $bill->payments()->delete();
            $bill->documents()->delete();
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function insurance_firm()
    {
        return $this->belongsTo(InsuranceFirm::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
