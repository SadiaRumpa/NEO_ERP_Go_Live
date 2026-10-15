<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobValuation extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'application_number',
        'job_id',
        'valuation_date',
        'period_end',
        'cumulative_work_value',
        'materials_on_site_value',
        'gross_valuation',
        'previous_certified_amount',
        'current_gross_amount',
        'retention_rate',
        'retention_deduction',
        'net_claim_amount',
        'status',
        'certified_amount',
        'certified_at',
        'certified_by',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'valuation_date' => 'date',
        'period_end' => 'date',
        'paid_at' => 'date',
        'certified_at' => 'datetime',
        'cumulative_work_value' => 'decimal:2',
        'materials_on_site_value' => 'decimal:2',
        'gross_valuation' => 'decimal:2',
        'previous_certified_amount' => 'decimal:2',
        'current_gross_amount' => 'decimal:2',
        'retention_rate' => 'decimal:2',
        'retention_deduction' => 'decimal:2',
        'net_claim_amount' => 'decimal:2',
        'certified_amount' => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by');
    }

    public function retention(): HasOne
    {
        return $this->hasOne(JobRetention::class, 'valuation_id');
    }
}
