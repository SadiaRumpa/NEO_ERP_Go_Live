<?php

namespace App\Http\Controllers;

use App\Models\JobScheduleWorker;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;


class StaffController extends Controller
{
    public function indexTasks(Request $request)
    {
        if ($request->ajax()) {

            $data = JobScheduleWorker::with(['jobSchedule'])
                ->orderBy('created_at', 'desc')
                ->where('user_id', auth()->id());

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('instructions', function ($row) {
                    return $row->jobSchedule->instructions;
                })
                ->addColumn('job', function ($row) {
                    return $row->jobSchedule->job_name . '-' . $row->jobSchedule->job_title;
                })
                ->addColumn('timeline', function ($row) {
                    $start = Carbon::parse($row->jobSchedule->start_date)->format('d M Y');
                    $end = Carbon::parse($row->jobSchedule->end_date)->format('d M Y');
                    return $start . ' - ' . $end;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('job-overviews.show', $row->id) . '" class="edit btn btn-primary btn-sm">View Job</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('tasks.index');
    }
}
