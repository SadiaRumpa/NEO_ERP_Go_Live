<?php

namespace App\Http\Controllers;

use App\Models\Fjob;
use App\Models\MailTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FjobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Fjob::with(['customer', 'jobaddress'])
                ->select('*')
                ->whereIn('job_status', ['accepted', 'new', 'inprogress', 'on_hold'])
                ->orderByDesc('created_at');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    return $row->customer->company_name;
                })
                ->addColumn('customer_name', function ($row) {
                    return $row->customer->contact_firstname . ' ' . $row->customer->contact_lastname;
                })
                ->addColumn('customer_email', function ($row) {
                    return $row->customer->contact_email;
                })
                ->addColumn('customer_phone', function ($row) {
                    return $row->customer->contact_phone;
                })
                ->addColumn('customer_mobile', function ($row) {
                    return $row->customer->contact_mobile;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="/jobs/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }
        return view('jobs.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($key)
    {
        $job = Fjob::with([
            'customer',
            'customer.billingaddress',
            'customer.siteaddress',
            'jobaddress',
            'jobimages',
            'jobnotes',
            'jobnotes.user',
            'jobattachments',
            'jobquotations'
        ])
            ->where('id', $key)->first();
        // dd($job);
        if (!$job) {
            return redirect()->route('jobs.index')->with('error', 'Job details not found.');
        }

        $mailtemplates = MailTemplate::where('status', 'active')
            ->orderBy('id', 'desc')->get();

        return view('jobs.show', compact('job','mailtemplates'));
    }

    public function updateJobStatus(Request $request, $id)
    {
        $job = Fjob::find($id);
        if (!$job) {
            return response()->json(['error' => 'Job not found.'], 404);
        }

        $job->update([
            'job_status' => $request->job_status,
        ]);

        return response()->json(['success' => 'Job status updated successfully.']);
    }
}
