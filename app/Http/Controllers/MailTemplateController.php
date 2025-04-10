<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use Illuminate\Http\Request;

class MailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MailTemplate::latest()->paginate(5);
        return view('mailtemplates.index', compact('data'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
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
