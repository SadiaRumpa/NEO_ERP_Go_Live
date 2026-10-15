<?php

namespace App\Services\Wip;

use App\Models\Fjob;
use App\Models\JobRetention;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\LaborTimesheet;
use App\Models\PlantHire;
use App\Models\PurchaseOrder;
use App\Models\StockItemRequisition;
use App\Models\SubcontractorPaymentCertificate;
use App\Models\SupplierBill;
use Illuminate\Support\Collection;

class JobCostingWipService
{
    /**
     * Compute comprehensive 4-pillar Live Job Costing & WIP Financial Ledger for a specific Job.
     */
    public function computeJobWip(int $jobId): array
    {
        $job = Fjob::with(['customer'])->findOrFail($jobId);

        // 1. REVENUE & CONTRACT SUM
        $approvedVariations = (float)JobVariation::where('job_id', $jobId)
            ->where('status', 'approved')
            ->sum('quoted_amount');

        $pendingVariations = (float)JobVariation::where('job_id', $jobId)
            ->whereIn('status', ['draft', 'submitted'])
            ->sum('quoted_amount');

        // Original Quoted Sum (from quotation or job default)
        $originalContract = 0.00;
        if (method_exists($job, 'quotations')) {
            $quote = $job->quotations()->where('status', 'accepted')->first();
            $originalContract = $quote ? (float)$quote->total : 0.00;
        }

        $revisedContractSum = round($originalContract + $approvedVariations, 2);

        // Certified Claims & Cash Collected
        $valuations = JobValuation::where('job_id', $jobId)->get();
        $totalCertifiedRevenue = (float)$valuations->whereIn('status', ['certified', 'paid'])->sum(fn($v) => $v->certified_amount ?? $v->net_claim_amount);
        $totalCashReceived = (float)$valuations->where('status', 'paid')->sum(fn($v) => $v->certified_amount ?? $v->net_claim_amount);

        // Retentions
        $retentions = JobRetention::where('job_id', $jobId)->get();
        $retentionHeld = (float)$retentions->sum(fn($r) => $r->balance_held);

        // 2. PILLAR 1: MATERIALS
        $purchaseOrders = PurchaseOrder::where('job_id', $jobId)->get();
        $poCommittedCost = (float)$purchaseOrders->whereIn('status', ['draft', 'sent', 'partially_received', 'received'])->sum('total_amount');

        // Incurred Material Bills (3-Way Matched Supplier Bills)
        $poIds = $purchaseOrders->pluck('id')->toArray();
        $supplierBills = SupplierBill::whereIn('purchase_order_id', $poIds)->get();
        $billsIncurredCost = (float)$supplierBills->whereIn('match_status', ['approved', 'overridden'])->sum(fn($b) => $b->total_amount ?? $b->billed_total ?? 0);

        // Stock Requisitions directly issued to job
        $stockRequisitionCost = 0.00;
        if (class_exists(StockItemRequisition::class)) {
            $reqs = StockItemRequisition::where('job_id', $jobId)->where('status', 'approved')->get();
            $stockRequisitionCost = (float)$reqs->sum('total_cost');
        }

        // Incurred materials: bills incurred (or PO committed if bills not yet logged) + stock issues
        $actualMaterialsCost = round(($billsIncurredCost > 0 ? $billsIncurredCost : $poCommittedCost) + $stockRequisitionCost, 2);

        // 3. PILLAR 2: LABOR (FULLY BURDENED WITH NI + PENSION + OVERHEAD)
        $timesheets = LaborTimesheet::where('job_id', $jobId)->where('status', 'approved')->get();
        $laborTotalHours = (float)$timesheets->sum('total_hours');
        $laborDirectPay = (float)$timesheets->sum('direct_pay_amount');
        $laborBurdenedCost = (float)$timesheets->sum('fully_burdened_cost');

        // 4. PILLAR 3: SUBCONTRACTORS (CIS DEDUCTIONS)
        $subCertificates = SubcontractorPaymentCertificate::where('job_id', $jobId)
            ->whereIn('payment_status', ['certified', 'paid'])
            ->get();
        $subcontractorGrossCost = (float)$subCertificates->sum('gross_amount');
        $subcontractorCisTax = (float)$subCertificates->sum('cis_deduction_amount');
        $subcontractorNetPaid = (float)$subCertificates->sum('net_payable');

        // 5. PILLAR 4: PLANT & EQUIPMENT HIRE (ACCRUED COSTS)
        $plantHires = PlantHire::where('job_id', $jobId)
            ->whereIn('status', ['active', 'off_hired', 'pending_off_hire'])
            ->get();
        $plantAccruedCost = (float)$plantHires->sum('accrued_hire_cost');
        $activePlantCount = $plantHires->where('status', 'active')->count();

        // 6. TOTAL COSTS & PROFITABILITY
        $totalActualCost = round(
            $actualMaterialsCost +
            $laborBurdenedCost +
            $subcontractorGrossCost +
            $plantAccruedCost,
            2
        );

        $totalCommittedCost = round(
            max($poCommittedCost, $actualMaterialsCost) +
            $laborBurdenedCost +
            $subcontractorGrossCost +
            $plantAccruedCost,
            2
        );

        $liveGrossProfit = round($revisedContractSum - $totalActualCost, 2);
        $liveGrossMarginPercent = $revisedContractSum > 0
            ? round(($liveGrossProfit / $revisedContractSum) * 100, 2)
            : 0.00;

        // Commercial Status Flag
        $commercialStatus = 'healthy';
        if ($liveGrossMarginPercent < 20.00) {
            $commercialStatus = 'critical';
        } elseif ($liveGrossMarginPercent < 30.00) {
            $commercialStatus = 'warning';
        }

        // 7. WIP VALUATION (Work in Progress Asset vs Overbilling Liability)
        // WIP = Incurred Cost - Certified Revenue
        $wipBalance = round($totalActualCost - $totalCertifiedRevenue, 2);

        return [
            'job' => $job,
            'job_id' => $jobId,
            'job_name' => $job->job_title ?? $job->job_name,
            'customer_name' => $job->customer?->company_name ?? 'Client Direct',
            'commercial_status' => $commercialStatus,

            // Revenue & Contract
            'original_contract_value' => $originalContract,
            'approved_variations' => $approvedVariations,
            'pending_variations' => $pendingVariations,
            'revised_contract_sum' => $revisedContractSum,
            'total_certified_revenue' => $totalCertifiedRevenue,
            'total_cash_received' => $totalCashReceived,
            'retention_held' => $retentionHeld,

            // 4 Cost Pillars
            'pillars' => [
                'materials' => [
                    'po_committed' => $poCommittedCost,
                    'bills_incurred' => $billsIncurredCost,
                    'stock_issues' => $stockRequisitionCost,
                    'actual_cost' => $actualMaterialsCost,
                    'count_pos' => $purchaseOrders->count(),
                ],
                'labor' => [
                    'total_hours' => $laborTotalHours,
                    'direct_pay' => $laborDirectPay,
                    'burdened_cost' => $laborBurdenedCost,
                    'count_timesheets' => $timesheets->count(),
                ],
                'subcontractors' => [
                    'gross_cost' => $subcontractorGrossCost,
                    'cis_tax_withheld' => $subcontractorCisTax,
                    'net_payable' => $subcontractorNetPaid,
                    'count_certificates' => $subCertificates->count(),
                ],
                'plant' => [
                    'accrued_cost' => $plantAccruedCost,
                    'active_count' => $activePlantCount,
                    'count_hires' => $plantHires->count(),
                ],
            ],

            // Total Cost, Margin & WIP
            'total_actual_cost' => $totalActualCost,
            'total_committed_cost' => $totalCommittedCost,
            'live_gross_profit' => $liveGrossProfit,
            'live_gross_margin_percent' => $liveGrossMarginPercent,
            'wip_balance' => $wipBalance,
            'wip_type' => $wipBalance >= 0 ? 'Unbilled WIP (Asset)' : 'Billing in Excess of Cost (Liability)',
        ];
    }

    /**
     * Get aggregate Commercial Portfolio Overview across all active construction jobs.
     */
    public function getCommercialPortfolioOverview(?int $businessId = null): array
    {
        $query = Fjob::query();
        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $jobs = $query->latest('id')->limit(50)->get();

        $portfolio = [];
        $totalContractSum = 0.00;
        $totalActualCosts = 0.00;
        $totalCertifiedRevenue = 0.00;
        $totalWipAsset = 0.00;
        $healthyCount = 0;
        $warningCount = 0;
        $criticalCount = 0;

        foreach ($jobs as $job) {
            $jobWip = $this->computeJobWip($job->id);
            $portfolio[] = $jobWip;

            $totalContractSum += $jobWip['revised_contract_sum'];
            $totalActualCosts += $jobWip['total_actual_cost'];
            $totalCertifiedRevenue += $jobWip['total_certified_revenue'];
            $totalWipAsset += max(0, $jobWip['wip_balance']);

            if ($jobWip['commercial_status'] === 'healthy') {
                $healthyCount++;
            } elseif ($jobWip['commercial_status'] === 'warning') {
                $warningCount++;
            } else {
                $criticalCount++;
            }
        }

        $totalProfit = round($totalContractSum - $totalActualCosts, 2);
        $averageMargin = $totalContractSum > 0
            ? round(($totalProfit / $totalContractSum) * 100, 2)
            : 0.00;

        return [
            'total_jobs_tracked' => count($portfolio),
            'total_contract_sum' => round($totalContractSum, 2),
            'total_actual_costs' => round($totalActualCosts, 2),
            'total_gross_profit' => $totalProfit,
            'average_margin_percent' => $averageMargin,
            'total_certified_revenue' => round($totalCertifiedRevenue, 2),
            'total_wip_asset' => round($totalWipAsset, 2),
            'health_breakdown' => [
                'healthy' => $healthyCount,
                'warning' => $warningCount,
                'critical' => $criticalCount,
            ],
            'jobs' => $portfolio,
        ];
    }
}
