<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerDetails;
use App\Models\Enquiry;
use App\Models\EnquiryAddress;
use App\Models\EnquiryImage;
use App\Models\EnquiryNote;
use App\Models\Lead;
use App\Models\LeadAddress;
use App\Models\LeadImage;
use App\Models\LeadNote;
use App\Models\MailTemplate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class EnquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Enquiry::with(['customer', 'enquiryaddress'])->select('*')->orderByDesc('created_at');

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
                    $btn = '<a href="enquiries/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('enquiries.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('id', 'desc')->get();
        return view('enquiries.create')->with(['customers' => $customers]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required_without:companyName',
            'companyName' => 'required_without:customer_id',
            'contactPersonFirstName' => 'required_without:customer_id|string|max:255',
            'contactPhone' => 'required_without:customer_id|string|max:20',
            'contactMobile' => 'required_without:customer_id|string|max:20',
            'contactEmail' => 'required_without:customer_id|email|max:255',
            'enquirydescription' => 'required|string',
            'enquiry_postcode' => 'required|string|max:20',
            'enquiry_address' => 'required|string',
            'enquiry_county' => 'nullable|string|max:255',
            'enquiry_country' => 'nullable|string|max:255',
            'photo1' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'photo2' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'photo3' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $customerid = $validated['customer_id'] ?? null;

            if (!$customerid) {
                $customer = Customer::create([
                    'company_name' => $validated['companyName'],
                    'contact_firstname' => $request->contactPersonFirstName,
                    'contact_lastname' => $request->contactPersonLastName,
                    'contact_phone' => $request->contactPhone,
                    'contact_mobile' => $request->contactMobile,
                    'contact_email' => $request->contactEmail,
                    'status' => 'active'
                ]);

                $customerid = $customer->id;

                // if ($request->billingAddress) {
                //     $customer->addresses()->create([
                //         'contact_firstname' => $request->billingContactFirstName,
                //         'contact_lastname' => $request->billingContactLastName,
                //         'contact_phone' => $request->billingcontactPhone,
                //         'contact_mobile' => $request->billingcontactMobile,
                //         'contact_email' => $request->billingcontactEmail,
                //         'address' => $request->billingAddress,
                //         'county' => $request->billingCounty,
                //         'postcode' => $request->billingPostcode,
                //         'country' => $request->billingCountry,
                //         'type' => 'billing'
                //     ]);
                // }

                // if ($request->siteAddress) {
                //     $customer->addresses()->create([
                //         'contact_firstname' => $request->siteContactFirstName,
                //         'contact_lastname' => $request->siteContactLastName,
                //         'contact_phone' => $request->sitecontactPhone,
                //         'contact_mobile' => $request->sitecontactMobile,
                //         'contact_email' => $request->sitecontactEmail,
                //         'address' => $request->siteAddress,
                //         'county' => $request->siteCounty,
                //         'postcode' => $request->sitePostcode,
                //         'country' => $request->siteCountry,
                //         'type' => 'site'
                //     ]);
                // }

                // $customer->details()->create([
                //     'company_name' => $request->assetsCompanyName,
                //     'registration_number' => $request->assetsRegistrationNo,
                //     'assets' => $request->assets ?? 0,
                //     'net_assets' => $request->netAssets ?? 0,
                //     'liabilities' => $request->liabilities ?? 0,
                //     'cash_in_bank' => $request->cashInBank ?? 0,
                // ]);
            }

            $enquiry = Enquiry::create([
                'enquiry_name' => randomenquiryGenerator(),
                'customer_id' => $customerid,
                'enquiry_description' => $validated['enquirydescription'],
                'enquiry_type' => 'query',
                'enquiry_source' => $validated['enquiry_source'] ?? 'call',
                'enquiry_status' => 'new',
            ]);

            EnquiryAddress::create([
                'enquiry_id' => $enquiry->id,
                'contact_firstname' => $request->contactPersonFirstName,
                'contact_lastname' => $request->contactPersonLastName,
                'contact_phone' => $request->contactPhone,
                'contact_mobile' => $request->contactMobile,
                'contact_email' => $request->contactEmail,
                'address' => $validated['enquiry_address'],
                'county' => $validated['enquiry_county'],
                'postcode' => $validated['enquiry_postcode'],
                'country' => $validated['enquiry_country']
            ]);

            $disk = env('FILESYSTEM_DISK', 'local');

            foreach (['photo1', 'photo2', 'photo3'] as $photoField) {
                if ($request->hasFile($photoField)) {
                    $file = $request->file($photoField);
                    $path = $file->store('uploads/enqueries', $disk);

                    if (!$path) {
                        throw new \Exception("Failed to store file: $photoField");
                    }

                    if ($disk === 's3') {
                        Storage::disk('s3')->setVisibility($path, 'public');
                    }

                    EnquiryImage::create([
                        'enquiry_id' => $enquiry->id,
                        'image_path' => $path,
                        'image_name' => $file->getClientOriginalName(),
                        'image_url' => Storage::disk($disk)->url($path),
                        'source' => $disk
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('enquiries.show', $enquiry->slug)
                ->with('success', 'Enquiry created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enquiry creation failed: ' . $e->getMessage());
            return back()->withErrors('An error occurred while creating the enquiry.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $key)
    {
        $enquiry = Enquiry::with(['customer', 'customer.billingaddress', 'customer.siteaddress', 'enquiryaddress', 'enquiryimages', 'enquirynotes'])->where('slug', $key)->first();

        if (!$enquiry) {
            return redirect()->route('enquiries.index')->with('error', 'Enquiry not found.');
        }
        $mailtemplates = MailTemplate::where('status', 'active')->orderBy('id', 'desc')->get();

        return view('enquiries.show', compact('enquiry', 'mailtemplates'));
    }

    public function enqueryNotes(Request $request)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'note'       => 'required|string',
        ]);

        $enquirynote = EnquiryNote::create([
            'enquiry_id'  => $validated['enquiry_id'],
            'note_by'     => Auth::id(),
            'note'        => $validated['note'],
            'note_type'   => 'internal',
            'note_status' => 'active',
        ]);

        if ($enquirynote) {
            return response()->json(['status' => 'success', 'message' => 'Enquiry note added successfully.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to add enquiry note.']);
    }

    public function deleteenqueryNotes($noteid)
    {
        $note = EnquiryNote::findOrFail($noteid);

        if (!$note) {
            return response()->json(['status' => 'error', 'message' => 'Note not found.']);
        }

        $note->delete();

        return response()->json(['status' => 'success', 'message' => 'Note deleted successfully.']);
    }

    public function enqueryImages(Request $request)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'enquiryphoto'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $disk = env('FILESYSTEM_DISK', 'local');

        if ($request->hasFile('enquiryphoto')) {
            $file = $request->file('enquiryphoto');
            $path = $file->store('uploads/enqueries', $disk);

            if (!$path) {
                return response()->json(['status' => 'error', 'message' => 'Failed to store image.']);
            }

            if ($disk === 's3') {
                Storage::disk('s3')->setVisibility($path, 'public');
            }

            EnquiryImage::create([
                'enquiry_id'  => $validated['enquiry_id'],
                'image_path'  => $path,
                'image_name'  => $file->getClientOriginalName(),
                'image_url'   => Storage::disk($disk)->url($path),
                'source'      => $disk
            ]);

            return redirect()->back()->with([
                'status' => 'success',
                'message' => 'Enquiry image added successfully.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to add enquiry image.']);
    }


    public function destroyEnqueryImage($id)
    {
        $image = EnquiryImage::find($id);

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


    public function updateEnquiryDetails(Request $request, $id)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'enquirydescription' => 'required|string',
        ]);

        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json([
                'status' => 'error',
                'message' => 'Enquiry not found.'
            ]);
        }

        $enquiry->update([
            'enquiry_description' => $validated['enquirydescription'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Enquiry details updated successfully.'
        ]);
    }

    public function updateEnquiryAddress(Request $request, $id)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'enquiry_address' => 'required|string',
            'enquiry_postcode' => 'required|string|max:20',
            'enquiry_county' => 'nullable|string|max:255',
        ]);

        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json([
                'status' => 'error',
                'message' => 'Enquiry not found.'
            ]);
        }

        $enquiryAddress = EnquiryAddress::where('enquiry_id', $id)->first();
        if ($enquiryAddress) {
            $enquiryAddress->update([
                'address' => $validated['enquiry_address'],
                'postcode' => $validated['enquiry_postcode'],
                'county' => $validated['enquiry_county'],
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Enquiry address not found.'
            ]);
        }

        return redirect()->back()->with([
            'status' => 'success',
            'message' => 'Enquiry address updated successfully.'
        ]);
    }

    public function destroy(string $id)
    {
        $enquiry = Enquiry::find($id);
        if ($enquiry) {
            $enquiry->delete();
            return redirect()->route('enquiries.index')->with('success', 'Enquiry deleted successfully.');
        }
        return redirect()->route('enquiries.index')->with('error', 'Enquiry not found.');
    }

    public function updateEnquiryStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);
        $enquiry = Enquiry::find($id);
        if ($enquiry) {
            $enquiry->update(['enquiry_status' => $validated['status']]);
            return redirect()->back()->with('success', 'Enquiry updated successfully.');
        }
        return redirect()->back()->with('error', 'Enquiry not found.');
    }

    public function enquerytoLeadConversion(Request $request)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'status' => 'required|string',
        ]);

        $enquiry = Enquiry::find($validated['enquiry_id']);
        if (!$enquiry) {
            return response()->json(['status' => 'error', 'message' => 'Enquiry not found.']);
        }

        $enquiryaddress = EnquiryAddress::where('enquiry_id', $validated['enquiry_id'])->first();
        $enquiryimages = EnquiryImage::where('enquiry_id', $validated['enquiry_id'])->get();
        $enquirynotes = EnquiryNote::where('enquiry_id', $validated['enquiry_id'])->get();

        $lead = Lead::create([
            'enquiry_id' => $validated['enquiry_id'],
            'lead_name' => $enquiry->enquiry_name,
            'lead_description' => $enquiry->enquiry_description,
            'customer_id' => $enquiry->customer_id,
            'lead_type' => $enquiry->enquiry_type,   
            'lead_category' => $enquiry->enquiry_category,    
            'lead_priority' => $enquiry->enquiry_priority,
            'lead_status' => 'new',
        ]);

        LeadAddress::create([
            'lead_id' => $lead->id,
            'contact_firstname' => $enquiryaddress->contact_firstname,
            'contact_lastname' => $enquiryaddress->contact_lastname,
            'contact_phone' => $enquiryaddress->contact_phone,
            'contact_mobile' => $enquiryaddress->contact_mobile,
            'contact_email' => $enquiryaddress->contact_email,
            'address' => $enquiryaddress->address,
            'county' => $enquiryaddress->county,
            'postcode' => $enquiryaddress->postcode,
            'country' => $enquiryaddress->country,
        ]);

        foreach ($enquiryimages as $image) {
            LeadImage::create([
                'lead_id' => $lead->id,
                'image_path' => $image->image_path,
                'image_name' => $image->image_name,
                'image_url' => $image->image_url,
                'source' => $image->source
            ]);
        }
        foreach ($enquirynotes as $note) {
            LeadNote::create([
                'lead_id' => $lead->id,
                'note_by' => $note->note_by,
                'note' => $note->note,
                'note_type' => $note->note_type,
                'note_status' => $note->note_status,
            ]);
        }
        $enquiry->update(['enquiry_status' => $validated['status']]);
        

        return response()->json(['status' => 'success', 'message' => 'Enquiry converted to lead successfully.']);
    }
}
