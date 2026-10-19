<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockItemRequisition extends Model
{
    protected $fillable = [
        'requisition_number',
        'fjob_id',
        'stock_warehouse_id',
        'requisition_date',
        'status',
        'requested_by',
        'approved_by',
        'issued_by',
        'approved_at',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'issued_at' => 'datetime',
    ];

    public function details(){
        return $this->hasMany(StockItemRequisitionDetail::class);
    }

    public function fjob()
    {
        return $this->belongsTo(Fjob::class);
    }

    public function stockWarehouse()
    {
        return $this->belongsTo(StockWarehouse::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
