<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
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
            'job_id'         => $this->job_id,
            'job'            => new JobResource($this->whenLoaded('job')),
            'start_date'     => $this->start_date,
            'end_date'       => $this->end_date,
            'status'         => $this->status,
            'is_provisional' => (bool) $this->is_provisional,
            'workers'        => $this->whenLoaded('workers', function () {
                return $this->workers->map(function ($w) {
                    return [
                        'user_id' => $w->id,
                        'name'    => $w->name,
                        'email'   => $w->email,
                    ];
                });
            }),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
