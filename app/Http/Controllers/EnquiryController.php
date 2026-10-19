<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Enquiry;
use App\Models\EnquiryAddress;
use App\Models\EnquiryImage;
use App\Models\EnquiryNote;
use App\Models\Lead;
use App\Models\LeadAddress;
use App\Models\LeadImage;
use App\Models\LeadNote;
use App\Models\MailTemplate;
use App\Models\User;
use App\Enums\EnquiryStatus;
use App\Actions\Pipeline\ConvertEnquiryToLead;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

use Mail;

use App\Mail\EnquiryRejectNotify;

class EnquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $data = Enquiry::join('customers', 'enquiries.customer_id', '=', 'customers.id')
                ->select([
                    'enquiries.id',
                    'enquiries.slug',
                    'enquiries.enquiry_name',
                    'enquiries.enquiry',
                    'enquiries.enquiry_status',
                    'customers.company_name',
                    'customers.contact_firstname',
                    'customers.contact_lastname',
                    'customers.contact_email',
                    'customers.contact_phone',
                    'customers.contact_mobile',
                    'enquiries.enquiry_source',
                    'enquiries.created_at'
                ])
                ->whereIn('enquiry_status', ['new', 'on_hold'])
                ->orderByDesc('enquiries.created_at');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    return $row->company_name ?? '-';
                })
                ->orderColumn('company_name', function ($query, $order) {
                    $query->orderBy('customers.company_name', $order);
                })
                ->filterColumn('company_name', function ($query, $keyword) {
                    $query->where('customers.company_name', 'like', "%{$keyword}%");
                })
                ->addColumn('customer_name', function ($row) {
                    return trim(($row->contact_firstname ?? '') . ' ' . ($row->contact_lastname ?? '')) ?: '-';
                })
                ->orderColumn('customer_name', function ($query, $order) {
                    $query->orderBy('customers.contact_firstname', $order)
                          ->orderBy('customers.contact_lastname', $order);
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('customers.contact_firstname', 'like', "%{$keyword}%")
                          ->orWhere('customers.contact_lastname', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_email', function ($row) {
                    return $row->contact_email ?? '-';
                })
                ->orderColumn('customer_email', function ($query, $order) {
                    $query->orderBy('customers.contact_email', $order);
                })
                ->filterColumn('customer_email', function ($query, $keyword) {
                    $query->where('customers.contact_email', 'like', "%{$keyword}%");
                })
                ->addColumn('customer_phone', function ($row) {
                    return $row->contact_phone ?? '-';
                })
                ->orderColumn('customer_phone', function ($query, $order) {
                    $query->orderBy('customers.contact_phone', $order);
                })
                ->filterColumn('customer_phone', function ($query, $keyword) {
                    $query->where('customers.contact_phone', 'like', "%{$keyword}%");
                })
                ->addColumn('customer_mobile', function ($row) {
                    return $row->contact_mobile ?? '-';
                })
                ->orderColumn('customer_mobile', function ($query, $order) {
                    $query->orderBy('customers.contact_mobile', $order);
                })
                ->filterColumn('customer_mobile', function ($query, $keyword) {
                    $query->where('customers.contact_mobile', 'like', "%{$keyword}%");
                })
                ->addColumn('enquiry', function ($row) {
                    return Str::limit(strip_tags($row->enquiry ?? ''), 50);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="/enquiries/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('enquiries.index');
    }

    public function showInprogress(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $data = Enquiry::with(['customer', 'enquiryaddress'])
                ->select('enquiries.*')
                ->where('enquiry_status', 'inprogress')
                ->orderByDesc('created_at');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    return $row->customer?->company_name ?? '-';
                })
                ->orderColumn('company_name', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.company_name', $order);
                })
                ->filterColumn('company_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('company_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_name', function ($row) {
                    return trim(($row->customer?->contact_firstname ?? '') . ' ' . ($row->customer?->contact_lastname ?? '')) ?: '-';
                })
                ->orderColumn('customer_name', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_firstname', $order)
                          ->orderBy('customers.contact_lastname', $order);
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_firstname', 'like', "%{$keyword}%")
                          ->orWhere('contact_lastname', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_email', function ($row) {
                    return $row->customer?->contact_email ?? '-';
                })
                ->orderColumn('customer_email', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_email', $order);
                })
                ->filterColumn('customer_email', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_email', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_phone', function ($row) {
                    return $row->customer?->contact_phone ?? '-';
                })
                ->orderColumn('customer_phone', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_phone', $order);
                })
                ->filterColumn('customer_phone', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_phone', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_mobile', function ($row) {
                    return $row->customer?->contact_mobile ?? '-';
                })
                ->orderColumn('customer_mobile', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_mobile', $order);
                })
                ->filterColumn('customer_mobile', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_mobile', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="/enquiries/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('enquiries.index')->with([
            'inprogress' => true,
        ]);
    }

    public function showCompleted(Request $request)
    {
        return $this->showConverted($request);
    }

    public function showConverted(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $data = Enquiry::with(['customer', 'enquiryaddress'])
                ->select('enquiries.*')
                ->where('enquiry_status', 'converted_to_lead')
                ->orderByDesc('created_at');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    return $row->customer?->company_name ?? '-';
                })
                ->orderColumn('company_name', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.company_name', $order);
                })
                ->filterColumn('company_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('company_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_name', function ($row) {
                    return trim(($row->customer?->contact_firstname ?? '') . ' ' . ($row->customer?->contact_lastname ?? '')) ?: '-';
                })
                ->orderColumn('customer_name', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_firstname', $order)
                          ->orderBy('customers.contact_lastname', $order);
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_firstname', 'like', "%{$keyword}%")
                          ->orWhere('contact_lastname', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_email', function ($row) {
                    return $row->customer?->contact_email ?? '-';
                })
                ->orderColumn('customer_email', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_email', $order);
                })
                ->filterColumn('customer_email', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_email', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_phone', function ($row) {
                    return $row->customer?->contact_phone ?? '-';
                })
                ->orderColumn('customer_phone', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_phone', $order);
                })
                ->filterColumn('customer_phone', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_phone', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_mobile', function ($row) {
                    return $row->customer?->contact_mobile ?? '-';
                })
                ->orderColumn('customer_mobile', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_mobile', $order);
                })
                ->filterColumn('customer_mobile', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_mobile', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="/enquiries/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('enquiries.index')->with([
            'completed' => true,
        ]);
    }

    public function showRejected(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {

            $data = Enquiry::with(['customer', 'enquiryaddress'])
                ->select('enquiries.*')
                ->whereIn('enquiry_status', ['rejected', 'archived'])
                ->orderByDesc('created_at');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    return $row->customer?->company_name ?? '-';
                })
                ->orderColumn('company_name', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.company_name', $order);
                })
                ->filterColumn('company_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('company_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_name', function ($row) {
                    return trim(($row->customer?->contact_firstname ?? '') . ' ' . ($row->customer?->contact_lastname ?? '')) ?: '-';
                })
                ->orderColumn('customer_name', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_firstname', $order)
                          ->orderBy('customers.contact_lastname', $order);
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_firstname', 'like', "%{$keyword}%")
                          ->orWhere('contact_lastname', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_email', function ($row) {
                    return $row->customer?->contact_email ?? '-';
                })
                ->orderColumn('customer_email', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_email', $order);
                })
                ->filterColumn('customer_email', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_email', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_phone', function ($row) {
                    return $row->customer?->contact_phone ?? '-';
                })
                ->orderColumn('customer_phone', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_phone', $order);
                })
                ->filterColumn('customer_phone', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_phone', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('customer_mobile', function ($row) {
                    return $row->customer?->contact_mobile ?? '-';
                })
                ->orderColumn('customer_mobile', function ($query, $order) {
                    $query->leftJoin('customers', 'enquiries.customer_id', '=', 'customers.id')
                          ->orderBy('customers.contact_mobile', $order);
                })
                ->filterColumn('customer_mobile', function ($query, $keyword) {
                    $query->whereHas('customer', function ($q) use ($keyword) {
                        $q->where('contact_mobile', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="/enquiries/' . $row->slug . '" class="edit btn btn-primary btn-sm">Details</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }

        return view('enquiries.index')->with([
            'rejected' => true,
        ]);
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
        // return $request->all();
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'companyName' => 'required_without:customer_id|nullable|string|max:255',
            'contactPersonFirstName' => 'required_without:customer_id|nullable|string|max:255',
            'contactPhone'  => 'required_without_all:customer_id,contactMobile|nullable|max:20',
            'contactMobile' => 'required_without_all:customer_id,contactPhone|nullable|max:20',
            'contactEmail' => 'required_without:customer_id|nullable|email|max:255',
            'enquiry' => 'required|string',
            'enquirydescription' => 'nullable|string',
            'enquiry_postcode' => 'required|string|max:20',
            'enquiry_address' => 'required|string',
            'enquiry_county' => 'nullable|string|max:255',
            'enquiry_country' => 'nullable|string|max:255',
            'attachments.*' => 'nullable|image|mimes:jpg,jpeg,png|max:10024',
            'enquiry_priority' => 'nullable|string|in:low,medium,high',
        ]);

        //if validated then submit again
        if ($request->ajax()) {
            return response()->json(['success'=> 'success', 'redirect' => 'POST']);
        }

        DB::beginTransaction();

        try {
            $customerid = $validated['customer_id'] ?? null;

            if ($customerid) {
                $customer = Customer::find($customerid);
            } else {
                $customer = Customer::create([
                    'company_name' => $validated['companyName'],
                    'contact_firstname' => $validated['contactPersonFirstName'],
                    'contact_lastname' => $validated['contactPersonLastName'] ?? '',
                    'contact_phone' => $validated['contactPhone'] ?? '',
                    'contact_mobile' => $validated['contactMobile'] ?? '',
                    'contact_email' => $validated['contactEmail'],
                    'status' => 'active'
                ]);

                $customerid = $customer->id;

                CustomerAddress::create([
                    'customer_id' => $customerid,
                    'contact_firstname' => $validated['contactPersonFirstName'],
                    'contact_lastname' => $validated['contactPersonLastName'] ?? '',
                    'contact_phone' => $validated['contactPhone'] ?? '',
                    'contact_mobile' => $validated['contactMobile'] ?? '',
                    'contact_email' => $validated['contactEmail'],
                    'address' => $validated['enquiry_address'],
                    'county' => $validated['enquiry_county'],
                    'postcode' => $validated['enquiry_postcode'],
                    'country' => $validated['enquiry_country'],
                    'default' => 'yes',
                    'address_type' => 'site',
                ]);
            }

            $enquiry = Enquiry::create([
                'enquiry_name' => randomenquiryGenerator(),
                'customer_id' => $customerid,
                'enquiry' => $validated['enquiry'],
                'enquiry_description' => $validated['enquirydescription'],
                'enquiry_type' => 'query',
                'enquiry_category'     => $request->input('roofing_info') ? implode(',', $request->input('roofing_info')) : null,
                'annual_maintenance'  => in_array('maintenance', $request->input('annual_maintenance', [])),
                'installations'       => in_array('install', $request->input('safety', [])),
                'repairs'             => in_array('repairs', $request->input('safety', [])),
                'testing'             => in_array('testing', $request->input('safety', [])),
                'enquiry_status' => 'new',
                'enquiry_priority' => $validated['enquiry_priority'] ?? 'low',
                'enquiry_source' => $request->input('enquiry_source') ?? 'call',
            ]);

            EnquiryAddress::create([
                'enquiry_id' => $enquiry->id,
                'contact_firstname' => $validated['contactPersonFirstName'] ?? $customer->contact_firstname,
                'contact_lastname' => $validated['contactPersonLastName'] ?? $customer->contact_lastname,
                'contact_phone' => $validated['contactPhone'] ?? $customer->contact_phone,
                'contact_mobile' => $validated['contactMobile'] ?? $customer->contact_mobile,
                'contact_email' => $validated['contactEmail'] ?? $customer->contact_email,
                'address' => $validated['enquiry_address'],
                'county' => $validated['enquiry_county'],
                'postcode' => $validated['enquiry_postcode'],
                'country' => $validated['enquiry_country']
            ]);


            if ($request->hasFile('attachments')) {
                $disk = config('filesystems.default', 'local');

                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('uploads/enqueries', $disk);

                    if (!$path) {
                        throw new \Exception("Failed to store file: " . $file->getClientOriginalName());
                    }

                    EnquiryImage::create([
                        'enquiry_id' => $enquiry->id,
                        'image_path' => $path,
                        'image_name' => $file->getClientOriginalName(),
                        'image_url' => Storage::disk($disk)->url($path),
                        'source' => $disk,
                    ]);
                }
            }

            $enquiryNote = $request->input('enquiryNote');
            if ($enquiryNote) {
                EnquiryNote::create([
                    'enquiry_id' => $enquiry->id,
                    'note_by' => Auth::id(),
                    'note' => $enquiryNote,
                    'note_type' => 'internal',
                    'note_status' => 'active',
                ]);
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

    public function show(string $key)
    {
        $enquiry = Enquiry::with(['customer', 'customer.billingaddress', 'customer.siteaddress', 'enquiryaddress', 'enquiryimages', 'enquirynotes', 'enquirynotes.users'])->where('slug', $key)->first();

        if (!$enquiry) {
            return redirect()->route('enquiries.index')->with('error', 'Enquiry not found.');
        }
        $mailtemplates = MailTemplate::where('status', 'active')->orderBy('id', 'desc')->get();

        $leadmanagers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['job-manager', 'lead-job-manager']);
        })->get();

        return view('enquiries.show', compact('enquiry', 'mailtemplates', 'leadmanagers'));
    }

    public function enqueryNotes(Request $request, $enquiry_id)
    {
        $validated = $request->validate([
            'note'       => 'required|string',
        ]);
        
        $safeNote = Str::markdown($validated['note'], [
            'html_input' => 'allow', // strip
            'allow_unsafe_links' => false,
        ]);
        
        $checkEnquiry = Enquiry::find($enquiry_id);
        if (!$checkEnquiry) {
            return response()->json(['status' => 'error', 'message' => 'Enquiry not found.']);
        }

        $enquirynote = EnquiryNote::create([
            'enquiry_id'  => $enquiry_id,
            'note_by'     => Auth::id(),
            'note'        => $safeNote,
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
        

        $disk = config('filesystems.default', 'local');

        if ($request->hasFile('enquiryphoto')) {
            $file = $request->file('enquiryphoto');
            $path = $file->store('uploads/enqueries', $disk);

            if (!$path) {
                return response()->json(['status' => 'error', 'message' => 'Failed to store image.']);
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
                'success' => 'Enquiry image added successfully.'
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

    public function updateEnquiryOtherOptions(Request $request, $id)
    {
        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            return response()->json([
                'status' => 'error',
                'message' => 'Enquiry not found.'
            ]);
        }

        $enquiry->update([
            'enquiry_category'     => $request->input('enquiry_info') ? implode(',', $request->input('enquiry_info')) : null,
            'annual_maintenance'  => $request->input('annual_maintenance') ?? false,
            'installations'       => in_array('install', $request->input('safety_type', [])),
            'repairs'             => in_array('repairs', $request->input('safety_type', [])),
            'testing'             => in_array('testing', $request->input('safety_type', [])),
            'enquiry_priority' => $request->enquiry_priority ?? 'low',
            'enquiry_source' => $request->enquiry_source ?? 'call',
        ]);

        return back()->with([
            'status' => 'success',
            'message' => 'Enquiry options updated successfully.'
        ]);
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
        $allowedStatuses = implode(',', EnquiryStatus::values());
        $validated = $request->validate([
            'status' => "required|string|in:$allowedStatuses",
        ]);

        $enquiry = Enquiry::find($id);
        if (!$enquiry) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Enquiry not found.'], 404);
            }
            return redirect()->back()->with('error', 'Enquiry not found.');
        }

        $currentStatus = EnquiryStatus::tryFrom($enquiry->enquiry_status ?? 'new');
        $targetStatus = EnquiryStatus::tryFrom($validated['status']);

        if (!$targetStatus) {
            return response()->json(['status' => 'error', 'message' => 'Invalid status provided.'], 422);
        }

        if ($currentStatus && !$currentStatus->canTransitionTo($targetStatus)) {
            $msg = "Cannot transition enquiry from {$currentStatus->value} to {$targetStatus->value}.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $enquiry->update(['enquiry_status' => $targetStatus->value]);

        // update lead status to archived if enquiry is rejected/archived
        $lead = Lead::where('enquiry_id', '=', $enquiry->id)->first();
        if ($lead && in_array($targetStatus, [EnquiryStatus::REJECTED, EnquiryStatus::ARCHIVED])) {
            $lead->update(['lead_status' => 'archived']);
        }

        if ($targetStatus === EnquiryStatus::REJECTED) {
            $emailRes = $this->emailNotify($request, $enquiry->id);
            if ($emailRes->getStatusCode() >= 400) {
                return $emailRes;
            }
            return response()->json(['status' => 'success', 'message' => 'Enquiry status set to reject successfully.']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Enquiry updated successfully.']);
        }

        return redirect()->back()->with('success', 'Enquiry updated successfully.');
    }

    public function enquerytoLeadConversion(Request $request, ConvertEnquiryToLead $action)
    {
        $validated = $request->validate([
            'enquiry_id' => 'required|exists:enquiries,id',
            'status'     => 'nullable|string',
        ]);

        try {
            $lead = $action->execute((int)$validated['enquiry_id'], $request->users ?? []);
            return response()->json([
                'status'  => 'success',
                'message' => 'Enquiry converted to lead successfully.',
                'lead'    => $lead
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $enquiry = Enquiry::findOrFail($id);
        return redirect()->route('enquiries.show', $enquiry->slug);
    }

    public function update(Request $request, $id)
    {
        $enquiry = Enquiry::findOrFail($id);
        if ($request->filled('enquirydescription')) {
            $enquiry->enquiry_description = $request->input('enquirydescription');
        }
        if ($request->filled('enquiry')) {
            $enquiry->enquiry = $request->input('enquiry');
        }
        $enquiry->save();

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Enquiry updated successfully.']);
        }
        return redirect()->route('enquiries.show', $enquiry->slug)->with('success', 'Enquiry updated successfully.');
    }

    public function emailNotify(Request $request, ?int $enquiryId = null)
    {
        $validated = $request->validate([
            'sendtoemail' => 'required|email',
            'mailsubject' => 'required|string|max:255',
            'mailmessage' => 'required|string',
            'enquiry_id'  => 'nullable|exists:enquiries,id',
        ]);

        $targetEnquiryId = $enquiryId ?? $request->input('enquiry_id');
        $destination = trim($validated['sendtoemail']);

        // Anti-open-relay guard: destination must match an email associated with this enquiry
        if ($targetEnquiryId) {
            $enquiry = Enquiry::with(['customer', 'enquiryaddress'])->find($targetEnquiryId);
            if ($enquiry) {
                $allowedEmails = array_filter([
                    $enquiry->customer?->contact_email,
                    $enquiry->enquiryaddress?->contact_email,
                ]);

                if (!empty($allowedEmails) && !in_array(strtolower($destination), array_map('strtolower', $allowedEmails))) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Recipient email does not match any contact associated with this enquiry.'
                    ], 422);
                }
            }
        }

        // Header injection sanitization
        $subject = str_replace(["\r", "\n"], '', $validated['mailsubject']);
        $message = $validated['mailmessage'];

        try {
            Mail::to($destination)->queue(new EnquiryRejectNotify($subject, $message));
            return response()->json(['status' => 'success', 'message' => 'Email notification sent successfully.']);
        } catch (Exception $e) {
            Log::error('Outbound mail error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to send email notification.'], 500);
        }
    }
}
