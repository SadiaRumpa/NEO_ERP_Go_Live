<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\JobVariation;
use App\Services\Commercial\CommercialContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VariationController extends Controller
{
    public function __construct(
        protected CommercialContractService $commercialService
    ) {}

    public function index(Request $request): View
    {
        $query = JobVariation::with(['job', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        $variations = $query->latest('id')->paginate(15);
        $jobs = Fjob::latest('id')->limit(50)->get();

        return view('commercial.variations.index', compact('variations', 'jobs'));
    }

    public function create(Request $request): View
    {
        $jobs = Fjob::latest('id')->limit(50)->get();
        $selectedJobId = $request->input('job_id');

        return view('commercial.variations.create', compact('jobs', 'selectedJobId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:fjobs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost_amount' => 'required|numeric|min:0',
            'markup_percentage' => 'nullable|numeric|min:0',
            'quoted_amount' => 'nullable|numeric|min:0',
        ]);

        $variation = $this->commercialService->createVariation($validated, $request->user());

        return redirect()->route('commercial.variations.show', $variation)
            ->with('success', 'Variation ' . $variation->variation_number . ' logged successfully.');
    }

    public function show(JobVariation $variation): View
    {
        $variation->load(['job', 'approver']);
        return view('commercial.variations.show', compact('variation'));
    }

    public function approve(Request $request, JobVariation $variation): RedirectResponse
    {
        $validated = $request->validate([
            'client_signer_name' => 'required|string|max:255',
            'client_signer_email' => 'nullable|email|max:255',
            'signature_data' => 'nullable|string',
        ]);

        $this->commercialService->approveVariation(
            $variation,
            $validated['client_signer_name'],
            $validated['client_signer_email'] ?? null,
            $validated['signature_data'] ?? null,
            $request->user()
        );

        return redirect()->route('commercial.variations.show', $variation)
            ->with('success', 'Variation ' . $variation->variation_number . ' approved with e-signature. Revised contract sum updated.');
    }

    public function reject(Request $request, JobVariation $variation): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $this->commercialService->rejectVariation($variation, $validated['rejection_reason']);

        return redirect()->route('commercial.variations.show', $variation)
            ->with('warning', 'Variation marked as rejected.');
    }
}
