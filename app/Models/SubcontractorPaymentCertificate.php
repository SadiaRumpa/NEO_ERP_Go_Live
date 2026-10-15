<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractorPaymentCertificate extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'certificate_number',
        'subcontractor_id',
        'job_id',
        'period_start',
        'period_end',
        'tax_month',
        'gross_amount',
        'materials_cost',
        'labor_amount',
        'cis_tax_rate',
        'cis_deduction_amount',
        'net_payable',
        'payment_status',
        'paid_at',
        'certified_by',
        'certified_at',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'date',
        'certified_at' => 'datetime',
        'gross_amount' => 'decimal:2',
        'materials_cost' => 'decimal:2',
        'labor_amount' => 'decimal:2',
        'cis_tax_rate' => 'decimal:2',
        'cis_deduction_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
    ];

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(Subcontractor::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by');
    }
}
