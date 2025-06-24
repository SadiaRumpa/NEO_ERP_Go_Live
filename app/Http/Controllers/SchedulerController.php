<?php

namespace App\Http\Controllers;

use App\Models\Fjob;
use App\Models\JobSchedule;
use App\Models\JobScheduleWorker;
use App\Models\User;
use Illuminate\Http\Request;
use DateInterval;
use DatePeriod;
use Carbon\Carbon;
use DB;

class SchedulerController extends Controller
{

    public function index(Request $request)
    {
        $allMonths = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $selectedMonths = $request->get('months', [now()->format('F')]);

        $data = $this->generateCalendarData($selectedMonths);
        $jobs = Fjob::whereIn('job_status',  ['accepted', 'inprogress', 'inprogress'])
            ->get();
        $stuffs = User::role('stuff')->get();
        return view('scheduler.index', array_merge($data, [
            'allMonths' => $allMonths,
            'selectedMonths' => $selectedMonths,
            'jobs' => $jobs,
            'stuffs' => $stuffs,
        ]));
    }

    public function getSchedulerCalendarData(Request $request)
    {
        $selectedMonths = $request->get('months', []);
        $data = $this->generateCalendarData($selectedMonths);

        $html = view('partials.worker_calendar_table', $data)->render();

        return response()->json(['html' => $html]);
    }

    private function generateCalendarData(array $selectedMonths)
    {
        $startYear = now()->year;
        $datesByMonth = [];

        foreach ($selectedMonths as $month) {
            $start = Carbon::parse("first day of $month $startYear");
            $end = Carbon::parse("last day of $month $startYear");

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $datesByMonth[$month][] = $date->toDateString(); // Carbon instance, safe to use
            }
        }

        $workers = User::role('stuff')->get();

        // Normalize selectedMonths to match PostgreSQL 'Month' format (capitalized first letter)
        $normalizedMonths = array_map(fn($m) => ucfirst(strtolower($m)), $selectedMonths);

        // Use PostgreSQL-friendly month name filter
        $assignments = JobScheduleWorker::with('jobSchedule')
            ->whereIn(DB::raw("TRIM(TO_CHAR(assigned_date, 'Month'))"), $normalizedMonths)
            ->get()
            ->groupBy('worker_id');

        return compact('datesByMonth', 'workers', 'assignments');
    }


    public function storeJobSchedule(Request $request)
    {
        $validated = $request->validate([
            'job_id'      => 'required|exists:fjobs,id',
            'daterange'   => 'required|string',
            'workers'     => 'array',
            'workers.*'   => 'exists:users,id',
            'instructions' => 'nullable|string',
        ]);

        // Extract start and end dates
        [$startRaw, $endRaw] = explode(' to ', $validated['daterange']);
        $startDate = Carbon::parse($startRaw)->toDateString();
        $endDate   = Carbon::parse($endRaw)->toDateString();

        // Validate date range
        if ($startDate >= $endDate) {
            return response()->json(['error' => 'Start date must be before end date'], 422);
        }

        // Create the Job Schedule
        $jobSchedule = JobSchedule::create([
            'job_id'      => $validated['job_id'],
            'job_name'    => Fjob::find($validated['job_id'])->job_name,
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'instructions' => $validated['instructions'],
            'status'      => 'pending',
        ]);

        // Assign workers (if any)
        foreach ($validated['workers'] ?? [] as $workerId) {
            JobScheduleWorker::create([
                'job_schedule_id' => $jobSchedule->id,
                'worker_id'       => $workerId,
                'assigned_date'   => $startDate,
                'status'          => 'assigned',
            ]);
        }

        return response()->json([
            'message'      => 'Job schedule created successfully',
            'job_schedule' => $jobSchedule
        ], 201);
    }
}
