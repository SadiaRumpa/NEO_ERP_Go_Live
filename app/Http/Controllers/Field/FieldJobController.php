<?php

namespace App\Http\Controllers\Field;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\Fjob;
use App\Models\JobSafetyChecklist;
use App\Models\JobSitePhoto;
use App\Models\JobSiteVisit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FieldJobController extends Controller
{
    /**
     * Mobile field engineer home: Today's assigned jobs and active site visits.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // If staff/engineer, look for schedules assigned to them; otherwise show active jobs
        $jobsQuery = Fjob::with(['customer', 'jobaddress', 'schedules.workers']);

        if ($user && $user->hasRole('staff') && !$user->hasRole('super_admin')) {
            $jobsQuery->whereHas('schedules.workers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $jobs = $jobsQuery->whereNotIn('job_status', [JobStatus::ARCHIVED->value, JobStatus::COMPLETED->value])
            ->latest('id')
            ->get();

        $activeVisit = JobSiteVisit::where('user_id', $user?->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        return view('field.index', compact('jobs', 'activeVisit'));
    }

    /**
     * Mobile field engineer view for a specific job.
     */
    public function show(int $id): View
    {
        $user = Auth::user();
        $job = Fjob::with(['customer', 'jobaddress', 'schedules', 'notes', 'images'])->findOrFail($id);

        $currentVisit = JobSiteVisit::with(['safetyChecklist', 'photos'])
            ->where('job_id', $job->id)
            ->where('user_id', $user?->id)
            ->whereDate('created_at', Carbon::today())
            ->latest('id')
            ->first();

        $photos = JobSitePhoto::where('job_id', $job->id)->latest('id')->get();

        return view('field.show', compact('job', 'currentVisit', 'photos'));
    }

    /**
     * Record arrival on site with geo-coordinates.
     */
    public function arrive(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $job = Fjob::findOrFail($id);

        $validated = $request->validate([
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $visit = JobSiteVisit::firstOrCreate(
            [
                'job_id'     => $job->id,
                'user_id'    => $user->id,
                'status'     => 'in_progress',
            ],
            [
                'arrived_at' => now(),
                'latitude'   => $validated['latitude'] ?? null,
                'longitude'  => $validated['longitude'] ?? null,
            ]
        );

        // Advance job status to inprogress if it is confirmed/new
        if (in_array($job->job_status, ['new', 'confirmed'])) {
            $job->job_status = JobStatus::INPROGRESS->value;
            $job->save();
        }

        return back()->with('success', 'Arrival logged successfully. Time: ' . now()->format('H:i'));
    }

    /**
     * Submit Dynamic Site RAMS / Safety Checklist.
     */
    public function submitSafetyChecklist(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $job = Fjob::findOrFail($id);

        $visit = JobSiteVisit::where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->firstOrFail();

        $validated = $request->validate([
            'working_at_height_passed' => 'nullable|boolean',
            'ppe_compliant'            => 'nullable|boolean',
            'edge_protection_verified' => 'nullable|boolean',
            'weather_acceptable'       => 'nullable|boolean',
            'risk_assessment_notes'    => 'nullable|string',
            'signed_by_worker'         => 'required|string|max:100',
        ]);

        JobSafetyChecklist::updateOrCreate(
            ['job_site_visit_id' => $visit->id],
            [
                'job_id'                   => $job->id,
                'user_id'                  => $user->id,
                'working_at_height_passed' => !empty($validated['working_at_height_passed']),
                'ppe_compliant'            => !empty($validated['ppe_compliant']),
                'edge_protection_verified' => !empty($validated['edge_protection_verified']),
                'weather_acceptable'       => !empty($validated['weather_acceptable']),
                'risk_assessment_notes'    => $validated['risk_assessment_notes'] ?? null,
                'signed_by_worker'         => $validated['signed_by_worker'],
                'completed_at'             => now(),
            ]
        );

        return back()->with('success', 'Site Safety Checklist & RAMS signed off successfully.');
    }

    /**
     * Upload an on-site photo with geolocation.
     */
    public function uploadPhoto(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $job = Fjob::findOrFail($id);

        $validated = $request->validate([
            'photo'     => 'required|image|max:10240', // 10MB
            'caption'   => 'nullable|string|max:255',
            'stage'     => 'required|in:before,during,after',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $file = $request->file('photo');
        $fileName = 'site_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('site_photos', $fileName, 'public');

        $activeVisit = JobSiteVisit::where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();

        JobSitePhoto::create([
            'job_site_visit_id' => $activeVisit?->id,
            'job_id'            => $job->id,
            'user_id'           => $user->id,
            'photo_path'        => $path,
            'photo_name'        => $fileName,
            'caption'           => $validated['caption'] ?? null,
            'stage'             => $validated['stage'],
            'latitude'          => $validated['latitude'] ?? null,
            'longitude'         => $validated['longitude'] ?? null,
        ]);

        return back()->with('success', 'Site photo uploaded successfully.');
    }

    /**
     * Capture customer electronic sign-off and complete site visit.
     */
    public function completeVisit(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $job = Fjob::findOrFail($id);

        $visit = JobSiteVisit::where('job_id', $job->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->firstOrFail();

        $validated = $request->validate([
            'customer_signature'   => 'required|string',
            'customer_signer_name' => 'required|string|max:150',
            'notes'                => 'nullable|string',
            'mark_job_completed'   => 'nullable|boolean',
        ]);

        $visit->update([
            'status'               => 'completed',
            'departed_at'          => now(),
            'customer_signature'   => $validated['customer_signature'],
            'customer_signer_name' => $validated['customer_signer_name'],
            'signed_at'            => now(),
            'notes'                => $validated['notes'] ?? $visit->notes,
        ]);

        if (!empty($validated['mark_job_completed'])) {
            $job->job_status = JobStatus::COMPLETED->value;
            $job->job_closed_at = now();
            $job->job_closed_by = $user->id;
            $job->save();
        }

        return redirect()->route('field.jobs.index')->with('success', 'Site visit completed with customer sign-off.');
    }
}
