@extends('layouts.master')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Staff KPI Summary -->
    <div class="row g-6 mb-6">
        <div class="col-sm-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-heading">Assigned Schedules</span>
                        <h3 class="my-1 text-primary">{{ $assignedCount ?? 0 }}</h3>
                        <small class="text-muted">Jobs pending start</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="icon-base ti tabler-calendar-event icon-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-heading">In Progress</span>
                        <h3 class="my-1 text-warning">{{ $inProgressCount ?? 0 }}</h3>
                        <small class="text-muted">Currently active work</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="icon-base ti tabler-clock-play icon-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-heading">Completed Works</span>
                        <h3 class="my-1 text-success">{{ $completedCount ?? 0 }}</h3>
                        <small class="text-muted">Finished schedule tasks</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="icon-base ti tabler-circle-check icon-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Tasks Table -->
    <div class="row g-6">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">My Scheduled Job Assignments</h5>
                    <a href="{{ route('scheduler.index') }}" class="btn btn-sm btn-primary">Open Calendar</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Schedule Dates</th>
                                <th>Job Title</th>
                                <th>Status</th>
                                <th>Notes / Instructions</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments ?? [] as $assignment)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium text-heading">
                                                {{ $assignment->assigned_date ? \Carbon\Carbon::parse($assignment->assigned_date)->format('d M Y') : 'N/A' }}
                                                @if($assignment->completion_date && $assignment->completion_date != $assignment->assigned_date)
                                                    - {{ \Carbon\Carbon::parse($assignment->completion_date)->format('d M Y') }}
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-heading">
                                            {{ $assignment->jobSchedule->job_name ?? $assignment->jobSchedule->job_title ?? 'Scheduled Job' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-{{ $assignment->status == 'completed' ? 'success' : ($assignment->status == 'inprogress' ? 'warning' : 'primary') }}">
                                            {{ strtoupper($assignment->status ?? 'ASSIGNED') }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ Str::limit($assignment->notes ?? $assignment->jobSchedule->instructions ?? 'No notes provided', 50) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($assignment->jobSchedule && $assignment->jobSchedule->job_id)
                                            <a href="{{ route('job.show', $assignment->jobSchedule->job->slug ?? $assignment->jobSchedule->job_id) }}" class="btn btn-sm btn-label-secondary">
                                                View Job
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">You have no active schedule assignments.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
