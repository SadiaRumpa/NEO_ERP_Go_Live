<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\CustomerDetails;
use Illuminate\Http\Request;

use App\Models\Customer;
use App\Models\Enquiry;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::select('*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editUrl = route('customers.edit', $row->id); // Edit Route
                    $deleteUrl = route('customers.destroy', $row->id); // Delete Route

                    return '
                    <a href="' . $editUrl . '" class="edit btn btn-primary btn-sm">Edit</a>
                    <button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('customers.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'companyName' => 'required',
            'contactPersonName' => 'required',
            'contactPhone' => 'required',
            'contactEmail' => 'required',
        ]);

        $customer = new Customer();
        $customer->company_name = $request->companyName;
        $customer->contact_name = $request->contactPersonName;
        $customer->contact_no = $request->contactPhone;
        $customer->contact_email = $request->contactEmail;
        $customer->status = 'active';
        $customer->save();

        $customerid = $customer->id;

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


        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
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
        $customer = Customer::with(['billingaddress', 'siteaddress', 'findetails'])->findOrFail($id);
        // return response()->json($customer);

        return view('customers.edit')->with([
            'customer' => $customer
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'companyName' => 'required',
            'contactPersonName' => 'required',
            'contactPhone' => 'required',
            'contactEmail' => 'required',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->company_name = $request->companyName;
        $customer->contact_name = $request->contactPersonName;
        $customer->contact_no = $request->contactPhone;
        $customer->contact_email = $request->contactEmail;
        $customer->status = 'active';
        $customer->save();

        if ($request->billingAddress != null) {
            $customeraddress = CustomerAddress::where('customer_id', $id)->where('type', 'billing')->first();
            if ($customeraddress != null) {
                $customeraddress->address = $request->billingAddress;
                $customeraddress->city = $request->billingCity;
                $customeraddress->state = $request->billingState;
                $customeraddress->country = $request->billingCountry;
                $customeraddress->zip_code = $request->billingPostcode;
                $customeraddress->save();
            } else {
                $customeraddress = new CustomerAddress();
                $customeraddress->address = $request->billingAddress;
                $customeraddress->city = $request->billingCity;
                $customeraddress->state = $request->billingState;
                $customeraddress->country = $request->billingCountry;
                $customeraddress->zip_code = $request->billingPostcode;
                $customeraddress->customer_id = $id;
                $customeraddress->type = "billing";
                $customeraddress->save();
            }
        }
        if ($request->siteAddress != null) {
            $customeraddress = CustomerAddress::where('customer_id', $id)->where('type', 'site')->first();
            if ($customeraddress != null) {
                $customeraddress->address = $request->siteAddress;
                $customeraddress->city = $request->siteCity;
                $customeraddress->state = $request->siteState;
                $customeraddress->country = $request->siteCountry;
                $customeraddress->zip_code = $request->sitePostcode;
            } else {
                $customeraddress = new CustomerAddress();
                $customeraddress->address = $request->siteAddress;
                $customeraddress->city = $request->siteCity;
                $customeraddress->state = $request->siteState;
                $customeraddress->country = $request->siteCountry;
                $customeraddress->zip_code = $request->sitePostcode;
                $customeraddress->customer_id = $id;
                $customeraddress->type = "site";
                $customeraddress->save();
            }
        }
        if ($request->assets != null) {
            $customerdetails = CustomerDetails::where('customer_id', $id)->first();
            if ($customerdetails != null) {
                $customerdetails->assets = $request->assets;
                $customerdetails->net_assets = $request->netAssets;
                $customerdetails->liabilities = $request->liabilities;
                $customerdetails->cash_in_bank = $request->cashInBank;
                $customerdetails->save();
            } else {
                $customerdetails = new CustomerDetails();
                $customerdetails->assets = $request->assets ?? 0;
                $customerdetails->net_assets = $request->netAssets ?? 0;
                $customerdetails->liabilities = $request->liabilities ?? 0;
                $customerdetails->cash_in_bank = $request->cashInBank ?? 0;
                $customerdetails->customer_id = $id;
                $customerdetails->save();
            }
        }
        return redirect()->route('customers.edit', $id)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer) {
            $custenquiry = Enquiry::where('customer_id', $id)->exists(); // Check if Enquiry exists

            if ($custenquiry) {
                // If enquiries exist, update customer status to 'inactive'
                $customer->update(['status' => 'inactive']);

                return response()->json(['message' => 'Customer has active enquiries and is set to inactive.']);
            } else {
                // If no enquiries exist, delete the customer
                $customer->delete();
                CustomerAddress::where('customer_id', $id)->delete();
                CustomerDetails::where('customer_id', $id)->delete();

                return response()->json(['message' => 'Customer deleted successfully.']);
            }
        }

        return response()->json(['message' => 'Customer not found.'], 404);
    }
}
