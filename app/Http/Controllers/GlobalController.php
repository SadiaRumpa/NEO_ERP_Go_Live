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
use Illuminate\Support\Facades\Storage;

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
        
        $check = lookup_postcode($postcode);
        return response()->json(['address' => $check]);
    }

    public function checkPostTown($key)
    {
        $posttown = str_replace(' ', '', $key);
        $posttown = strtoupper($posttown);

        $check = lookup_places($posttown);
        return response()->json(['address' => $check]);
    }

    public function storeQuery(Request $request)
    {
        $validated = $request->validate([
            'firstname'        => 'required|string|max:255',
            'lastname'         => 'nullable|string|max:255',
            'company'          => 'required|string|max:255',
            'phone'            => 'required|string|max:11',
            'email'            => 'required|email|max:255',
            'jobdescription'   => 'required',
            'postcode'         => 'required|string|max:20',
            'address'          => 'required|string',
            'county'           => 'nullable|string|max:255',
            'country'          => 'nullable|string|max:255',
            'photo1'           => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'photo2'           => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'photo3'           => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'enquiry_source'   => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Create customer
            $customer = Customer::create([
                'company_name'      => $validated['company'],
                'contact_firstname' => $validated['firstname'],
                'contact_lastname'  => $validated['lastname'],
                'contact_phone'     => $validated['phone'],
                'contact_mobile'    => $validated['phone'],
                'contact_email'     => $validated['email'],
                'status'            => 'active',
            ]);

            // Create enquiry
            $enquiry = Enquiry::create([
                'enquiry_name'        => randomenquiryGenerator(),
                'customer_id'         => $customer->id,
                'enquiry_description' => $validated['jobdescription'],
                'enquiry_type'        => 'query',
                'enquiry_source'      => $validated['enquiry_source'] ?? 'website',
                'enquiry_status'      => 'new',
            ]);

            // Save address
            EnquiryAddress::create([
                'enquiry_id'       => $enquiry->id,
                'contact_firstname' => $validated['firstname'],
                'contact_lastname'  => $validated['lastname'],
                'contact_phone'     => $validated['phone'],
                'contact_mobile'    => $validated['phone'],
                'contact_email'     => $validated['email'],
                'address'           => $validated['address'],
                'county'            => $validated['county'],
                'postcode'          => $validated['postcode'],
                'country'           => $validated['country'],
            ]);

            // Handle image upload
            $this->uploadImages($request, $enquiry->id);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Your query has been submitted successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Query Submission Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while submitting your query.',
            ], 500);
        }
    }
    protected function uploadImages(Request $request, $enquiryid)
    {
        $disk = env('FILESYSTEM_DISK', 'local');

        foreach (['photo1', 'photo2', 'photo3'] as $photoField) {
            if (!$request->hasFile($photoField)) continue;

            $file = $request->file($photoField);
            $originalName = $file->getClientOriginalName();

            if ($disk === 's3') {
                $path = $file->store('uploads', 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $url = Storage::disk('s3')->url($path);
            } else {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/enqueries');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $file->move($destination, $filename);
                $path = 'uploads/enqueries/' . $filename;
                $url = asset($path);
            }

            EnquiryImage::create([
                'enquiry_id' => $enquiryid,
                'image_path' => $path,
                'image_name' => $originalName,
                'image_url'  => $url,
                'source'     => $disk,
            ]);
        }
    }
}
