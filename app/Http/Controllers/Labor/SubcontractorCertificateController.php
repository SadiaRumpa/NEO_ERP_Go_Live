<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\Subcontractor;
use App\Models\SubcontractorPaymentCertificate;
use App\Services\Labor\LaborCisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubcontractorCertificateController extends Controller
{
    public function __construct(
        protected LaborCisService $laborCisService
    ) {}

    public function index(Request $request): View
    {
        $query = SubcontractorPaymentCertificate::with(['subcontractor', 'job', 'certifier']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        if ($request->filled('subcontractor_id')) {
            $query->where('subcontractor_id', $request->input('subcontractor_id'));
        }

        if ($request->filled('tax_month')) {
            $query->where('tax_month', $request->input('tax_month'));
        }

        $certificates = $query->latest('id')->paginate(15);
        $subcontractors = Subcontractor::where('status', 'active')->orderBy('company_name')->get();

        return view('subcontractors.certificates.index', compact('certificates', 'subcontractors'));
    }

    public function create(Request $request): View
    {
        $subcontractors = Subcontractor::where('status', 'active')->orderBy('company_name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();
        $selectedSubId = $request->input('subcontractor_id');

        return view('subcontractors.certificates.create', compact('subcontractors', 'jobs', 'selectedSubId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'job_id' => 'required|exists:fjobs,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'gross_amount' => 'required|numeric|min:0.01',
            'materials_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $cert = $this->laborCisService->createPaymentCertificate($validated, $request->user());

        return redirect()->route('subcontractors.certificates.show', $cert)
            ->with('success', 'Subcontractor Payment Certificate ' . $cert->certificate_number . ' generated with CIS deduction £' . number_format($cert->cis_deduction_amount, 2));
    }

    public function show(SubcontractorPaymentCertificate $certificate): View
    {
        $certificate->load(['subcontractor', 'job', 'certifier']);
        return view('subcontractors.certificates.show', compact('certificate'));
    }

    public function markPaid(Request $request, SubcontractorPaymentCertificate $certificate): RedirectResponse
    {
        $certificate->update([
            'payment_status' => 'paid',
            'paid_at' => now()->toDateString(),
        ]);

        return redirect()->route('subcontractors.certificates.show', $certificate)
            ->with('success', 'Payment Certificate ' . $certificate->certificate_number . ' marked as PAID.');
    }

    public function cisMonthlyReturn(Request $request): View
    {
        $taxMonth = $request->input('tax_month', date('Y-m'));
        $return = $this->laborCisService->generateCis300MonthlyReturn($taxMonth);

        return view('subcontractors.cis_monthly_return', compact('return', 'taxMonth'));
    }
}
