<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bill;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class Document extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'bill_id',
        'document_type',
        'file_name',
        'file_type',
        'file_path',
        'file_size',
        'upload_date',
        'uploaded_by',
        'version',
    ];

    public function bill() {
        return $this->belongsTo(Bill::class);
    }
}
