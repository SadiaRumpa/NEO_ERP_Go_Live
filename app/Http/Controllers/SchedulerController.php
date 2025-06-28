<?php

namespace App\Http\Controllers;

use App\Models\Fjob;
use App\Models\JobSchedule;
use App\Models\JobScheduleAttachment;
use App\Models\JobScheduleWorker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Container\Attributes\Storage;
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
        $jobs = Fjob::whereIn('job_status',  ['accepted', 'inprogress'])
            ->get();
        $stuffs = User::role('stuff')->orderBy('name', 'asc')->get();
        // return $jobs;
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
                $datesByMonth[$month][] = $date->toDateString();
            }
        }

        $workers = User::role('stuff')->orderBy('name', 'asc')->get();

        // Fetch all assignments in the selected months (based on assigned_date or completion_date range)
        $assignments = JobScheduleWorker::with('jobSchedule')
            ->whereMonth('assigned_date', '>=', Carbon::parse('first day of ' . $selectedMonths[0])->month)
            ->whereMonth('completion_date', '<=', Carbon::parse('last day of ' . end($selectedMonths))->month)
            ->get();

        // Build a lookup: [user_id][date] = assignment
        $assignmentMap = [];

        foreach ($assignments as $assignment) {
            $start = Carbon::parse($assignment->assigned_date);
            $end = Carbon::parse($assignment->completion_date);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $assignmentMap[$assignment->user_id][$date->toDateString()][] = $assignment;
            }
        }

        return compact('datesByMonth', 'workers', 'assignmentMap');
    }

    public function showSchedule($id)
    {
        $schedule = JobSchedule::with([
            'job' => function ($q) {
                $q->select('id', 'job_description', 'customer_id', 'job_type', 'job_status');
            },
            'job.jobaddress' => function ($q) {
                $q->select('id', 'fjob_id', 'address', 'country', 'county', 'contact_firstname', 'contact_lastname', 'contact_phone', 'contact_mobile', 'contact_email');
            },
            'job.customer' => function ($q) {
                $q->select('id', 'company_name');
            },
            'workers'
        ])->findOrFail($id);

        $workerslist = User::role('stuff')->orderBy('name', 'asc')->get();

        return response()->json([
            'schedule' => $schedule,
            'workers' => $schedule->workers,
            'job' => $schedule->job,
            'customer' => $schedule->job->customer,
            'jobAddress' => $schedule->job->jobaddress,
            'workerslist' => $workerslist,
        ]);
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
        $dateParts = explode(' to ', $validated['daterange']);
        $startDate = Carbon::parse($dateParts[0])->toDateString();
        $endDate = isset($dateParts[1])
            ? Carbon::parse($dateParts[1])->toDateString()
            : $startDate; // Single date

        // Validate date logic
        if ($startDate > $endDate) {
            return response()->json(['error' => 'Start date must be before end date'], 422);
        }



        // ✅ Check for duplicate job schedule with same job_id, workers, and date range
        $workerIds = $validated['workers'] ?? [];

        $existing = JobSchedule::where('job_id', $validated['job_id'])
            ->whereDate('start_date', $startDate)
            ->whereDate('end_date', $endDate)
            ->whereHas('workers', function ($query) use ($workerIds) {
                $query->whereIn('user_id', $workerIds);
            })
            ->whereRaw("
            (
                SELECT COUNT(*) 
                FROM job_schedule_workers 
                WHERE job_schedule_id = job_schedules.id 
                AND user_id IN (" . implode(',', array_fill(0, count($workerIds), '?')) . ")
            ) = ?
            ", array_merge($workerIds, [count($workerIds)]))
            ->first();
        // dd($existing);
        if ($existing) {
            return response()->json(['error' => 'Duplicate job schedule already exists'], 409);
        }

        // ✅ Create job schedule
        $jobSchedule = JobSchedule::create([
            'job_id'       => $validated['job_id'],
            'job_name'     => Fjob::find($validated['job_id'])->job_name,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'instructions' => $validated['instructions'],
            'status'       => 'pending',
        ]);

        // ✅ Assign workers
        foreach ($workerIds as $workerId) {
            JobScheduleWorker::create([
                'job_schedule_id' => $jobSchedule->id,
                'user_id'         => $workerId,
                'assigned_date'   => $startDate,
                'completion_date' => $endDate,
                'status'          => 'assigned',
            ]);
        }

        return response()->json([
            'message'      => 'Job schedule created successfully',
            'job_schedule' => $jobSchedule
        ], 201);
    }


    public function updateJobSchedule(Request $request, $id)
    {
        // dd($request->all());
        $validated = $request->validate([
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

        // Find the Job Schedule
        $jobSchedule = JobSchedule::findOrFail($id);
        $jobSchedule->update([
            'start_date'  => $startDate,
            'end_date'    => $endDate,
            'instructions' => $validated['instructions'],
            'status'      => 'pending',
        ]);

        // Update workers
        JobScheduleWorker::where('job_schedule_id', $id)->delete();
        foreach ($validated['workers'] ?? [] as $workerId) {
            JobScheduleWorker::create([
                'job_schedule_id' => $jobSchedule->id,
                'user_id'       => $workerId,
                'assigned_date'   => $startDate,
                'completion_date' => $endDate,
                'status'          => 'assigned',
            ]);
        }

        return response()->json([
            'message'      => 'Job schedule updated successfully',
        ]);
    }

    public function deleteSchedule(Request $request, $id)
    {
        $schedule = JobSchedule::find($id);

        if (!$schedule) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        // Delete associated workers
        JobScheduleWorker::where('job_schedule_id', $id)->delete();

        // Handle attachments
        $attachments = JobScheduleAttachment::where('job_schedule_id', $id)->get();
        foreach ($attachments as $attachment) {
            $disk = $attachment->source === 's3' ? 's3' : 'public';
            $path = $attachment->attachment_path;

            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            $attachment->delete();
        }

        // Finally, delete the schedule
        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted successfully']);
    }
}
