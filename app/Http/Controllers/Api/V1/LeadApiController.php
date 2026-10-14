<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Pipeline\ConvertLeadToJob;
use App\Enums\LeadStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\JobResource;
use App\Http\Resources\Api\V1\LeadResource;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadApiController extends ApiController
{
    /**
     * Display a listing of leads with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::with('customer');

        if ($request->filled('status')) {
            $query->where('lead_status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $leads = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($leads, LeadResource::class);
    }

    /**
     * Display the specified lead.
     */
    public function show(int $id): JsonResponse
    {
        $lead = Lead::with(['customer', 'enquiry', 'addresses', 'images', 'notes'])->find($id);

        if (!$lead) {
            return $this->errorResponse('Lead not found.', 404);
        }

        return $this->successResponse(new LeadResource($lead));
    }

    /**
     * Store a newly created lead.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'enquiry_id'         => 'nullable|exists:enquiries,id',
            'annual_maintenance' => 'nullable|boolean',
            'installations'      => 'nullable|boolean',
            'repairs'            => 'nullable|boolean',
            'testing'            => 'nullable|boolean',
            'assigned_to'        => 'nullable|array',
        ]);

        $lead = Lead::create([
            'customer_id'        => $validated['customer_id'],
            'lead_name'          => 'Lead for Customer #' . $validated['customer_id'],
            'slug'               => 'lead-' . uniqid(),
            'enquiry_id'         => $validated['enquiry_id'] ?? 0,
            'lead_status'        => LeadStatus::NEW->value,
            'annual_maintenance' => $validated['annual_maintenance'] ?? false,
            'installations'      => $validated['installations'] ?? false,
            'repairs'            => $validated['repairs'] ?? false,
            'testing'            => $validated['testing'] ?? false,
            'assigned_to'        => $validated['assigned_to'] ?? null,
        ]);

        return $this->successResponse(new LeadResource($lead->load('customer')), 'Lead created successfully.', 201);
    }

    /**
     * Update the status of the lead adhering to the state machine.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return $this->errorResponse('Lead not found.', 404);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        $currentEnum = LeadStatus::tryFrom($lead->lead_status);
        $newEnum = LeadStatus::tryFrom($request->input('status'));

        if (!$newEnum) {
            return $this->errorResponse('Invalid lead status.', 422);
        }

        if ($currentEnum && !$currentEnum->canTransitionTo($newEnum)) {
            return $this->errorResponse(
                "Illegal status transition from '{$currentEnum->value}' to '{$newEnum->value}'.",
                422
            );
        }

        $lead->lead_status = $newEnum->value;
        $lead->save();

        return $this->successResponse(new LeadResource($lead), 'Lead status updated successfully.');
    }

    /**
     * Convert a lead to a job via the transactional pipeline action.
     */
    public function convertToJob(int $id, ConvertLeadToJob $action): JsonResponse
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return $this->errorResponse('Lead not found.', 404);
        }

        try {
            $job = $action->execute($lead);
            return $this->successResponse(new JobResource($job), 'Lead successfully converted to job.', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 409);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
