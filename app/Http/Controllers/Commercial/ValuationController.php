<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\JobValuation;
use App\Services\Commercial\CommercialContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ValuationController extends Controller
{
    public function __construct(
        protected CommercialContractService $commercialService
    ) {}

    public function index(Request $request): View
    {
        $query = JobValuation::with(['job', 'certifier', 'retention']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        $valuations = $query->latest('id')->paginate($this->getPerPage(15))->withQueryString();
        $jobs = Fjob::latest('id')->limit(50)->get();

        return view('commercial.valuations.index', compact('valuations', 'jobs'));
    }

    public function create(Request $request): View
    {
        $jobs = Fjob::latest('id')->limit(50)->get();
        $selectedJobId = $request->input('job_id');

        return view('commercial.valuations.create', compact('jobs', 'selectedJobId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:fjobs,id',
            'valuation_date' => 'required|date',
            'period_end' => 'required|date|after_or_equal:valuation_date',
            'cumulative_work_value' => 'required|numeric|min:0',
            'materials_on_site_value' => 'nullable|numeric|min:0',
            'previous_certified_amount' => 'nullable|numeric|min:0',
            'retention_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $valuation = $this->commercialService->createValuation($validated, $request->user());

        return redirect()->route('commercial.valuations.show', $valuation)
            ->with('success', 'Application for Payment ' . $valuation->application_number . ' generated with net claim £' . number_format($valuation->net_claim_amount, 2));
    }

    public function show(JobValuation $valuation): View
    {
        $valuation->load(['job', 'certifier', 'retention']);
        return view('commercial.valuations.show', compact('valuation'));
    }

    public function certify(Request $request, JobValuation $valuation): RedirectResponse
    {
        $validated = $request->validate([
            'certified_amount' => 'required|numeric|min:0',
        ]);

        $this->commercialService->certifyValuation($valuation, (float)$validated['certified_amount'], $request->user());

        return redirect()->route('commercial.valuations.show', $valuation)
            ->with('success', 'Application ' . $valuation->application_number . ' certified for payment.');
    }

    public function markPaid(Request $request, JobValuation $valuation): RedirectResponse
    {
        $valuation->update([
            'status' => 'paid',
            'paid_at' => now()->toDateString(),
        ]);

        return redirect()->route('commercial.valuations.show', $valuation)
            ->with('success', 'Application ' . $valuation->application_number . ' marked as paid.');
    }
}
