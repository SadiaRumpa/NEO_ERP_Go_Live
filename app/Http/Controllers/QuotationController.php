<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationSection;
use App\Models\QuotationSectionAttachment;
use App\Models\QuotationSectionContent;
use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateSection;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller
{
    public function index(Request $request, $leadid)
    {
        $leaddata = Lead::where('id', $leadid)->first();
        if (!$leaddata) {
            return redirect()->route('leads.index')->with('error', 'Lead not found.');
        }

        $customerdata = Customer::with(['billingaddress'])
            ->where('id', $leaddata->customer_id)->first();

        $quotationTemplates = QuotationTemplate::where('status', 1)
            ->orderBy('template_name', 'asc')
            ->get();

        if ($request->ajax()) {
            $data = Quotation::select('*')->with(['jobType'])->where('lead_id', $leadid);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $showUrl = route('quotations.show', $row->id);
                    return '
                    <a href="' . $showUrl . '" class="shoq btn btn-primary btn-sm">Show</a>
                    <a href="' . route('quotations.edit', $row->id) . '" class="edit btn btn-warning btn-sm">Edit</a>
                    <button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('quotations.index')->with([
            'leaddata' => $leaddata,
            'customerdata' => $customerdata,
            'leadid' => $leadid,
            'quotationTemplates' => $quotationTemplates,
        ]);
    }

    public function create($lead_id, $template_id)
    {
        // return $request->all();
        $leaddata = Lead::with(['leadaddress'])->where('id', $lead_id)->first();
        // return $leaddata;
        if (!$leaddata) {
            return redirect()->route('leads.index')->with('error', 'Lead not found.');
        }

        $customerdata = Customer::where('id', $leaddata->customer_id)->first();

        $quotationTemplate = QuotationTemplate::with(['sections', 'sections.samples'])
            ->where('id', $template_id)->first();

        return view('quotations.create')->with([
            'leaddata' => $leaddata,
            'customerdata' => $customerdata,
            'leadid' => $lead_id,
            'quotationTemplate' => $quotationTemplate,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'customer_id' => 'required|exists:customers,id',
            'job_type_id' => 'required|exists:job_types,id',
            'quotation_template_id' => 'required|exists:quotation_templates,id',
            'quotation_version' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Create the quotation
            $quotation = Quotation::create([
                'lead_id' => $request->lead_id,
                'customer_id' => $request->customer_id,
                'job_type_id' => $request->job_type_id,
                'quotation_template_id' => $request->quotation_template_id,
                'quotation_date' => $request->quotation_date,
                'quotation_version' => $request->quotation_version,
                'cover_letter' => $request->cover_letter,
                'remarks' => $request->remarks,
                'status' => 'draft',
            ]);

            // Copy template sections to quotation sections
            $templateSections = QuotationTemplateSection::where('quotation_template_id', $request->quotation_template_id)
                ->orderBy('order')
                ->get();

            foreach ($templateSections as $templateSection) {
                // Generate slug or use from template
                $slug = $templateSection->section_slug ?? Str::slug($templateSection->section_name);

                // Create quotation section
                $quotationSection = $quotation->sections()->create([
                    'section_name' => $templateSection->section_name,
                    'section_type' => $templateSection->section_type,
                    'has_attachments' => $templateSection->has_attachments,
                    'order' => $templateSection->order,
                ]);

                // Insert section content if available
                if ($request->filled($slug)) {
                    QuotationSectionContent::updateOrCreate(
                        ['quotation_section_id' => $quotationSection->id],
                        [
                            'sample_type' => $templateSection->section_type,
                            'sample_source' => $request->input($slug),
                        ]
                    );
                }

                // Handle file attachments if applicable
                if ($templateSection->has_attachments && $request->hasFile("attachments.$slug")) {

                    foreach ($request->file("attachments.$slug") as $file) {

                        $disk = env('FILESYSTEM_DISK', 'local');
                        $path = $file->store('uploads/quotations', $disk);

                        if (!$path) {
                            return response()->json(['status' => 'error', 'message' => 'Failed to store image.']);
                        }

                        if ($disk === 's3') {
                            Storage::disk('s3')->setVisibility($path, 'public');
                        }

                        QuotationSectionAttachment::create([
                            'quotation_section_id'  => $quotationSection->id,
                            'attachment_path'  => $path,
                            'attachment_name'  => $file->getClientOriginalName(),
                            'attachment_url'   => Storage::disk($disk)->url($path),
                            'source'      => $disk
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('quotations.show', $quotation->id)
                ->with('success', 'Quotation created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create quotation: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $quotation = Quotation::with(['lead', 'customer', 'customer.billingaddress', 'sections' => function ($q) {
            $q->orderBy('order', 'desc');
        }, 'sections.content', 'sections.attachments'])
            ->where('id', $id)->first();

        if (!$quotation) {
            return redirect()->route('quotations.index')->with('error', 'Quotation not found.');
        }
        // return $quotation;

        return view('quotations.show')->with([
            'quotation' => $quotation,
        ]);
    }

    public function edit($id)
    {
        $quotation = Quotation::with(['lead','quotationTemplate', 'customer', 'customer.billingaddress', 'sections' => function ($q) {
            $q->orderBy('order', 'desc');
        }, 'sections.content', 'sections.attachments'])
            ->where('id', $id)->first();

        if (!$quotation) {
            return redirect()->route('quotations.index')->with('error', 'Quotation not found.');
        }

        // return $quotation;

        return view('quotations.edit')->with([
            'quotation' => $quotation,
        ]);
    }

    public function downloadQuotationPdf($quotationId)
    {
        $quotation = Quotation::with(['lead', 'customer', 'customer.billingaddress', 'sections' => function ($q) {
            $q->orderBy('order', 'desc');
        }, 'sections.content', 'sections.attachments'])
            ->findOrFail($quotationId);
        $pdf = Pdf::loadView('pdf.quotaions', compact('quotation'));

        return $pdf->download('quotation_' . $quotation->id . '.pdf');
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $quotation = Quotation::with('sections.attachments', 'sections.content')->findOrFail($id);
            foreach ($quotation->sections as $section) {
                // Delete attachments
                $section->attachments()->delete();
                // Delete content
                $section->content()->delete();
            }

            // Delete sections
            $quotation->sections()->delete();
            // Delete the quotation
            $quotation->delete();
            DB::commit();

            return response()->json(['success' => 'Quotation deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to delete quotation. ' . $e->getMessage()], 500);
        }
    }
}
