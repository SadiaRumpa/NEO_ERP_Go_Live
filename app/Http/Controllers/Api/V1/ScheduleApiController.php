<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Api\V1\ScheduleResource;
use App\Models\JobSchedule;
use App\Models\JobScheduleWorker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleApiController extends ApiController
{
    /**
     * Display a listing of job schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobSchedule::with(['job', 'workers']);

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        if ($request->has('is_provisional')) {
            $query->where('is_provisional', $request->boolean('is_provisional'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $schedules = $query->latest('start_date')->paginate($perPage);

        return $this->paginatedResponse($schedules, ScheduleResource::class);
    }

    /**
     * Display the specified schedule.
     */
    public function show(int $id): JsonResponse
    {
        $schedule = JobSchedule::with(['job', 'workers'])->find($id);

        if (!$schedule) {
            return $this->errorResponse('Schedule not found.', 404);
        }

        return $this->successResponse(new ScheduleResource($schedule));
    }

    /**
     * Store a newly created schedule with double-booking conflict detection.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_id'         => 'required|exists:fjobs,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'status'         => 'nullable|string',
            'is_provisional' => 'nullable|boolean',
            'worker_ids'     => 'nullable|array',
            'worker_ids.*'   => 'exists:users,id',
        ]);

        $workerIds = $validated['worker_ids'] ?? [];
        $conflicts = [];

        if (!empty($workerIds)) {
            $overlappingWorkers = JobScheduleWorker::whereIn('user_id', $workerIds)
                ->whereHas('schedule', function ($q) use ($validated) {
                    $q->where(function ($sub) use ($validated) {
                        $sub->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                            ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                            ->orWhere(function ($span) use ($validated) {
                                $span->where('start_date', '<=', $validated['start_date'])
                                     ->where('end_date', '>=', $validated['end_date']);
                            });
                    });
                })
                ->with('user')
                ->get();

            foreach ($overlappingWorkers as $ow) {
                $conflicts[] = [
                    'worker_id'   => $ow->user_id,
                    'worker_name' => $ow->user?->name,
                    'schedule_id' => $ow->job_schedule_id,
                ];
            }
        }

        $schedule = DB::transaction(function () use ($validated, $workerIds) {
            $sched = JobSchedule::create([
                'job_id'         => $validated['job_id'],
                'start_date'     => $validated['start_date'],
                'end_date'       => $validated['end_date'],
                'status'         => $validated['status'] ?? 'pending',
                'is_provisional' => $validated['is_provisional'] ?? false,
            ]);

            foreach ($workerIds as $workerId) {
                JobScheduleWorker::create([
                    'job_schedule_id' => $sched->id,
                    'user_id'         => $workerId,
                    'assigned_date'   => $validated['start_date'],
                    'status'          => 'assigned',
                ]);
            }

            return $sched;
        });

        $resource = new ScheduleResource($schedule->load(['job', 'workers']));

        $response = [
            'schedule'     => $resource,
            'has_conflict' => !empty($conflicts),
            'conflicts'    => $conflicts,
        ];

        return $this->successResponse(
            $response,
            !empty($conflicts) ? 'Schedule created with worker double-booking warnings.' : 'Schedule created successfully.',
            201
        );
    }
}
