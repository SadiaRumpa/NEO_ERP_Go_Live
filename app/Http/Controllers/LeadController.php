<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadImage;
use App\Models\LeadNote;
use App\Models\MailTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    public function __construct()
    {
        // Logic to display leads
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Lead::with(['customer', 'leadaddress'])->select('*')->orderByDesc('created_at');

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

    public function show($key)
    {
        $lead = Lead::with(['customer', 'customer.billingaddress', 'customer.siteaddress', 'leadaddress', 'leadimages', 'leadnotes'])
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
        $lead = Lead::where('slug', $request->lead_slug)->first();
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        if ($request->hasFile('lead_images')) {
            foreach ($request->file('lead_images') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/lead_images'), $filename);

                $lead->leadimages()->create([
                    'image_path' => 'uploads/lead_images/' . $filename,
                ]);
            }
        }

        return response()->json(['success' => 'Images uploaded successfully.']);
    }
    public function destroyLeadImage($id)
    {
        $leadImage = LeadImage::find($id);
        if ($leadImage) {
            $leadImage->delete();
            return response()->json(['success' => 'Image deleted successfully.']);
        } else {
            return response()->json(['error' => 'Image not found.'], 404);
        }
    }
    public function leadNotes(Request $request)
    {
        $lead = Lead::where('slug', $request->lead_slug)->first();
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $lead->leadnotes()->create([
            'note' => $request->note,
            'created_by' => auth()->user()->id,
        ]);

        return response()->json(['success' => 'Note added successfully.']);
    }
    public function deleteLeadNotes($id)
    {
        $leadNote = LeadNote::find($id);
        if ($leadNote) {
            $leadNote->delete();
            return response()->json(['success' => 'Note deleted successfully.']);
        } else {
            return response()->json(['error' => 'Note not found.'], 404);
        }
    }

    public function updateLeadDetails(Request $request, $id)
    {
        $lead = Lead::find($id);
        if (!$lead) {
            return response()->json(['error' => 'Lead not found.'], 404);
        }

        $lead->update([
            'lead_description' => $request->lead_description,
        ]);

        return response()->json(['success' => 'Lead details updated successfully.']);
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
