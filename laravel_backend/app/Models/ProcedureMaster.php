<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcedureMaster extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'code',
        'name',
        'standard_charge',
        'is_active'
    ];

    protected $casts = [
        'standard_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}