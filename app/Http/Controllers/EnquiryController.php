<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerDetails;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EnquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Enquiry::select('*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="javascript:void(0)" class="edit btn btn-primary btn-sm">Details</a>';
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
        $request->validate([
            'enquiry_date' => 'required',
            'enquiry_details' => 'required',
            'status' => 'required',
        ]);

        if (!$request->customer_id) {
            $customer = new Customer();
            $customer->company_name = $request->companyName;
            $customer->contact_name = $request->contactPersonName;
            $customer->contact_no = $request->contactPhone;
            $customer->contact_email = $request->contactEmail;
            $customer->status = 'active';
            $customer->save();

            $customerid = $customer->id;
            $request->merge(['customer_id' => $customer->id]);

            if ($request->billingAddress != null) {
                $customeraddress = new CustomerAddress();
                $customeraddress->address = $request->billingAddress;
                $customeraddress->city = $request->billingCity;
                $customeraddress->state = $request->billingState;
                $customeraddress->country = $request->billingCountry;
                $customeraddress->zip_code = $request->billingPostcode;
                $customeraddress->customer_id = $customerid;
                $customeraddress->type = "billing";
                $customeraddress->save();
            }

            if ($request->siteAddress != null) {
                $customeraddress = new CustomerAddress();
                $customeraddress->address = $request->siteAddress;
                $customeraddress->city = $request->siteCity;
                $customeraddress->state = $request->siteState;
                $customeraddress->country = $request->siteCountry;
                $customeraddress->zip_code = $request->sitePostcode;
                $customeraddress->customer_id = $customerid;
                $customeraddress->type = "site";
                $customeraddress->save();
            }


            $customerdetails = new CustomerDetails();
            $customerdetails->customer_id = $customerid;
            $customerdetails->assets = $request->assets ?? 0;
            $customerdetails->net_assets = $request->netAssets ?? 0;
            $customerdetails->liabilities = $request->liabilities ?? 0;
            $customerdetails->cash_in_bank = $request->cashInBank ?? 0;
            $customerdetails->save();
        } else{
            $customerid = $request->customer_id;
            
            $enquiry = new Enquiry();
            $enquiry->enquiry_name = generateEnquiryName();
            $enquiry->enquiry_description = $request->enquirydescription;
            $enquiry->customer_id = $customerid;

        }


        

        return redirect()->route('enquiries.index')
            ->with('success', 'Enquiry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
