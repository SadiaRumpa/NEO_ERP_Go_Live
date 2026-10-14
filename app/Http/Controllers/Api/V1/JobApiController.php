<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\JobStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\JobResource;
use App\Models\Fjob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApiController extends ApiController
{
    /**
     * Display a listing of jobs with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Fjob::with('customer');

        if ($request->filled('status')) {
            $query->where('job_status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $jobs = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($jobs, JobResource::class);
    }

    /**
     * Display the specified job.
     */
    public function show(int $id): JsonResponse
    {
        $job = Fjob::with(['customer', 'lead', 'enquiry'])->find($id);

        if (!$job) {
            return $this->errorResponse('Job not found.', 404);
        }

        return $this->successResponse(new JobResource($job));
    }

    /**
     * Store a newly created job.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'lead_id'            => 'nullable|exists:leads,id',
            'enquiry_id'         => 'nullable|exists:enquiries,id',
            'annual_maintenance' => 'nullable|boolean',
            'installations'      => 'nullable|boolean',
            'repairs'            => 'nullable|boolean',
            'testing'            => 'nullable|boolean',
            'assigned_to'        => 'nullable|array',
        ]);

        $job = Fjob::create([
            'customer_id'        => $validated['customer_id'],
            'job_name'           => 'Job for Customer #' . $validated['customer_id'],
            'slug'               => 'job-' . uniqid(),
            'lead_id'            => $validated['lead_id'] ?? 0,
            'enquiry_id'         => $validated['enquiry_id'] ?? 0,
            'job_status'         => JobStatus::NEW->value,
            'annual_maintenance' => $validated['annual_maintenance'] ?? false,
            'installations'      => $validated['installations'] ?? false,
            'repairs'            => $validated['repairs'] ?? false,
            'testing'            => $validated['testing'] ?? false,
            'assigned_to'        => $validated['assigned_to'] ?? null,
        ]);

        return $this->successResponse(new JobResource($job->load('customer')), 'Job created successfully.', 201);
    }

    /**
     * Update the status of the job adhering to the state machine.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $job = Fjob::find($id);

        if (!$job) {
            return $this->errorResponse('Job not found.', 404);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        $currentEnum = JobStatus::tryFrom($job->job_status);
        $newEnum = JobStatus::tryFrom($request->input('status'));

        if (!$newEnum) {
            return $this->errorResponse('Invalid job status.', 422);
        }

        if ($currentEnum && !$currentEnum->canTransitionTo($newEnum)) {
            return $this->errorResponse(
                "Illegal status transition from '{$currentEnum->value}' to '{$newEnum->value}'.",
                422
            );
        }

        $job->job_status = $newEnum->value;
        $job->save();

        return $this->successResponse(new JobResource($job), 'Job status updated successfully.');
    }
}
