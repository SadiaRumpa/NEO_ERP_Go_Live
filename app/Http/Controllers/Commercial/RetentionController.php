<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\JobRetention;
use App\Services\Commercial\CommercialContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetentionController extends Controller
{
    public function __construct(
        protected CommercialContractService $commercialService
    ) {}

    public function index(Request $request): View
    {
        $query = JobRetention::with(['job', 'valuation']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        $retentions = $query->latest('id')->paginate($this->getPerPage(15))->withQueryString();
        $jobs = Fjob::latest('id')->limit(50)->get();

        $totalHeld = JobRetention::whereIn('status', ['held', 'partially_released'])->get()->sum(fn($r) => $r->balance_held);
        $totalReleased = JobRetention::sum('total_retained_amount') - $totalHeld;

        return view('commercial.retentions.index', compact('retentions', 'jobs', 'totalHeld', 'totalReleased'));
    }

    public function releasePc(Request $request, JobRetention $retention): RedirectResponse
    {
        $this->commercialService->releasePracticalCompletionRetention($retention);

        return redirect()->route('commercial.retentions.index')
            ->with('success', 'Practical Completion retention release (£' . number_format($retention->pc_release_amount, 2) . ') authorized.');
    }

    public function releaseDefects(Request $request, JobRetention $retention): RedirectResponse
    {
        $this->commercialService->releaseDefectsLiabilityRetention($retention);

        return redirect()->route('commercial.retentions.index')
            ->with('success', 'Defects Liability retention release (£' . number_format($retention->defects_release_amount, 2) . ') authorized.');
    }
}
