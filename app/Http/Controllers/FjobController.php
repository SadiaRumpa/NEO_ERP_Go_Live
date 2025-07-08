<?php

namespace App\Http\Controllers;

use App\Models\Fjob;
use App\Models\FjobAttachment;
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
                ->whereIn('job_status', ['draft', 'accepted', 'new', 'inprogress', 'on_hold'])
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
                    $btn = '<a href="/job/' . $row->id . '" class="edit btn btn-primary btn-sm">Details</a>';
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

        return view('jobs.show', compact('job', 'mailtemplates'));
    }

    public function uploadJobAttachments(Request $request)
    {
        $request->validate([
            'jobid' => 'required|exists:fjobs,id',
            'attachment_type' => 'required|string',
            'title' => 'required|string|max:255',
            'attachment' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10024',
        ]);

        $disk = env('FILESYSTEM_DISK', 'local');

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('uploads/attachments', $disk);

            if (!$path) {
                return response()->json(['status' => 'error', 'message' => 'Failed to store image.']);
            }

            if ($disk === 's3') {
                Storage::disk('s3')->setVisibility($path, 'public');
            }

            FjobAttachment::create([
                'fjob_id'  => $request->input('jobid'),
                'attachment_path'  => $path,
                'attachment_name'  => $request->input('title'),
                'description' => $request->input('attachment_type'),
                'attachment_type'  => $file->getClientOriginalExtension(),
                'attachment_url'   => Storage::disk($disk)->url($path),
                'source'      => $disk,
                'uploaded_by' => Auth::id(),
                'status' => 'active',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Attachment added successfully.'
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function destroyLeadAttachment($id)
    {
        $attachment = FjobAttachment::find($id);

        if (!$attachment) {
            return response()->json(['status' => 'error', 'message' => 'Attachment not found.']);
        }

        // Optional: delete from storage if needed
        if ($attachment->source !== 's3' && Storage::disk('public')->exists($attachment->attachment_path)) {
            Storage::disk('public')->delete($attachment->attachment_path);
        }

        $attachment->delete();

        return response()->json(['status' => 'success', 'message' => 'Attachment deleted successfully.']);
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

    public function updateJobTitle(Request $request, $id)
    {
        $job = Fjob::find($id);
        if (!$job) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job not found.'
            ], 404);
        }

        $job->update([
            'job_title' => $request->job_title,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Job title updated successfully.'
        ]);
    }
}
