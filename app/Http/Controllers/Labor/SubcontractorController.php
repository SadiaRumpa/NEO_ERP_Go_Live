<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\Subcontractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubcontractorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subcontractor::withCount('paymentCertificates');

        if ($request->filled('cis_tax_status')) {
            $query->where('cis_tax_status', $request->input('cis_tax_status'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('utr_number', 'like', "%{$s}%")
                  ->orWhere('contact_name', 'like', "%{$s}%")
                  ->orWhere('trade_specialism', 'like', "%{$s}%");
            });
        }

        $subcontractors = $query->latest('id')->paginate(15);

        return view('subcontractors.index', compact('subcontractors'));
    }

    public function create(): View
    {
        return view('subcontractors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'utr_number' => 'required|string|size:10|regex:/^[0-9]+$/',
            'company_registration_number' => 'nullable|string|max:50',
            'national_insurance_number' => 'nullable|string|max:50',
            'cis_tax_status' => 'required|in:gross,standard,higher',
            'cis_verification_number' => 'nullable|string|max:50',
            'verified_at' => 'nullable|date',
            'trade_specialism' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $subcontractor = Subcontractor::create($validated);

        return redirect()->route('subcontractors.show', $subcontractor)
            ->with('success', 'Subcontractor registered with HMRC CIS tax rate ' . $subcontractor->cis_tax_rate . '%.');
    }

    public function show(Subcontractor $subcontractor): View
    {
        $subcontractor->load(['paymentCertificates.job']);
        return view('subcontractors.show', compact('subcontractor'));
    }

    public function edit(Subcontractor $subcontractor): View
    {
        return view('subcontractors.edit', compact('subcontractor'));
    }

    public function update(Request $request, Subcontractor $subcontractor): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'utr_number' => 'required|string|size:10|regex:/^[0-9]+$/',
            'company_registration_number' => 'nullable|string|max:50',
            'national_insurance_number' => 'nullable|string|max:50',
            'cis_tax_status' => 'required|in:gross,standard,higher',
            'cis_verification_number' => 'nullable|string|max:50',
            'verified_at' => 'nullable|date',
            'trade_specialism' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $subcontractor->update($validated);

        return redirect()->route('subcontractors.show', $subcontractor)
            ->with('success', 'Subcontractor CIS profile updated.');
    }
}
