<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Services\Wip\JobCostingWipService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobCostingController extends Controller
{
    public function __construct(
        protected JobCostingWipService $wipService
    ) {}

    /**
     * Commercial Portfolio Dashboard (All Projects Live Costing & WIP).
     */
    public function portfolio(Request $request): View
    {
        $overview = $this->wipService->getCommercialPortfolioOverview($request->user()->business_id);
        return view('commercial.wip.portfolio', compact('overview'));
    }

    /**
     * Live Job Costing & WIP Ledger for an individual project.
     */
    public function show(Fjob $job): View
    {
        $wip = $this->wipService->computeJobWip($job->id);

        // Fetch detailed itemized rows for all 4 pillars
        $job->load([
            'customer',
            'purchaseOrders.supplier',
            'laborTimesheets.worker',
            'subcontractorCertificates.subcontractor',
            'plantHires.supplier',
            'variations',
            'valuations',
            'retentions',
        ]);

        return view('commercial.wip.show', compact('job', 'wip'));
    }
}
