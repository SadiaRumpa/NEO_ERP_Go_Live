<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Pipeline\ConvertEnquiryToLead;
use App\Enums\EnquiryStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\EnquiryResource;
use App\Http\Resources\Api\V1\LeadResource;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnquiryApiController extends ApiController
{
    /**
     * Display a listing of enquiries with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Enquiry::with('customer');

        if ($request->filled('status')) {
            $query->where('enquiry_status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $enquiries = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($enquiries, EnquiryResource::class);
    }

    /**
     * Display the specified enquiry.
     */
    public function show(int $id): JsonResponse
    {
        $enquiry = Enquiry::with(['customer', 'addresses', 'images', 'notes'])->find($id);

        if (!$enquiry) {
            return $this->errorResponse('Enquiry not found.', 404);
        }

        return $this->successResponse(new EnquiryResource($enquiry));
    }

    /**
     * Store a newly created enquiry.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'enquiry'            => 'required|string',
            'enquiry_name'       => 'nullable|string|max:255',
            'annual_maintenance' => 'nullable|boolean',
            'installations'      => 'nullable|boolean',
            'repairs'            => 'nullable|boolean',
            'testing'            => 'nullable|boolean',
        ]);

        $enquiryName = !empty($validated['enquiry_name'])
            ? $validated['enquiry_name']
            : (substr($validated['enquiry'], 0, 50) ?: 'Enquiry #' . uniqid());

        $enquiry = Enquiry::create([
            'customer_id'        => $validated['customer_id'],
            'enquiry_name'       => $enquiryName,
            'enquiry'            => $validated['enquiry'],
            'enquiry_status'     => EnquiryStatus::NEW->value,
            'annual_maintenance' => $validated['annual_maintenance'] ?? false,
            'installations'      => $validated['installations'] ?? false,
            'repairs'            => $validated['repairs'] ?? false,
            'testing'            => $validated['testing'] ?? false,
        ]);

        return $this->successResponse(new EnquiryResource($enquiry->load('customer')), 'Enquiry created successfully.', 201);
    }

    /**
     * Update the status of the enquiry adhering to the state machine.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $enquiry = Enquiry::find($id);

        if (!$enquiry) {
            return $this->errorResponse('Enquiry not found.', 404);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        $currentEnum = EnquiryStatus::tryFrom($enquiry->enquiry_status);
        $newEnum = EnquiryStatus::tryFrom($request->input('status'));

        if (!$newEnum) {
            return $this->errorResponse('Invalid enquiry status.', 422);
        }

        if ($currentEnum && !$currentEnum->canTransitionTo($newEnum)) {
            return $this->errorResponse(
                "Illegal status transition from '{$currentEnum->value}' to '{$newEnum->value}'.",
                422
            );
        }

        $enquiry->enquiry_status = $newEnum->value;
        $enquiry->save();

        return $this->successResponse(new EnquiryResource($enquiry), 'Enquiry status updated successfully.');
    }

    /**
     * Convert an enquiry to a lead via the transactional pipeline action.
     */
    public function convertToLead(int $id, ConvertEnquiryToLead $action): JsonResponse
    {
        $enquiry = Enquiry::find($id);

        if (!$enquiry) {
            return $this->errorResponse('Enquiry not found.', 404);
        }

        try {
            $lead = $action->execute($enquiry);
            return $this->successResponse(new LeadResource($lead), 'Enquiry successfully converted to lead.', 201);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 409);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
