<?php

namespace App\Services\Commercial;

use App\Models\Fjob;
use App\Models\JobRetention;
use App\Models\JobValuation;
use App\Models\JobVariation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommercialContractService
{
    /**
     * Create a Contract Variation (Change Order).
     */
    public function createVariation(array $data, ?User $user = null): JobVariation
    {
        return DB::transaction(function () use ($data, $user) {
            $year = date('Y');
            $count = JobVariation::whereYear('created_at', $year)->count() + 1;
            $varNumber = 'VAR-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $cost = (float)($data['cost_amount'] ?? 0.00);
            $markup = (float)($data['markup_percentage'] ?? 20.00);

            if (isset($data['quoted_amount']) && (float)$data['quoted_amount'] > 0) {
                $quoted = (float)$data['quoted_amount'];
            } else {
                $quoted = round($cost * (1 + ($markup / 100)), 2);
            }

            return JobVariation::create([
                'business_id' => $user?->business_id ?? $data['business_id'] ?? null,
                'variation_number' => $data['variation_number'] ?? $varNumber,
                'job_id' => $data['job_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'cost_amount' => $cost,
                'markup_percentage' => $markup,
                'quoted_amount' => $quoted,
                'status' => 'draft',
            ]);
        });
    }

    /**
     * Approve Variation with client digital e-signature.
     */
    public function approveVariation(
        JobVariation $variation,
        ?string $signerName = null,
        ?string $signerEmail = null,
        ?string $signatureData = null,
        ?User $internalApprover = null
    ): JobVariation {
        return DB::transaction(function () use ($variation, $signerName, $signerEmail, $signatureData, $internalApprover) {
            $variation->update([
                'status' => 'approved',
                'client_signer_name' => $signerName ?? 'Client Representative',
                'client_signer_email' => $signerEmail,
                'signature_data' => $signatureData ?? 'DATA_URL_ESIGN_RECORD',
                'signed_at' => now(),
                'approved_by' => $internalApprover?->id ?? auth()->id(),
                'approved_at' => now(),
            ]);

            return $variation->fresh(['job', 'approver']);
        });
    }

    /**
     * Reject Variation with client or commercial rationale.
     */
    public function rejectVariation(JobVariation $variation, string $reason): JobVariation
    {
        $variation->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return $variation->fresh(['job']);
    }

    /**
     * Create an Application for Payment (Progress Valuation).
     */
    public function createValuation(array $data, ?User $user = null): JobValuation
    {
        return DB::transaction(function () use ($data, $user) {
            $year = date('Y');
            $count = JobValuation::whereYear('created_at', $year)->count() + 1;
            $appNumber = 'APP-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $cumulativeWork = (float)($data['cumulative_work_value'] ?? 0.00);
            $materialsOnSite = (float)($data['materials_on_site_value'] ?? 0.00);
            $grossValuation = round($cumulativeWork + $materialsOnSite, 2);

            // Previous certified or applied
            $previousCertified = (float)($data['previous_certified_amount'] ?? 0.00);
            if ($previousCertified == 0.00) {
                // Auto-lookup previous valuation on this job if not manually supplied
                $prev = JobValuation::where('job_id', $data['job_id'])
                    ->whereIn('status', ['certified', 'paid'])
                    ->latest('id')
                    ->first();
                $previousCertified = $prev ? (float)$prev->gross_valuation : 0.00;
            }

            $currentGross = max(0.00, round($grossValuation - $previousCertified, 2));

            $retentionRate = isset($data['retention_rate']) ? (float)$data['retention_rate'] : 5.00;
            $retentionDeduction = round($currentGross * ($retentionRate / 100), 2);
            $netClaim = max(0.00, round($currentGross - $retentionDeduction, 2));

            $valuation = JobValuation::create([
                'business_id' => $user?->business_id ?? $data['business_id'] ?? null,
                'application_number' => $data['application_number'] ?? $appNumber,
                'job_id' => $data['job_id'],
                'valuation_date' => $data['valuation_date'] ?? now()->toDateString(),
                'period_end' => $data['period_end'] ?? now()->toDateString(),
                'cumulative_work_value' => $cumulativeWork,
                'materials_on_site_value' => $materialsOnSite,
                'gross_valuation' => $grossValuation,
                'previous_certified_amount' => $previousCertified,
                'current_gross_amount' => $currentGross,
                'retention_rate' => $retentionRate,
                'retention_deduction' => $retentionDeduction,
                'net_claim_amount' => $netClaim,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            // If retention is deducted, create a JobRetention ledger entry
            if ($retentionDeduction > 0) {
                $retCount = JobRetention::whereYear('created_at', $year)->count() + 1;
                $retRef = 'RET-' . $year . '-' . str_pad($retCount, 4, '0', STR_PAD_LEFT);

                // 50% on Practical Completion, 50% on Defects Liability (12 months post-PC)
                $pcAmount = round($retentionDeduction / 2, 2);
                $defectsAmount = round($retentionDeduction - $pcAmount, 2);

                JobRetention::create([
                    'business_id' => $valuation->business_id,
                    'job_id' => $valuation->job_id,
                    'valuation_id' => $valuation->id,
                    'retention_reference' => $retRef,
                    'total_retained_amount' => $retentionDeduction,
                    'pc_release_amount' => $pcAmount,
                    'defects_release_amount' => $defectsAmount,
                    'status' => 'held',
                ]);
            }

            return $valuation;
        });
    }

    /**
     * Certify Valuation (Payment Certificate from Employer's Agent / Architect).
     */
    public function certifyValuation(
        JobValuation $valuation,
        ?float $certifiedAmount = null,
        ?User $certifier = null
    ): JobValuation {
        $amount = $certifiedAmount !== null ? $certifiedAmount : (float)$valuation->net_claim_amount;

        $valuation->update([
            'status' => 'certified',
            'certified_amount' => $amount,
            'certified_at' => now(),
            'certified_by' => $certifier?->id ?? auth()->id(),
        ]);

        return $valuation->fresh(['job', 'certifier', 'retention']);
    }

    /**
     * Release Retention at Practical Completion (50% release).
     */
    public function releasePracticalCompletionRetention(JobRetention $retention, ?Carbon $pcDate = null): JobRetention
    {
        $retention->update([
            'practical_completion_date' => $pcDate ?? now()->toDateString(),
            'pc_released_at' => now(),
            'defects_expiry_date' => ($pcDate ?? now())->copy()->addYear()->toDateString(),
            'status' => $retention->balance_held <= $retention->pc_release_amount ? 'partially_released' : 'partially_released',
        ]);

        if ($retention->balance_held <= 0) {
            $retention->update(['status' => 'fully_released']);
        }

        return $retention;
    }

    /**
     * Release Retention at Defects Liability Expiry (remaining 50% release).
     */
    public function releaseDefectsLiabilityRetention(JobRetention $retention): JobRetention
    {
        $retention->update([
            'defects_released_at' => now(),
            'status' => 'fully_released',
        ]);

        return $retention;
    }

    /**
     * Aggregate full commercial financial position for a Job.
     */
    public function getJobCommercialSummary(int $jobId): array
    {
        $job = Fjob::find($jobId);

        // Variations
        $approvedVariations = (float)JobVariation::where('job_id', $jobId)
            ->where('status', 'approved')
            ->sum('quoted_amount');

        $pendingVariations = (float)JobVariation::where('job_id', $jobId)
            ->whereIn('status', ['draft', 'submitted'])
            ->sum('quoted_amount');

        // Accepted Quotation Value (Original Contract Sum)
        $originalContract = 0.00;
        if ($job && method_exists($job, 'quotations')) {
            $acceptedQuote = $job->quotations()->where('status', 'accepted')->first();
            $originalContract = $acceptedQuote ? (float)$acceptedQuote->total : 0.00;
        }

        $revisedContractSum = round($originalContract + $approvedVariations, 2);

        // Valuations
        $valuations = JobValuation::where('job_id', $jobId)->get();
        $totalGrossValued = (float)$valuations->max('gross_valuation') ?? 0.00;
        $totalCertifiedNet = (float)$valuations->whereIn('status', ['certified', 'paid'])->sum('certified_amount');
        $totalRetentionDeducted = (float)$valuations->sum('retention_deduction');

        // Retentions Ledger
        $retentions = JobRetention::where('job_id', $jobId)->get();
        $totalRetained = (float)$retentions->sum('total_retained_amount');
        $retentionHeld = (float)$retentions->sum(fn($r) => $r->balance_held);

        return [
            'job_id' => $jobId,
            'original_contract_value' => $originalContract,
            'approved_variations_total' => $approvedVariations,
            'pending_variations_total' => $pendingVariations,
            'revised_contract_sum' => $revisedContractSum,
            'total_gross_valued' => $totalGrossValued,
            'total_certified_net' => $totalCertifiedNet,
            'total_retention_deducted' => $totalRetentionDeducted,
            'retention_held' => $retentionHeld,
        ];
    }
}
