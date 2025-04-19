<?php

namespace App\Http\Controllers;

use App\Models\UkPostcode;
use Illuminate\Http\Request;
use App\Models\Enquiry;
use App\Models\Customer;
use App\Models\EnquiryAddress;
use App\Models\EnquiryImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlobalController extends Controller
{
    public function query()
    {
        return view('externals.query');
    }

    public function checkPostCode($key)
    {
        $postcode = str_replace(' ', '', $key);
        $postcode = strtoupper($postcode);
        $address = [];

        $check = UkPostcode::select('roads', 'parish', 'nearest_station', 'county', 'postcode', 'country')
            ->where("normalized_postcode", $postcode)
            ->first();

        if ($check) {
            $address = [
                'roads' => $check->roads,
                'parish' => $check->parish,
                'nearest_station' => $check->nearest_station,
                'county' => $check->county,
                'postcode' => $check->postcode,
                'country' => $check->country,
            ];
        } else {
            $address['roads'] = 'Invalid postcode';
        }

        return response()->json(['address' => $address]);
    }

    public function storeQuery(Request $request)
    {

        $validated = $request->validate([
            // 'g-recaptcha-response' => 'required|captcha',
            'firstname'            => 'required|string|max:255',
            'lastname'             => 'nullable|string|max:255',
            'company'              => 'required|string|max:255',
            'phone'                => 'required|string|max:20',
            'email'                => 'required|email|max:255',
            'jobdescription'       => 'required|string',
            'postcode'             => 'required|string|max:20',
            'address'              => 'required|string',
            'county'               => 'nullable|string|max:255',
            'country'              => 'nullable|string|max:255',
            'photo1'               => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'photo2'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'photo3'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'enquiry_source'       => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Create Customer
            $customer = Customer::create([
                'company_name'      => $validated['company'],
                'contact_firstname' => $validated['firstname'],
                'contact_lastname'  => $validated['lastname'],
                'contact_phone'     => $validated['phone'],
                'contact_mobile'    => $validated['phone'],
                'contact_email'     => $validated['email'],
                'status'            => 'active',
            ]);

            // Create Enquiry
            $enquiry = Enquiry::create([
                'enquiry_name'        => $this->randomenquiryGenerator(),
                'customer_id'         => $customer->id,
                'enquiry_description' => $validated['jobdescription'],
                'enquiry_type'        => 'query',
                'enquiry_source'      => $validated['enquiry_source'] ?? 'website',
                'enquiry_status'      => 'new',
            ]);

            // Create Address
            EnquiryAddress::create([
                'enquiry_id'         => $enquiry->id,
                'contact_firstname'  => $validated['firstname'],
                'contact_lastname'   => $validated['lastname'],
                'contact_phone'      => $validated['phone'],
                'contact_mobile'     => $validated['phone'],
                'contact_email'      => $validated['email'],
                'address'            => $validated['address'],
                'county'             => $validated['county'],
                'postcode'           => $validated['postcode'],
                'country'            => $validated['country'],
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


            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Your query has been submitted successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Query Submission Failed: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    private function randomenquiryGenerator()
    {
        $date = date('Ymd');
        // Count how many enquiries were created today
        $todayCount = Enquiry::whereDate('created_at', now()->toDateString())->count() + 1;
        // Pad the number with leading zeros (4 digits)
        $sequence = str_pad($todayCount, 4, '0', STR_PAD_LEFT);
        $enquiryNumber = $date . $sequence;

        return $enquiryNumber;
    }
}
