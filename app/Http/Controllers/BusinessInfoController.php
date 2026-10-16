<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessInfoRequest;
use App\Http\Requests\UpdateBusinessInfoRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BusinessInfo;
use App\Models\Enquiry;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BusinessInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = BusinessInfo::select('*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $showUrl = route('businessinfo.show', $row->id); // Edit Route
                    return '
                    <a href="' . $showUrl . '" class="show btn btn-primary btn-sm">Show</a>
                    <button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        else{
            //TODO auth has permission
                
            $businessinfo = BusinessInfo::get();
            $businessinfoCount = $businessinfo->count();
            if($businessinfoCount==0){
                return $this->create();
            }
            elseif($businessinfoCount==1){
                $businessinfo = BusinessInfo::first();
                // return view('businessinfo.edit')->with([
                //     'businessinfo' => $businessinfo
                // ]);
                return $this->edit($businessinfo->id);
            }
        }
        return view('businessinfo.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('businessinfo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name'        => 'required|unique:business_info|string|max:255',
            'businessShortName'  => 'nullable|string|max:255',
            'businessDomain'   => 'nullable|string|max:255',
            'businessStartDate'       => 'nullable|date|string|max:20',
            'allowedFiletypes'  => 'nullable|string|max:255',
            'businessAddress1'  => 'nullable|string|max:255',
            'businessAddress2'  => 'nullable|string|max:255',
            'businessCity'  => 'nullable|string|max:255',
            'businessState'  => 'nullable|string|max:255',
            'businessZip'  => 'nullable|string|max:255',
            'businessCountry'      => 'nullable|string|max:255',
            'businessPhone'      => 'nullable|string|max:20',
            'businessEmail'       => 'nullable|email|max:255',
            'businessWebsite'       => 'nullable|string|max:255',
            'businessRegistrationNumber'  => 'nullable|string|max:255',
            'businessRegistrationNumber2'  => 'nullable|string|max:255',
            'businessLogo' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg']) // Example: allow specific types
                    ->max(100 * 1024), // Example: max 100MB
            ],
            'businessLogoDark' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg']) // Example: allow specific types
                    ->max(100 * 1024), // Example: max 100MB
            ],
            'businessFavicon' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg', 'ico']) // Example: allow specific types
                    ->max(100 * 1024), // Example: max 100MB
            ],
            
            
        ]);
    
        //format 
        if(!empty($validated['businessPhone'])){
            $validated['businessPhone'] = simple_format_phone($validated['businessPhone']);
        }
        if(empty($validated['allowedFiletypes'])){
            $validated['allowedFiletypes'] = 'IMG:.jpg,.jpeg,.png DOC:.pdf,.jpg,.jpeg,.png,.doc,.docx';
        }

        DB::beginTransaction();

        try {
            $businessInfo = new BusinessInfo();
            $businessInfo->business_name    = $validated['business_name'];
            $businessInfo->business_short_name    = $validated['businessShortName'] ?? null;
            $businessInfo->business_domain = $validated['businessDomain'] ?? null;
            $businessInfo->business_allowed_file_types      = $validated['allowedFiletypes'] ?? 'IMG:.jpg,.jpeg,.png DOC:.pdf,.jpg,.jpeg,.png,.doc,.docx';
            $businessInfo->business_logo  = $businessLogoPath ?? null;
            $businessInfo->business_logo_dark   = $businessLogoDarkPath ?? null;
            $businessInfo->business_favicon   = $businessFaviconPath ?? null;
            $businessInfo->business_address   = $validated['businessAddress1'] ?? null;
            $businessInfo->business_address2   = $validated['businessAddress2'] ?? null;
            $businessInfo->business_city   = $validated['businessCity'] ?? null;
            $businessInfo->business_state   = $validated['businessState'] ?? null;
            $businessInfo->business_zip   = $validated['businessZip'] ?? null;
            $businessInfo->business_country   = $validated['businessCountry'] ?? null;
            $businessInfo->business_phone   = $validated['businessPhone'] ?? null;
            $businessInfo->business_email   = $validated['businessEmail'] ?? null;
            $businessInfo->business_website   = $validated['businessWebsite'] ?? null;
            $businessInfo->business_registration_number   = $validated['businessRegistrationNumber'] ?? null;
            $businessInfo->business_registration_number2   = $validated['businessRegistrationNumber2'] ?? null;
            $businessInfo->business_start_date   = $validated['businessStartDate'] ?? null;
            $businessInfo->business_status          = 'active';
            $businessInfo->save();
            
            $id = $businessInfo->id;
            $businessinfo = BusinessInfo::findOrFail($id);

            $businessLogoPath = "";
            $businessLogoDarkPath =  "";
            $businessFaviconPath =  "";

            $fileBusinessLogo = $request->file('businessLogo');
            $fileBusinessLogoDark = $request->file('businessLogoDark');
            $fileBusinessFavicon = $request->file('businessFavicon');

            // Store the file
            $disk = config('filesystems.default', 'local');
            if(!empty($fileBusinessLogo)){
                $path = $fileBusinessLogo->store('businessinfo/' . $id, $disk); 
                $businessLogoPath = Storage::disk($disk)->url($path);
            }
            if(!empty($fileBusinessLogoDark)){
                $path = $fileBusinessLogoDark->store('businessinfo/' . $id, $disk); 
                $businessLogoDarkPath = Storage::disk($disk)->url($path);
            }
            if(!empty($fileBusinessFavicon)){
                $path = $fileBusinessFavicon->store('businessinfo/' . $id, $disk); 
                $businessFaviconPath = Storage::disk($disk)->url($path);
            }
            
            // You can get original name, extension, size, etc.
            //$originalName = $fileBusinessLogo->getClientOriginalName();


            $businessinfoUpdateData = array();
            if(!empty($businessLogoPath)){
                $businessinfoUpdateData['business_logo'] = $businessLogoPath;
            }
            if(!empty($businessLogoDarkPath)){
                $businessinfoUpdateData['business_logo_dark'] = $businessLogoDarkPath;
            }
            if(!empty($businessFaviconPath)){
                $businessinfoUpdateData['business_favicon'] = $businessFaviconPath;
            }
            $businessinfo->update($businessinfoUpdateData);

            DB::commit();

            return redirect()->route('businessinfo.show', $businessInfo->id)
                ->with('success', 'Business Info created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Business Info store failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the business info. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $businessInfo = BusinessInfo::findOrFail($id);
        
        return view('businessInfo.show')->with([
            'businessinfo' => $businessInfo
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id='')
    {
        $businessinfo = new BusinessInfo();
        if(!empty($id)){
            $businessinfo = BusinessInfo::findOrFail($id);
            // return response()->json($businessinfo);
        }

        $branlogoinfo = array();
        $disk = config('filesystems.default', 'local');
        $brandlogofiles = Storage::disk($disk)->files('brandlogos/' . $id);
        $diskToUse = $disk;
        if (empty($brandlogofiles) && $disk !== 'public') {
            $brandlogofiles = Storage::disk('public')->files('brandlogos/' . $id);
            $diskToUse = 'public';
        }

        foreach ($brandlogofiles as $file) {
            $branlogoinfo[] = Storage::disk($diskToUse)->url($file);
        }

        return view('businessinfo.edit')->with([
            'businessinfo' => $businessinfo,
            'branlogoinfo' => $branlogoinfo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //dd($request->all());

        $validated = $request->validate([
            'business_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('business_info', 'business_name')->ignore($id), // Ignores the current business's name
            ],
            'businessShortName'  => 'nullable|string|max:255',
            'businessDomain'   => 'nullable|string|max:255',
            'businessStartDate'       => 'nullable|date|string|max:20',
            'allowedFiletypes'  => 'nullable|string|max:255',
            'businessAddress1'  => 'nullable|string|max:255',
            'businessAddress2'  => 'nullable|string|max:255',
            'businessCity'  => 'nullable|string|max:255',
            'businessState'  => 'nullable|string|max:255',
            'businessZip'  => 'nullable|string|max:255',
            'businessCountry'      => 'nullable|string|max:255',
            'businessPhone'      => 'nullable|string|max:20',
            'businessEmail'       => 'nullable|email|max:255',
            'businessWebsite'       => 'nullable|string|max:255',
            'businessRegistrationNumber'  => 'nullable|string|max:255',
            'businessRegistrationNumber2'  => 'nullable|string|max:255',
            'business_brand_app_name'        => 'required|string|max:255',
            'business_brand_logo_on_top_left'  => 'nullable',
            'business_brand_credit_on_footer_enable'  => 'nullable|integer|min:0|max:1',
            'business_brand_credit_on_footer'  => 'nullable',
            'brandSidebarLogo' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg', 'svg', 'webp'])
                    ->max(50 * 1024),
            ],
            'constructed_brand_logo' => 'nullable|string',
            'brand_logo_show_text' => 'nullable',
            'businessLogo' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg']) // Example: allow specific types
                    ->max(100 * 1024), // Example: max 100MB
            ],
            'businessLogoDark' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg']) // Example: allow specific types
                    ->max(100 * 1024), // Example: max 100MB
            ],
            'businessFavicon' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg', 'ico']) // Example: allow specific types
                    ->max(100 * 1024), // Example: max 100MB
            ],
            'businessStatus' => 'required|string',
            
        ]);

        //format 
        if(!empty($validated['businessPhone'])){
            $validated['businessPhone'] = simple_format_phone($validated['businessPhone']);
        }

        $businessLogoPath = "";
        $businessLogoDarkPath =  "";
        $businessFaviconPath =  "";

        $fileBusinessLogo = $request->file('businessLogo');
        $fileBusinessLogoDark = $request->file('businessLogoDark');
        $fileBusinessFavicon = $request->file('businessFavicon');

        // Store the file
        $disk = config('filesystems.default', 'local');
        if(!empty($fileBusinessLogo)){
            $path = $fileBusinessLogo->store('businessinfo/' . $id, $disk); 
            $businessLogoPath = Storage::disk($disk)->url($path);
        }
        if(!empty($fileBusinessLogoDark)){
            $path = $fileBusinessLogoDark->store('businessinfo/' . $id, $disk); 
            $businessLogoDarkPath = Storage::disk($disk)->url($path);
        }
        if(!empty($fileBusinessFavicon)){
            $path = $fileBusinessFavicon->store('businessinfo/' . $id, $disk); 
            $businessFaviconPath = Storage::disk($disk)->url($path);
        }

        // Update 
        DB::beginTransaction();

        try {
            $businessinfo = BusinessInfo::findOrFail($id);

            if(empty($validated['allowedFiletypes'])){
                $validated['allowedFiletypes'] = $businessinfo->business_allowed_file_types ?: 'IMG:.jpg,.jpeg,.png DOC:.pdf,.jpg,.jpeg,.png,.doc,.docx';
            }

            // Resolve Brand Logo
            $businessBrandLogo = $request->input('business_brand_logo_on_top_left');
            if ($request->hasFile('brandSidebarLogo')) {
                $fileBrandSidebarLogo = $request->file('brandSidebarLogo');
                $path = $fileBrandSidebarLogo->store('brandlogos/' . $id, $disk);
                $brandLogoUrl = Storage::disk($disk)->url($path);
                $appName = $validated['business_brand_app_name'] ?? 'ERP';
                $showText = $request->input('brand_logo_show_text', '1') == '1';
                $textMarkup = $showText ? '<span class="app-brand-text demo menu-text fw-bold ms-3">' . e($appName) . '</span>' : '';
                $businessBrandLogo = '<span class="app-brand-logo demo"><img src="' . $brandLogoUrl . '" style="max-height: 28px; width: auto; object-fit: contain;" alt="' . e($appName) . '"></span>' . $textMarkup;
            } elseif ($request->filled('constructed_brand_logo')) {
                $businessBrandLogo = $request->input('constructed_brand_logo');
            }

            if (empty($businessBrandLogo)) {
                $businessBrandLogo = $businessinfo->business_brand_logo_on_top_left ?? 'ERP';
            }

            // Preserve or set standard Footer HTML
            $businessBrandFooter = $businessinfo->business_brand_credit_on_footer;
            if (empty($businessBrandFooter) || str_contains($businessBrandFooter, 'made with')) {
                $businessBrandFooter = '&copy; ' . date('Y') . ', created by <a href="https://neotericdigital.co.uk/" target="_blank" class="footer-link">Neoteric Digital</a>';
            }
            $businessBrandCreditEnable = $request->input('business_brand_credit_on_footer_enable', $businessinfo->business_brand_credit_on_footer_enable ?? 1);

            $businessinfoUpdateData = [
                'business_name'   => $validated['business_name'],
                'business_short_name' => $validated['businessShortName'] ?? null,
                'business_domain' => $validated['businessDomain'] ?? null,
                'business_allowed_file_types' => $validated['allowedFiletypes'] ?? null,
                'business_address' => $validated['businessAddress1'] ?? null,
                'business_address2' => $validated['businessAddress2'] ?? null,
                'business_city' => $validated['businessCity'] ?? null,
                'business_state' => $validated['businessState'] ?? null,
                'business_zip' => $validated['businessZip'] ?? null,
                'business_country' => $validated['businessCountry'] ?? null,
                'business_phone' => $validated['businessPhone'] ?? null,
                'business_email' => $validated['businessEmail'] ?? null,
                'business_website' => $validated['businessWebsite'] ?? null,
                'business_registration_number'     => $validated['businessRegistrationNumber'] ?? null,
                'business_registration_number2'       => $validated['businessRegistrationNumber2'] ?? null,
                'business_start_date'  => $validated['businessStartDate'] ?? null,
                'business_status' => $validated['businessStatus'],
                'business_brand_app_name'   => $validated['business_brand_app_name'],
                'business_brand_logo_on_top_left' => $businessBrandLogo,
                'business_brand_credit_on_footer_enable' => $businessBrandCreditEnable,
                'business_brand_credit_on_footer' => $businessBrandFooter,
            ];
            if(!empty($businessLogoPath)){
                $businessinfoUpdateData['business_logo'] = $businessLogoPath;
            }
            if(!empty($businessLogoDarkPath)){
                $businessinfoUpdateData['business_logo_dark'] = $businessLogoDarkPath;
            }
            if(!empty($businessFaviconPath)){
                $businessinfoUpdateData['business_favicon'] = $businessFaviconPath;
            }
            
            $businessinfo->update($businessinfoUpdateData);

            DB::commit();

            return redirect()->back()->with('success', 'Business Info updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Business Info update failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the business info. Please try again.');
        }
    }

    public function uploadBrandFiles(Request $request, string $id){
        if ($request->hasFile('brandFile')) {
            
            if ($request->file('brandFile')->isValid()) {
                //
                $validated = $request->validate([
                    'brandFile' => 'mimes:jpg,jpeg,png,svg,webp|max:102400', //100MB
                ]);

                $disk = config('filesystems.default', 'local');
                $fileBrandFile = $request->file('brandFile');
                $path = $fileBrandFile->store('brandlogos/' . $id, $disk); 
                
                return redirect()->back()->with('success', 'File uploaded.');
            }
            else{
                return redirect()->back()->with('error', 'File is not valid.');
            }
        }else{
            return redirect()->back()->with('error', 'File is not selected.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessInfo $businessInfo)
    {
        
    }
}
