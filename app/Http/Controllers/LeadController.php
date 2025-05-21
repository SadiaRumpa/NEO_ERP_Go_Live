<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadImage;
use App\Models\LeadNote;
use App\Models\MailTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    public function __construct()
    {
        // Logic to display leads
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Lead::with(['customer', 'leadaddress'])
                ->select('*')
                ->whereIn('lead_status', ['new', 'inprogress'])
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
                    $btn = '<a href="/leads/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('leads.index');
    }

    public function showCompleted(Request $request)
    {
        if ($request->ajax()) {

            $data = Lead::with(['customer', 'leadaddress'])
                ->select('*')
                ->where('lead_status', 'completed')
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
                    $btn = '<a href="/leads/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('leads.index')->with([
            'completed' => true,
        ]);
    }

    public function showRejected(Request $request)
    {
        if ($request->ajax()) {

            $data = Lead::with(['customer', 'leadaddress'])
                ->select('*')
                ->where('lead_status', 'rejected')
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
                    $btn = '<a href="/leads/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('leads.index')->with([
            'rejected' => true,
        ]);
    }

    public function show($key)
    {
        $lead = Lead::with(['customer', 'customer.billingaddress', 'customer.siteaddress', 'leadaddress', 'leadimages', 'leadnotes', 'leadnotes.user'])
            ->where('slug', $key)->first();
        if (!$lead) {
            return redirect()->route('leads.index')->with('error', 'Lead details not found.');
        }
        $mailtemplates = MailTemplate::where('status', 'active')
            ->orderBy('id', 'desc')->get();

        return view('leads.show', compact('lead', 'mailtemplates'));
    }

    public function addLeadImages(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'leadphoto'      => 'required|image|mimes:jpg,jpeg,png|max:10024',
        ]);

        $disk = env('FILESYSTEM_DISK', 'local');

        if ($request->hasFile('leadphoto')) {
            $file = $request->file('leadphoto');
            $path = $file->store('uploads/leads', $disk);

            if (!$path) {
                return response()->json(['status' => 'error', 'message' => 'Failed to store image.']);
            }

            if ($disk === 's3') {
                Storage::disk('s3')->setVisibility($path, 'public');
            }

            LeadImage::create([
                'lead_id'  => $validated['lead_id'],
                'image_path'  => $path,
                'image_name'  => $file->getClientOriginalName(),
                'image_url'   => Storage::disk($disk)->url($path),
                'source'      => $disk
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Lead image added successfully.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to add enquiry image.']);
    }
    public function destroyLeadImage($id)
    {
        $image = LeadImage::find($id);

        if (!$image) {
            return response()->json(['status' => 'error', 'message' => 'Image not found.']);
        }

        // Optional: delete from storage if needed
        if ($image->source !== 's3' && Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return response()->json(['status' => 'success', 'message' => 'Image deleted successfully.']);
    }
    public function leadNotes(Request $request)
    {
        $validated = $request->validate([
            'note' => 'required|string',
            'lead_slug' => 'required|string',
        ]);
        $lead = Lead::where('slug', $request->lead_slug)->first();
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $lead->leadnotes()->create([
            'note' => $validated['note'],
            'note_by' => optional(auth()->user())->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead details updated successfully.'
        ]);
    }
    public function deleteLeadNotes($id)
    {
        $leadNote = LeadNote::find($id);
        if ($leadNote) {
            $leadNote->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Lead note deleted successfully.'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Lead note not found.'
            ]);
        }
    }

    public function updateLeadDetails(Request $request, $id)
    {
        $validated = $request->validate([
            'leaddescription' => 'required|string',
        ]);
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $lead->update([
            'lead_description' => $validated['leaddescription'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lead details updated successfully.'
        ]);
    }
    public function updateLeadAddress(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $lead->leadaddress()->update([
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'postcode' => $request->postcode,
            'country' => $request->country,
        ]);

        return response()->json(['success' => 'Lead address updated successfully.']);
    }
    public function updateLeadStatus(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $lead->update([
            'lead_status' => $request->lead_status,
        ]);

        return response()->json(['success' => 'Lead status updated successfully.']);
    }
}
