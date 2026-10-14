<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'company_name'      => $this->company_name,
            'contact_firstname' => $this->contact_firstname,
            'contact_lastname'  => $this->contact_lastname,
            'contact_name'      => $this->contact_name,
            'contact_email'     => $this->contact_email,
            'contact_phone'     => $this->contact_phone,
            'status'            => $this->status ?? 'active',
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),
        ];
    }
}
