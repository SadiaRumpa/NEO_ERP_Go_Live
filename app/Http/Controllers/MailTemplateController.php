<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\MailTemplate;
use Illuminate\Http\Request;

class MailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = $this->getPerPage(10);
        $data = MailTemplate::latest()->paginate($perPage)->withQueryString();
        return view('mailtemplates.index', compact('data', 'perPage'))
            ->with('i', ($data->currentPage() - 1) * $data->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('mailtemplates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required',
            'subject' => 'required',
            'mailbody' => 'required',
        ]);

        $mailTemplate = new MailTemplate();
        $mailTemplate->name = $request->name;
        $mailTemplate->subject = $request->subject;
        $mailTemplate->body = $request->mailbody;
        $mailTemplate->status = $request->status;
        $mailTemplate->save();

        return redirect()->route('mailtemplates.index')
            ->with('success', 'Mail Template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($mailid)
    {
        $mailTemplate = MailTemplate::find($mailid);
        if (!$mailTemplate) {
            return redirect()->route('mailtemplates.show')
                ->with('error', 'Mail Template not found.');
        }
        return view('mailtemplates.show', compact('mailTemplate'));
    }


    public function showmailtemplate($id, $rel_type='', $rel_id = 0)
    {
        $template = MailTemplate::findOrFail($id);

        $body = $template->body;
        $variables = ['{t}' => '', '{fn}' => '', '{ln}' => ''];

        if ($rel_type == 'enquiry' && $rel_id) {
            $enquiry = Enquiry::with('customer')->find($rel_id);
            if ($enquiry && $enquiry->customer) {
                $variables['{fn}'] = $enquiry->customer->contact_firstname ?? '';
                $variables['{ln}'] = $enquiry->customer->contact_lastname ?? '';
            }
        } elseif ($rel_type == 'lead' && $rel_id) {
            $lead = \App\Models\Lead::with('customer')->find($rel_id);
            if ($lead && $lead->customer) {
                $variables['{fn}'] = $lead->customer->contact_firstname ?? '';
                $variables['{ln}'] = $lead->customer->contact_lastname ?? '';
            }
        } elseif (($rel_type == 'job' || $rel_type == 'fjob') && $rel_id) {
            $job = \App\Models\Fjob::with('customer')->find($rel_id);
            if ($job && $job->customer) {
                $variables['{fn}'] = $job->customer->contact_firstname ?? '';
                $variables['{ln}'] = $job->customer->contact_lastname ?? '';
            }
        }

        $replacedContent = str_replace(array_keys($variables), array_values($variables), $body);

        return response()->json([
            'message' => $replacedContent,
        ]);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($mailid)
    {
        $mailTemplate = MailTemplate::find($mailid);
        if (!$mailTemplate) {
            return redirect()->route('mailtemplates.index')
                ->with('error', 'Mail Template not found.');
        }
        // dd($mailTemplate);
        return view('mailtemplates.edit', compact('mailTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $mailid)
    {
        $request->validate([
            'name' => 'required',
            'subject' => 'required',
            'mailbody' => 'required',
        ]);
        $mailTemplate = MailTemplate::find($mailid);
        if (!$mailTemplate) {
            return redirect()->route('mailtemplates.index')
                ->with('error', 'Mail Template not found.');
        }

        $mailTemplate->name = $request->name;
        $mailTemplate->subject = $request->subject;
        $mailTemplate->body = $request->mailbody;
        $mailTemplate->status = $request->status;
        $mailTemplate->save();

        return redirect()->route('mailtemplates.edit', $mailid)
            ->with('success', 'Mail Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($mailid)
    {
        $mailTemplate = MailTemplate::find($mailid);
        if (!$mailTemplate) {
            return redirect()->route('mailtemplates.index')
                ->with('error', 'Mail Template not found.');
        }
        // Delete the mail template
        // $mailTemplate->attachments()->delete(); // Assuming you have a relationship for attachments
        $mailTemplate->delete();

        return redirect()->route('mailtemplates.index')
            ->with('success', 'Mail Template deleted successfully.');
    }
}
