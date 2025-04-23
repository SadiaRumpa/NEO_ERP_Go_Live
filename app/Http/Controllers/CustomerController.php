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

    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $validated = $request->validate([
            'companyName'         => 'required|string|max:255',
            'contactPersonFirstName'   => 'required|string|max:255',
            'contactPersonLastName'   => 'nullable|string|max:255',
            'contactPhone'        => 'required|string|max:20',
            'contactEmail'        => 'required|email|max:255',
        ]);

        // Update Customer
        $customer = Customer::findOrFail($id);
        $customer->update([
            'company_name'   => $validated['companyName'],
            'contact_firstname'   => $validated['contactPersonFirstName'],
            'contact_lastname' => $validated['contactPersonLastName'],
            'contact_phone'     => $validated['contactPhone'],
            'contact_mobile'       => $validated['contactPhone'],
            'contact_email'  => $validated['contactEmail'],
        ]);

        // Update addresses
        $this->updateCustomerAddress($request, $id, 'billing');
        $this->updateCustomerAddress($request, $id, 'site');

        // Update customer financial details
        if ($request->filled('assets')) {
            $this->updateCustomerDetails($request, $id);
        }

        return redirect()->back()->with('success', 'Customer updated successfully.');
    }

    private function updateCustomerAddress(Request $request, $customerId, $type)
    {
        $prefix = $type === 'billing' ? 'billing' : 'site';

        if (!$request->filled("{$prefix}Address")) {
            return;
        }

        $addressData = [
            'contact_firstname'=> $request->input("{$prefix}PersonFirstName"),
            'contact_lastname' => $request->input("{$prefix}PersonLastName"),
            'contact_phone'    => $request->input("{$prefix}Phone"),
            'contact_mobile'   => $request->input("{$prefix}Mobile"),
            'contact_email'    => $request->input("{$prefix}Email"),
            'address'     => $request->input("{$prefix}Address"),
            'county'     => $request->input("{$prefix}County") ?? 'United Kingdom',
            'postcode'    => $request->input("{$prefix}Postcode"),
            'customer_id' => $customerId,
            'type'        => $type,
        ];

        $customerAddress = CustomerAddress::firstOrNew([
            'customer_id' => $customerId,
            'type'        => $type,
        ]);

        $customerAddress->fill($addressData)->save();
    }
    private function updateCustomerDetails(Request $request, $customerId)
    {
        $detailsData = [
            'company_name'       => $request->input('companyName', ''),
            'registration_number' => $request->input('registration_no', ''),
            'assets'             => $request->input('assets', 0),
            'net_assets'         => $request->input('netAssets', 0),
            'liabilities'        => $request->input('liabilities', 0),
            'cash_in_bank'       => $request->input('cashInBank', 0),
            'customer_id'        => $customerId,
        ];

        $customerDetails = CustomerDetails::firstOrNew(['customer_id' => $customerId]);
        $customerDetails->fill($detailsData)->save();
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
