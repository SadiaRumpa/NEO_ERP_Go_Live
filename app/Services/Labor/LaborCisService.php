<?php

namespace App\Services\Labor;

use App\Models\LaborRateCard;
use App\Models\LaborTimesheet;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LaborCisService
{
    /**
     * Calculate direct pay and fully burdened cost for a worker based on their rate card.
     */
    public function calculateLaborCost(User $worker, float $standardHours, float $ot15Hours = 0, float $ot20Hours = 0, float $travelHours = 0): array
    {
        $rateCard = LaborRateCard::where('user_id', $worker->id)
            ->where('is_active', true)
            ->latest('effective_from')
            ->first();

        $baseRate = $rateCard ? (float)$rateCard->base_hourly_rate : 20.00;
        $ot15Mult = $rateCard ? (float)$rateCard->overtime_multiplier_1_5 : 1.50;
        $ot20Mult = $rateCard ? (float)$rateCard->overtime_multiplier_2_0 : 2.00;
        $burdenMultiplier = $rateCard ? $rateCard->burden_multiplier : 1.2680; // default 26.8% overhead

        $standardPay = ($standardHours + $travelHours) * $baseRate;
        $ot15Pay = $ot15Hours * ($baseRate * $ot15Mult);
        $ot20Pay = $ot20Hours * ($baseRate * $ot20Mult);

        $directPay = round($standardPay + $ot15Pay + $ot20Pay, 2);
        $fullyBurdenedCost = round($directPay * $burdenMultiplier, 2);
        $totalHours = $standardHours + $travelHours + $ot15Hours + $ot20Hours;

        return [
            'base_hourly_rate' => $baseRate,
            'burden_multiplier' => $burdenMultiplier,
            'total_hours' => $totalHours,
            'direct_pay_amount' => $directPay,
            'fully_burdened_cost' => $fullyBurdenedCost,
        ];
    }

    /**
     * Submit an operative timesheet.
     */
    public function submitTimesheet(array $data, User $worker): LaborTimesheet
    {
        return DB::transaction(function () use ($data, $worker) {
            $year = date('Y');
            $count = LaborTimesheet::whereYear('created_at', $year)->count() + 1;
            $tsNumber = 'TS-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $standard = (float)($data['standard_hours'] ?? 0);
            $ot15 = (float)($data['overtime_1_5_hours'] ?? 0);
            $ot20 = (float)($data['overtime_2_0_hours'] ?? 0);
            $travel = (float)($data['travel_hours'] ?? 0);

            $costing = $this->calculateLaborCost($worker, $standard, $ot15, $ot20, $travel);

            return LaborTimesheet::create([
                'business_id' => $worker->business_id,
                'timesheet_number' => $data['timesheet_number'] ?? $tsNumber,
                'user_id' => $worker->id,
                'job_id' => $data['job_id'],
                'work_date' => $data['work_date'] ?? now()->toDateString(),
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'travel_hours' => $travel,
                'standard_hours' => $standard,
                'overtime_1_5_hours' => $ot15,
                'overtime_2_0_hours' => $ot20,
                'total_hours' => $costing['total_hours'],
                'cost_rate_per_hour' => $costing['base_hourly_rate'],
                'burden_multiplier' => $costing['burden_multiplier'],
                'direct_pay_amount' => $costing['direct_pay_amount'],
                'fully_burdened_cost' => $costing['fully_burdened_cost'],
                'status' => 'submitted',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Approve timesheet and finalize burdened labor cost into Job WIP.
     */
    public function approveTimesheet(LaborTimesheet $timesheet, User $approver): LaborTimesheet
    {
        $timesheet->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        return $timesheet->fresh(['worker', 'job', 'approver']);
    }

    /**
     * Create and issue a Subcontractor Payment Certificate with UK HMRC CIS deductions.
     */
    public function createPaymentCertificate(array $data, ?User $certifier = null): SubcontractorPaymentCertificate
    {
        return DB::transaction(function () use ($data, $certifier) {
            $year = date('Y');
            $count = SubcontractorPaymentCertificate::whereYear('created_at', $year)->count() + 1;
            $certNumber = 'SPC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $subcontractor = Subcontractor::findOrFail($data['subcontractor_id']);

            $grossAmount = (float)$data['gross_amount'];
            $materialsCost = (float)($data['materials_cost'] ?? 0.00);

            // In UK CIS, materials are strictly exempt from CIS tax deduction
            $laborAmount = max(0.00, $grossAmount - $materialsCost);

            // Apply verified HMRC deduction rate (Gross: 0%, Standard: 20%, Higher: 30%)
            $cisRate = isset($data['cis_tax_rate']) ? (float)$data['cis_tax_rate'] : $subcontractor->cis_tax_rate;
            $cisDeduction = round($laborAmount * ($cisRate / 100), 2);
            $netPayable = round($grossAmount - $cisDeduction, 2);

            $periodEnd = $data['period_end'] ?? now()->toDateString();
            $taxMonth = $data['tax_month'] ?? substr($periodEnd, 0, 7);

            return SubcontractorPaymentCertificate::create([
                'business_id' => $subcontractor->business_id,
                'certificate_number' => $data['certificate_number'] ?? $certNumber,
                'subcontractor_id' => $subcontractor->id,
                'job_id' => $data['job_id'],
                'period_start' => $data['period_start'] ?? now()->subDays(14)->toDateString(),
                'period_end' => $periodEnd,
                'tax_month' => $taxMonth,
                'gross_amount' => $grossAmount,
                'materials_cost' => $materialsCost,
                'labor_amount' => $laborAmount,
                'cis_tax_rate' => $cisRate,
                'cis_deduction_amount' => $cisDeduction,
                'net_payable' => $netPayable,
                'payment_status' => $data['payment_status'] ?? 'certified',
                'certified_by' => $certifier?->id ?? auth()->id(),
                'certified_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Generate HMRC CIS300 Monthly Return summary for a given tax month (e.g. '2026-10').
     */
    public function generateCis300MonthlyReturn(string $taxMonth, ?int $businessId = null): array
    {
        $query = SubcontractorPaymentCertificate::with(['subcontractor', 'job'])
            ->where('tax_month', $taxMonth)
            ->whereIn('payment_status', ['certified', 'paid']);

        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $certificates = $query->get();

        $totalGross = $certificates->sum('gross_amount');
        $totalMaterials = $certificates->sum('materials_cost');
        $totalLabor = $certificates->sum('labor_amount');
        $totalDeductions = $certificates->sum('cis_deduction_amount');
        $totalNetPaid = $certificates->sum('net_payable');

        // Group by Subcontractor for CIS300 schedule
        $subcontractorSummaries = [];
        foreach ($certificates->groupBy('subcontractor_id') as $subId => $certs) {
            $sub = $certs->first()->subcontractor;
            $subcontractorSummaries[] = [
                'subcontractor_id' => $sub->id,
                'name' => $sub->company_name,
                'utr_number' => $sub->utr_number,
                'verification_number' => $sub->cis_verification_number ?? 'VERIFIED',
                'tax_status' => $sub->cis_tax_status,
                'tax_rate' => $certs->first()->cis_tax_rate,
                'total_gross' => $certs->sum('gross_amount'),
                'total_materials' => $certs->sum('materials_cost'),
                'total_labor' => $certs->sum('labor_amount'),
                'total_deductions' => $certs->sum('cis_deduction_amount'),
                'total_net_paid' => $certs->sum('net_payable'),
                'certificates_count' => $certs->count(),
            ];
        }

        return [
            'tax_month' => $taxMonth,
            'filing_deadline' => date('Y-m-19', strtotime($taxMonth . '-01 +1 month')), // UK HMRC CIS filing is 19th of following month
            'total_gross' => (float)$totalGross,
            'total_materials' => (float)$totalMaterials,
            'total_labor' => (float)$totalLabor,
            'total_deductions' => (float)$totalDeductions,
            'total_net_paid' => (float)$totalNetPaid,
            'subcontractors_count' => count($subcontractorSummaries),
            'certificates_count' => $certificates->count(),
            'schedule' => $subcontractorSummaries,
            'certificates' => $certificates,
        ];
    }
}
