<?php

namespace App\Services\Plant;

use App\Models\PlantHire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PlantHireService
{
    /**
     * Book / create a new Plant & Equipment Hire record.
     */
    public function createPlantHire(array $data, ?User $user = null): PlantHire
    {
        return DB::transaction(function () use ($data, $user) {
            $year = date('Y');
            $count = PlantHire::whereYear('created_at', $year)->count() + 1;
            $hireRef = 'PH-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $plantHire = new PlantHire([
                'business_id' => $user?->business_id ?? $data['business_id'] ?? null,
                'hire_reference' => $data['hire_reference'] ?? $hireRef,
                'job_id' => $data['job_id'],
                'supplier_id' => $data['supplier_id'],
                'item_name' => $data['item_name'],
                'category' => $data['category'] ?? 'tools',
                'serial_asset_tag' => $data['serial_asset_tag'] ?? null,
                'start_date' => $data['start_date'],
                'expected_end_date' => $data['expected_end_date'],
                'daily_rate' => $data['daily_rate'] ?? 0.00,
                'weekly_rate' => $data['weekly_rate'] ?? 0.00,
                'delivery_charge' => $data['delivery_charge'] ?? 0.00,
                'collection_charge' => $data['collection_charge'] ?? 0.00,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            $plantHire->accrued_hire_cost = $plantHire->computeAccruedCost();
            $plantHire->save();

            return $plantHire;
        });
    }

    /**
     * Process Off-Hire and record supplier return reference.
     */
    public function offHirePlant(
        PlantHire $plantHire,
        string $offHireReference,
        ?string $actualEndDate = null,
        ?string $notes = null,
        ?User $user = null
    ): PlantHire {
        return DB::transaction(function () use ($plantHire, $offHireReference, $actualEndDate, $notes, $user) {
            $actualEnd = $actualEndDate ?? now()->toDateString();
            $plantHire->actual_end_date = $actualEnd;
            $plantHire->off_hire_reference = $offHireReference;
            $plantHire->off_hired_at = now();
            $plantHire->off_hired_by = $user?->id ?? auth()->id();
            $plantHire->status = 'off_hired';

            if ($notes) {
                $plantHire->notes = trim(($plantHire->notes ?? '') . "\n[OFF-HIRED]: " . $notes);
            }

            $plantHire->accrued_hire_cost = $plantHire->computeAccruedCost();
            $plantHire->save();

            return $plantHire->fresh(['job', 'supplier', 'offHiredByUser']);
        });
    }

    /**
     * Fetch active plant hires that are nearing scheduled off-hire date (48 hours) or overdue.
     */
    public function getExpiringOrOverdueHires(?int $businessId = null): array
    {
        $query = PlantHire::with(['job', 'supplier'])->where('status', 'active');
        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $allActive = $query->orderBy('expected_end_date', 'asc')->get();

        $overdue = [];
        $expiring = [];

        foreach ($allActive as $hire) {
            if ($hire->isOverdue()) {
                $overdue[] = $hire;
            } elseif ($hire->isExpiringSoon(2)) {
                $expiring[] = $hire;
            }
        }

        return [
            'overdue' => $overdue,
            'expiring' => $expiring,
            'active_count' => $allActive->count(),
            'total_accrued_active' => (float)$allActive->sum('accrued_hire_cost'),
        ];
    }

    /**
     * Re-calculate and synchronize accrued costs for all active plant hires.
     */
    public function syncAccruedCosts(?int $businessId = null): int
    {
        $query = PlantHire::where('status', 'active');
        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $count = 0;
        foreach ($query->get() as $hire) {
            $hire->accrued_hire_cost = $hire->computeAccruedCost();
            $hire->save();
            $count++;
        }

        return $count;
    }

    /**
     * Get total plant & equipment cost allocated to a specific Job's WIP.
     */
    public function getJobAccruedPlantCost(int $jobId): float
    {
        return (float)PlantHire::where('job_id', $jobId)
            ->whereIn('status', ['active', 'off_hired', 'pending_off_hire'])
            ->sum('accrued_hire_cost');
    }
}
