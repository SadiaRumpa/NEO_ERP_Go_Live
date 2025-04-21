<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerDetails;
use App\Models\Enquiry;
use App\Models\EnquiryAddress;
use App\Models\EnquiryImage;
use App\Models\EnquiryNote;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

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
                ->addColumn('action', function ($row) {
                    $btn = '<a href="enquiries/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
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
            'enquirydescription' => 'required',
            'enquiry_postcode'             => 'required|string|max:20',
            'enquiry_address'              => 'required|string',
            'enquiry_county'               => 'nullable|string|max:255',
            'enquiry_country'              => 'nullable|string|max:255',
            'photo1'               => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'photo2'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'photo3'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $customerid = null;
        if (!$request->customer_id) {
            $customer = new Customer();
            $customer->company_name = $request->companyName;
            $customer->contact_firstname = $request->contactPersonFirstName;
            $customer->contact_lastname = $request->contactPersonLastName;
            $customer->contact_phone = $request->contactPhone;
            $customer->contact_mobile = $request->contactMobile;
            $customer->contact_email = $request->contactEmail;
            $customer->status = 'active';
            $customer->save();

            $customerid = $customer->id;
            $request->merge(['customer_id' => $customer->id]);

            if ($request->billingAddress != null) {
                $customeraddress = new CustomerAddress();
                $customeraddress->contact_firstname = $request->billingContactFirstName;
                $customeraddress->contact_lastname = $request->contactPersonLastName;
                $customeraddress->contact_phone = $request->contactPhone;
                $customeraddress->contact_mobile = $request->contactMobile;
                $customeraddress->contact_email = $request->contactEmail;
                $customeraddress->address = $request->billingAddress;
                $customeraddress->country = $request->billingCountry;
                $customeraddress->zip_code = $request->billingPostcode;
                $customeraddress->customer_id = $customerid;
                $customeraddress->type = "billing";
                $customeraddress->save();
            }

            if ($request->siteAddress != null) {
                $customeraddress = new CustomerAddress();
                $customeraddress->address = $request->siteAddress;
                $customeraddress->county = $request->siteCountry;
                $customeraddress->zip_code = $request->sitePostcode;
                $customeraddress->country = $request->siteCountry;
                $customeraddress->customer_id = $customerid;
                $customeraddress->type = "site";
                $customeraddress->save();
            }

            $customerdetails = new CustomerDetails();
            $customerdetails->customer_id = $customerid;
            $customerdetails->assets = $request->registration_number;
            $customerdetails->assets = $request->assets ?? 0;
            $customerdetails->net_assets = $request->netAssets ?? 0;
            $customerdetails->liabilities = $request->liabilities ?? 0;
            $customerdetails->cash_in_bank = $request->cashInBank ?? 0;
            $customerdetails->save();
        } else {
            $customerid = $request->customer_id;
        }
        // Create Enquiry
        $enquiry = Enquiry::create([
            'enquiry_name'        => $this->randomenquiryGenerator(),
            'customer_id'         => $customerid,
            'enquiry_description' => $validated['enquirydescription'],
            'enquiry_type'        => 'query',
            'enquiry_source'      => $validated['enquiry_source'] ?? 'call',
            'enquiry_status'      => 'new',
        ]);

        // Create Address
        EnquiryAddress::create([
            'enquiry_id'         => $enquiry->id,
            'address'            => $validated['enquiry_address'],
            'county'             => $validated['enquiry_county'],
            'postcode'           => $validated['enquiry_postcode'],
            'country'            => $validated['enquiry_country'],
        ]);

        foreach (['photo1', 'photo2', 'photo3'] as $photoField) {
            if ($request->hasFile($photoField)) {
                $file = $request->file($photoField);

                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Define upload path in public/
                $destinationPath = public_path('uploads/enqueries');

                // Create directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move file to public/uploads/enqueries/
                $file->move($destinationPath, $filename);

                // Save relative path to DB
                EnquiryImage::create([
                    'enquiry_id' => $enquiry->id,
                    'image_path' => 'uploads/enqueries/' . $filename,
                    'image_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('enquiries.show', $enquiry->slug)
            ->with('success', 'Enquiry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $key)
    {
        $enquiry = Enquiry::with(['customer', 'enquiryaddress', 'enquiryimages', 'enquirynotes'])->where('slug', $key)->first();

        if (!$enquiry) {
            return redirect()->route('enquiries.index')->with('error', 'Enquiry not found.');
        }

        return view('enquiries.show', compact('enquiry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
}
