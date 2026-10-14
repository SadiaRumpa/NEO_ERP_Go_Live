<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'lead_number'    => $this->lead_number ?? null,
            'customer_id'    => $this->customer_id,
            'enquiry_id'     => $this->enquiry_id,
            'customer'       => new CustomerResource($this->whenLoaded('customer')),
            'lead_status'    => $this->lead_status,
            'roofing_flags'  => [
                'annual_maintenance' => (bool) $this->annual_maintenance,
                'installations'      => (bool) $this->installations,
                'repairs'            => (bool) $this->repairs,
                'testing'            => (bool) $this->testing,
            ],
            'assigned_to'    => $this->assigned_to,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
