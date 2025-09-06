<?php

namespace App\Http\Controllers;

use App\Models\Fjob;
use App\Models\FjobProgress;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FjobProgressController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = FjobProgress::with(['tasks'])->select('fjob_progresses.*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('job-overviews.show', $row->id).'" class="edit btn btn-primary btn-sm">View</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('job_overviews.index');
    }

    public function create(){
        $fjob = Fjob::where('status', '!=', 'Completed')->get();
    }

}
