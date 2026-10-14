<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnquiryResource extends JsonResource
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
            'customer_id'    => $this->customer_id,
            'customer'       => new CustomerResource($this->whenLoaded('customer')),
            'enquiry_status' => $this->enquiry_status,
            'description'    => $this->enquiry,
            'roofing_flags'  => [
                'annual_maintenance' => (bool) $this->annual_maintenance,
                'installations'      => (bool) $this->installations,
                'repairs'            => (bool) $this->repairs,
                'testing'            => (bool) $this->testing,
            ],
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
