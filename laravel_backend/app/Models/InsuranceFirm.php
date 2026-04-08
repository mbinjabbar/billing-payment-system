<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceFirm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'firm_type',
        'contact_person',
        'email',
        'phone',
        'address',
        'carrier_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bills()
    {
        return $this->hasMany(Bill::class, 'insurance_firm_id');
    }
}