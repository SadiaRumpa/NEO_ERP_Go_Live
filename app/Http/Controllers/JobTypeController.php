<?php

namespace App\Http\Controllers;

use App\Models\JobType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JobTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = JobType::select('*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('jobtypes.edit', $row->id); // Edit Route
                    $deleteUrl = route('jobtypes.destroy', $row->id); // Delete Route

                    return '
                    <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm">Edit</a>
                    <button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('jobtypes.index');
    }

    public function create(){}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'typeName' => 'required|string|max:255',
            'jobTypeDetails' => 'nullable|string',
        ]);

        JobType::create([
            'name' => $validated['typeName'],
            'description' => $validated['jobTypeDetails'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job Type created successfully.',
        ]);
    }

    public function show($id){}

    public function edit($id){}

    public function update(Request $request, $id)
    {
        // Update the job type in the database
        // ...

        return redirect()->route('jobtypes.index')->with('success', 'Job Type updated successfully.');
    }

    public function destroy($id)
    {
        $jobType = JobType::findOrFail($id);
        $jobType->delete();

        return redirect()->route('jobtypes.index')->with('success', 'Job Type deleted successfully.');
    }
}
