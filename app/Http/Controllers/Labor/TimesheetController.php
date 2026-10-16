<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\LaborTimesheet;
use App\Models\User;
use App\Services\Labor\LaborCisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimesheetController extends Controller
{
    public function __construct(
        protected LaborCisService $laborCisService
    ) {}

    public function index(Request $request): View
    {
        $query = LaborTimesheet::with(['worker', 'job', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        $timesheets = $query->latest('work_date')->paginate($this->getPerPage(15))->withQueryString();
        $workers = User::orderBy('name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();

        return view('labor.timesheets.index', compact('timesheets', 'workers', 'jobs'));
    }

    public function create(): View
    {
        $workers = User::orderBy('name')->get();
        $jobs = Fjob::latest('id')->limit(50)->get();
        return view('labor.timesheets.create', compact('workers', 'jobs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'job_id' => 'required|exists:fjobs,id',
            'work_date' => 'required|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'travel_hours' => 'nullable|numeric|min:0',
            'standard_hours' => 'required|numeric|min:0',
            'overtime_1_5_hours' => 'nullable|numeric|min:0',
            'overtime_2_0_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $worker = User::findOrFail($validated['user_id']);
        $timesheet = $this->laborCisService->submitTimesheet($validated, $worker);

        return redirect()->route('labor.timesheets.show', $timesheet)
            ->with('success', 'Timesheet ' . $timesheet->timesheet_number . ' submitted successfully.');
    }

    public function show(LaborTimesheet $timesheet): View
    {
        $timesheet->load(['worker', 'job', 'approver']);
        return view('labor.timesheets.show', compact('timesheet'));
    }

    public function approve(Request $request, LaborTimesheet $timesheet): RedirectResponse
    {
        $this->laborCisService->approveTimesheet($timesheet, $request->user());

        return redirect()->route('labor.timesheets.show', $timesheet)
            ->with('success', 'Timesheet ' . $timesheet->timesheet_number . ' approved. Fully burdened labor cost allocated to Job WIP.');
    }

    public function reject(Request $request, LaborTimesheet $timesheet): RedirectResponse
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $timesheet->update([
            'status' => 'rejected',
            'notes' => trim(($timesheet->notes ?? '') . "\n[REJECTED]: " . $request->input('rejection_reason')),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('labor.timesheets.show', $timesheet)
            ->with('warning', 'Timesheet marked as rejected.');
    }
}
