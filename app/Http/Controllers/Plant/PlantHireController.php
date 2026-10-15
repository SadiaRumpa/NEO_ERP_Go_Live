<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\PlantHire;
use App\Models\Supplier;
use App\Services\Plant\PlantHireService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlantHireController extends Controller
{
    public function __construct(
        protected PlantHireService $plantHireService
    ) {}

    public function index(Request $request): View
    {
        $query = PlantHire::with(['job', 'supplier']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $plantHires = $query->latest('id')->paginate(15);
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();

        $alerts = $this->plantHireService->getExpiringOrOverdueHires($request->user()->business_id);

        return view('plant_hires.index', compact('plantHires', 'suppliers', 'jobs', 'alerts'));
    }

    public function create(Request $request): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();
        $selectedJobId = $request->input('job_id');

        return view('plant_hires.create', compact('suppliers', 'jobs', 'selectedJobId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:fjobs,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:scaffolding,mewp,telehandler,skips,tools,other',
            'serial_asset_tag' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'expected_end_date' => 'required|date|after_or_equal:start_date',
            'daily_rate' => 'nullable|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'collection_charge' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $hire = $this->plantHireService->createPlantHire($validated, $request->user());

        return redirect()->route('plant-hires.show', $hire)
            ->with('success', 'Plant hire record ' . $hire->hire_reference . ' booked successfully.');
    }

    public function show(PlantHire $plantHire): View
    {
        $plantHire->load(['job', 'supplier', 'offHiredByUser']);
        $currentAccrued = $plantHire->computeAccruedCost();

        return view('plant_hires.show', compact('plantHire', 'currentAccrued'));
    }

    public function edit(PlantHire $plantHire): View
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();

        return view('plant_hires.edit', compact('plantHire', 'suppliers', 'jobs'));
    }

    public function update(Request $request, PlantHire $plantHire): RedirectResponse
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:fjobs,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:scaffolding,mewp,telehandler,skips,tools,other',
            'serial_asset_tag' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'expected_end_date' => 'required|date|after_or_equal:start_date',
            'daily_rate' => 'nullable|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'collection_charge' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $plantHire->update($validated);
        $plantHire->accrued_hire_cost = $plantHire->computeAccruedCost();
        $plantHire->save();

        return redirect()->route('plant-hires.show', $plantHire)
            ->with('success', 'Plant hire record ' . $plantHire->hire_reference . ' updated.');
    }

    public function offHire(Request $request, PlantHire $plantHire): RedirectResponse
    {
        $validated = $request->validate([
            'off_hire_reference' => 'required|string|max:100',
            'actual_end_date' => 'required|date|after_or_equal:' . $plantHire->start_date->toDateString(),
            'notes' => 'nullable|string',
        ]);

        $this->plantHireService->offHirePlant(
            $plantHire,
            $validated['off_hire_reference'],
            $validated['actual_end_date'],
            $validated['notes'] ?? null,
            $request->user()
        );

        return redirect()->route('plant-hires.show', $plantHire)
            ->with('success', 'Plant ' . $plantHire->item_name . ' successfully off-hired with reference: ' . $validated['off_hire_reference']);
    }

    public function alerts(Request $request): View
    {
        $alerts = $this->plantHireService->getExpiringOrOverdueHires($request->user()->business_id);
        return view('plant_hires.alerts', compact('alerts'));
    }
}
