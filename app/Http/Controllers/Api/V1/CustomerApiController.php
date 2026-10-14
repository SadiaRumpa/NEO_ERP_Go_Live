<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerApiController extends ApiController
{
    /**
     * Display a listing of customers with optional search and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_firstname', 'like', "%{$search}%")
                  ->orWhere('contact_lastname', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $customers = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($customers, CustomerResource::class);
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->errorResponse('Customer not found.', 404);
        }

        return $this->successResponse(new CustomerResource($customer));
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name'      => 'required|string|max:255',
            'contact_firstname' => 'nullable|string|max:255',
            'contact_lastname'  => 'nullable|string|max:255',
            'contact_name'      => 'nullable|string|max:255',
            'contact_email'     => 'required|email|max:255',
            'contact_phone'     => 'nullable|string|max:50',
            'status'            => 'nullable|string',
        ]);

        $firstName = $validated['contact_firstname'] ?? '';
        $lastName = $validated['contact_lastname'] ?? '';
        if (empty($firstName) && !empty($validated['contact_name'])) {
            $parts = explode(' ', trim($validated['contact_name']), 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';
        }
        if (empty($firstName)) {
            $firstName = 'Primary';
            $lastName = 'Contact';
        }

        // Duplicate check
        $existing = Customer::where('company_name', $validated['company_name'])
            ->where('contact_email', $validated['contact_email'])
            ->first();

        if ($existing) {
            return $this->errorResponse('A customer with this company name and contact email already exists.', 409);
        }

        $customer = Customer::create([
            'company_name'      => $validated['company_name'],
            'contact_firstname' => $firstName,
            'contact_lastname'  => $lastName,
            'contact_email'     => $validated['contact_email'],
            'contact_phone'     => $validated['contact_phone'] ?? null,
            'status'            => $validated['status'] ?? 'active',
        ]);

        return $this->successResponse(new CustomerResource($customer), 'Customer created successfully.', 201);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->errorResponse('Customer not found.', 404);
        }

        $validated = $request->validate([
            'company_name'      => 'sometimes|required|string|max:255',
            'contact_firstname' => 'nullable|string|max:255',
            'contact_lastname'  => 'nullable|string|max:255',
            'contact_email'     => 'sometimes|required|email|max:255',
            'contact_phone'     => 'nullable|string|max:50',
            'status'            => 'nullable|string',
        ]);

        $customer->update($validated);

        return $this->successResponse(new CustomerResource($customer), 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->errorResponse('Customer not found.', 404);
        }

        $customer->delete();

        return $this->successResponse(null, 'Customer deleted successfully.');
    }
}
