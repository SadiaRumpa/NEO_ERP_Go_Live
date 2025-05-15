<?php

namespace App\Http\Controllers;

use App\Models\JobType;
use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateSection;
use App\Models\QuotationTemplateSectionSample;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class QuotationTemplateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = QuotationTemplate::select('*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $showUrl = route('quotationtemplates.show', $row->id); // Edit Route
                    $editUrl = route('quotationtemplates.edit', $row->id); // Edit Route
                    $deleteUrl = route('quotationtemplates.destroy', $row->id); // Delete Route

                    return '
                    <a href="' . $showUrl . '" class="shoq btn btn-primary btn-sm">Show</a>
                    <a href="' . $editUrl . '" class="edit btn btn-warning btn-sm">Edit</a>
                    <button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('quotationtemplates.index');
    }

    public function create()
    {
        $jobtype = JobType::orderBy('name', 'asc')->get();
        return view('quotationtemplates.create')->with(['jobtype' => $jobtype]);
    }

    public function store(Request $request)
    {
        // return $request->all();
        $validation = $request->validate([
            'jobtype' => 'required|exists:job_types,slug',
            'template_name' => 'required|string',
        ]);

        $quotationTemplate = new QuotationTemplate();
        $quotationTemplate->job_type_id = JobType::where('slug', $validation['jobtype'])->first()->id;
        $quotationTemplate->job_type_name = $validation['jobtype'];
        $quotationTemplate->template_name = $validation['template_name'];
        $quotationTemplate->status = 1; // Assuming status is 1 for active
        $quotationTemplate->save();

        $quotationTemplateid = $quotationTemplate->id;

        // Save each section from the repeater
        foreach ($request->input('group-a', []) as $index => $section) {
            QuotationTemplateSection::create([
                'quotation_template_id' => $quotationTemplateid,
                'section_name'         => $section['section_name'],
                'section_type'         => $section['section_type'],
                'is_required'          => $section['is_required'],
                'has_attachments'      => $section['has_attachment'], // match key name
                'order'                => $index + 1,
            ]);
        }

        return redirect()->route('quotationtemplates.show', $quotationTemplateid)->with('success', 'Quotation template created successfully.');
    }

    public function show($id)
    {
        $quotationTemplate = QuotationTemplate::with(['sections', 'sections.samples'])->findOrFail($id);
        // return $quotationTemplate;
        return view('quotationtemplates.show', compact('quotationTemplate'));
    }

    public function edit($id)
    {
        return view('quotationtemplate.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Update the quotation template
        return redirect()->route('quotationtemplate.index')->with('success', 'Quotation template updated successfully.');
    }

    public function destroy($id)
    {
        // Delete the quotation template
        return redirect()->route('quotationtemplate.index')->with('success', 'Quotation template deleted successfully.');
    }

    public function populateSections(Request $request)
    {
        // return $request->all();
        $validation = $request->validate([
            'quotation_template_id' => 'required|exists:quotation_templates,id',
        ]);
        $template = QuotationTemplate::with('sections')->findOrFail($validation['quotation_template_id']);

        foreach ($template->sections as $section) {
            $slug = $section->section_slug;

            if ($request->has($slug)) {
                $content = $request->input($slug);

                QuotationTemplateSectionSample::updateOrCreate(
                    ['quotation_template_section_id' => $section->id],
                    [
                        'sample_type' => $section->section_type, // or dynamic if needed
                        'sample_source' => $content,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Samples saved successfully.');
    }
}
