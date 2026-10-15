<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantHire extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'hire_reference',
        'job_id',
        'supplier_id',
        'item_name',
        'category',
        'serial_asset_tag',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'daily_rate',
        'weekly_rate',
        'delivery_charge',
        'collection_charge',
        'status',
        'off_hire_reference',
        'off_hired_at',
        'off_hired_by',
        'accrued_hire_cost',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'off_hired_at' => 'datetime',
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'collection_charge' => 'decimal:2',
        'accrued_hire_cost' => 'decimal:2',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Fjob::class, 'job_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function offHiredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'off_hired_by');
    }

    /**
     * Compute the accrued cost of this hire up to a given date (or current date/actual return date).
     */
    public function computeAccruedCost(?Carbon $targetDate = null): float
    {
        $startDate = Carbon::parse($this->start_date)->startOfDay();
        
        $endDate = $this->actual_end_date 
            ? Carbon::parse($this->actual_end_date)->startOfDay()
            : ($targetDate ? $targetDate->copy()->startOfDay() : Carbon::now()->startOfDay());

        if ($endDate->lt($startDate)) {
            $days = 0;
        } else {
            // Include starting day: 1-day hire has diff of 0 + 1 = 1 day
            $days = $startDate->diffInDays($endDate) + 1;
        }

        $dailyRate = (float)$this->daily_rate;
        $weeklyRate = (float)$this->weekly_rate;

        // If weekly rate is provided and hire duration >= 7 days, optimize calculation (weeks + extra days)
        if ($weeklyRate > 0 && $days >= 7) {
            $weeks = intdiv($days, 7);
            $extraDays = $days % 7;
            $hireCharge = ($weeks * $weeklyRate) + ($extraDays * ($dailyRate > 0 ? $dailyRate : ($weeklyRate / 7)));
        } else {
            $hireCharge = $days * $dailyRate;
        }

        $totalCost = $hireCharge + (float)$this->delivery_charge + (float)$this->collection_charge;

        return round($totalCost, 2);
    }

    /**
     * Check if hire is currently active and past its scheduled off-hire date.
     */
    public function isOverdue(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return Carbon::parse($this->expected_end_date)->startOfDay()->lt(Carbon::today());
    }

    /**
     * Check if hire is expiring within N days (e.g. 2 days / 48 hours).
     */
    public function isExpiringSoon(int $daysThreshold = 2): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $expected = Carbon::parse($this->expected_end_date)->startOfDay();
        $today = Carbon::today();

        return $expected->gte($today) && $expected->lte($today->copy()->addDays($daysThreshold));
    }
}
