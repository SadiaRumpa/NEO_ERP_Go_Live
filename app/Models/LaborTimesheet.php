<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaborTimesheet extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'timesheet_number',
        'user_id',
        'job_id',
        'work_date',
        'start_time',
        'end_time',
        'travel_hours',
        'standard_hours',
        'overtime_1_5_hours',
        'overtime_2_0_hours',
        'total_hours',
        'cost_rate_per_hour',
        'burden_multiplier',
        'direct_pay_amount',
        'fully_burdened_cost',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'travel_hours' => 'decimal:2',
        'standard_hours' => 'decimal:2',
        'overtime_1_5_hours' => 'decimal:2',
        'overtime_2_0_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'cost_rate_per_hour' => 'decimal:2',
        'burden_multiplier' => 'decimal:4',
        'direct_pay_amount' => 'decimal:2',
        'fully_burdened_cost' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
