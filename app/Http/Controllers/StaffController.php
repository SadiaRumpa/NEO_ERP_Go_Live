<?php

namespace App\Http\Controllers;

use App\Models\JobScheduleWorker;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function indexTasks(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $data = JobScheduleWorker::with(['jobSchedule'])
                ->select('job_schedule_workers.*')
                ->orderBy('created_at', 'desc')
                ->where('user_id', auth()->id());

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('instructions', function ($row) {
                    return $row->jobSchedule ? Str::limit(strip_tags($row->jobSchedule->instructions ?? ''), 80) : 'N/A';
                })
                ->orderColumn('instructions', function ($query, $order) {
                    $query->leftJoin('job_schedules', 'job_schedule_workers.job_schedule_id', '=', 'job_schedules.id')
                          ->orderBy('job_schedules.instructions', $order);
                })
                ->filterColumn('instructions', function ($query, $keyword) {
                    $query->whereHas('jobSchedule', function ($q) use ($keyword) {
                        $q->where('instructions', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('job', function ($row) {
                    return $row->jobSchedule ? (($row->jobSchedule->job_name ?? '') . ' - ' . ($row->jobSchedule->job_title ?? '')) : 'N/A';
                })
                ->orderColumn('job', function ($query, $order) {
                    $query->leftJoin('job_schedules', 'job_schedule_workers.job_schedule_id', '=', 'job_schedules.id')
                          ->orderBy('job_schedules.job_name', $order)->orderBy('job_schedules.job_title', $order);
                })
                ->filterColumn('job', function ($query, $keyword) {
                    $query->whereHas('jobSchedule', function ($q) use ($keyword) {
                        $q->where('job_name', 'like', "%{$keyword}%")
                          ->orWhere('job_title', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('timeline', function ($row) {
                    if (!$row->jobSchedule || !$row->jobSchedule->start_date) {
                        return 'N/A';
                    }
                    $start = Carbon::parse($row->jobSchedule->start_date)->format('d M Y');
                    $end = Carbon::parse($row->jobSchedule->end_date ?? $row->jobSchedule->start_date)->format('d M Y');
                    return $start . ' - ' . $end;
                })
                ->orderColumn('timeline', function ($query, $order) {
                    $query->leftJoin('job_schedules', 'job_schedule_workers.job_schedule_id', '=', 'job_schedules.id')
                          ->orderBy('job_schedules.start_date', $order);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('tasks.show', $row->id) . '" class="edit btn btn-primary btn-sm">View Job</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('tasks.index');
    }

    public function showTasks($task_id)
    {
        $taskdetails = JobScheduleWorker::with([
            'jobSchedule',
            'jobSchedule.job',
            'jobSchedule.workers',
            'jobSchedule.attachments',
            'jobSchedule.job.jobaddress',
            'jobSchedule.job.jobimages',
            'jobSchedule.job.jobaddress'
        ])->findOrFail($task_id);
        return view('tasks.show', compact('taskdetails'));
    }

    public function updateTasks(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:job_schedule_workers,id',
            'status' => 'required|string|in:inprogress,completed,rejected',
        ]);

        $task = JobScheduleWorker::findOrFail($request->task_id);
        $task->status = $request->status;
        $task->save();

        return back()->with('success', 'Task status updated successfully.');
    }
}
