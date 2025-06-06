<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\CustomerDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                    $showUrl = route('customers.show', $row->id); // Edit Route
                    return '
                    <a href="' . $showUrl . '" class="show btn btn-primary btn-sm">Show</a>
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
        $validated = $request->validate([
            'companyName'        => 'required|string|max:255',
            'contactPersonFirstName'  => 'required|string|max:255',
            'contactPersonLastName'   => 'nullable|string|max:255',
            'contactPhone'       => 'required|string|max:20',
            'contactEmail'       => 'required|email|max:255',
            'contactMobile'      => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();

        try {
            $customer = new Customer();
            $customer->company_name    = $validated['companyName'];
            $customer->contact_firstname    = $validated['contactPersonFirstName'];
            $customer->contact_lastname = $validated['contactPersonLastName'] ?? null;
            $customer->contact_phone      = $validated['contactPhone'];
            $customer->contact_mobile  = $validated['contactMobile'] ?? null;
            $customer->contact_email   = $validated['contactEmail'];
            $customer->status          = 'active';
            $customer->save();

            DB::commit();

            return redirect()->route('customers.show', $customer->id)
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer store failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the customer. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::with(['billingaddress', 'siteaddress', 'findetails'])->findOrFail($id);
        // return response()->json($customer);

        return view('customers.show')->with([
            'customer' => $customer
        ]);
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
            'contactPersonFirstName'   => 'required|string|max:255',
            'contactPersonLastName'   => 'nullable|string|max:255',
            'contactPhone'        => 'required|string|max:20',
            'contactEmail'        => 'required|email|max:255',
        ]);

        // Update Customer
        $customer = Customer::findOrFail($id);
        $customer->update([
            'contact_firstname'   => $validated['contactPersonFirstName'],
            'contact_lastname' => $validated['contactPersonLastName'],
            'contact_phone'     => $validated['contactPhone'],
            'contact_mobile'       => $validated['contactMobile'],
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

    public function updateBasicInfo(Request $request, $id)
    {
        $request->validate([
            'contactPersonFirstName' => 'required|string|max:255',
            'contactEmail' => 'required|email',
            'contactPhone' => 'required_without:contactMobile|max:20',
            'contactMobile' => 'required_without:contactPhone|max:20',
        ]);

        $customer = Customer::findOrFail($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }
        // Update the customer's basic information
        $customer->contact_firstname = $request->input('contactPersonFirstName');
        $customer->contact_lastname = $request->input('contactPersonLastName');
        $customer->contact_email = $request->input('contactEmail');
        $customer->contact_phone = $request->input('contactPhone');
        $customer->contact_mobile = $request->input('contactMobile');
        $customer->status = 'active'; // Ensure the customer is active
        // Save the updated customer
        if (!$customer->save()) {
            return response()->json(['message' => 'Failed to update customer info.'], 500);
        }
       

        return response()->json(['message' => 'Customer info updated successfully.']);
    }

    public function updateFinancialDetails(Request $request, string $id)
    {
        $validated = $request->validate([
            'registration_no' => 'nullable|string|max:255',
            'assets' => 'required|numeric',
            'netAssets' => 'required|numeric',
            'liabilities' => 'required|numeric',
            'cashInBank' => 'required|numeric',
        ]);

        try {
            $customer = Customer::findOrFail($id);
            if (!$customer) {
                return redirect()->back()->with('error', 'Customer not found.');
            }
            CustomerDetails::updateOrCreate(
                ['customer_id' => $id], // Match condition
                [
                    'company_name' => $customer->company_name,
                    'registration_number' => $validated['registration_no'],
                    'assets' => $validated['assets'],
                    'net_assets' => $validated['netAssets'],
                    'liabilities' => $validated['liabilities'],
                    'cash_in_bank' => $validated['cashInBank'],
                ]
            );

            return redirect()->back()->with('success', 'Customer financial details updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
            // return $e->getMessage();
        }
    }

    public function storeCustomerAddress(Request $request, string $id)
    {
        // return $request->all();
        $validated = $request->validate([
            'address' => 'required|string|max:255',
            'county' => 'nullable|string|max:255',
            'postcode' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'type' => 'required|string|in:billing,site',
        ]);

        $customerAddress = new CustomerAddress();
        $customerAddress->customer_id = $id;
        $customerAddress->contact_firstname = $request->input('contactPersonFirstName');
        $customerAddress->contact_lastname = $request->input('contactPersonLastName');
        $customerAddress->contact_phone = $request->input('contactPhone');
        $customerAddress->contact_mobile = $request->input('contactMobile');
        $customerAddress->contact_email = $request->input('contactEmail');
        $customerAddress->address = $validated['address'];
        $customerAddress->county = $validated['county'] ?? '';
        $customerAddress->country = $validated['country'];
        $customerAddress->postcode = $validated['postcode'];
        $customerAddress->address_type = $validated['type'];
        $customerAddress->save();

        return redirect()->back()->with('success', 'Customer address created successfully.');
    }

    public function deleteCustomerAddress($id)
    {
        $address = CustomerAddress::findOrFail($id);
        if ($address) {
            $address->delete();
            return redirect()->back()->with('success', 'Customer address deleted successfully.');
        }
        return redirect()->back()->with('error', 'Customer address not found.');
    }


    
}
