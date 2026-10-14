@extends('layouts.master')

@section('title', 'Health & Safety Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6 mb-6">
        <!-- Expiring Vehicle Compliance Card -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100 border-start border-warning border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-heading">Compliance Due (30 Days)</span>
                        <h3 class="my-1 text-warning">{{ $expiringVehicles ?? 0 }}</h3>
                        <small class="text-muted">MOT / Tax / Insurance expiring</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="icon-base ti tabler-truck icon-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Tool Checkups -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100 border-start border-danger border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-heading">Tool Checkups Due</span>
                        <h3 class="my-1 text-danger">{{ $toolCheckupsDue ?? 0 }}</h3>
                        <small class="text-muted">Pending equipment inspections</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="icon-base ti tabler-tool icon-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ongoing Trainings -->
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100 border-start border-success border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-heading">Safety Trainings</span>
                        <h3 class="my-1 text-success">{{ $trainingsCount ?? 0 }}</h3>
                        <small class="text-muted">Active course certifications</small>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="icon-base ti tabler-certificate icon-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Job Schedules Table -->
    <div class="row g-6">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">Upcoming Job Schedules for Safety Review</h5>
                    <a href="{{ route('scheduler.index') }}" class="btn btn-sm btn-primary">Open Scheduler</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Job Title</th>
                                <th>Status</th>
                                <th>Provisional?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingSchedules ?? [] as $schedule)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($schedule->start_date)->format('d M Y') }}</td>
                                    <td>{{ $schedule->end_date ? \Carbon\Carbon::parse($schedule->end_date)->format('d M Y') : 'Single Day' }}</td>
                                    <td>
                                        <span class="fw-medium text-heading">{{ $schedule->job_name ?? $schedule->job_title ?? 'Job Schedule' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ strtoupper($schedule->status) }}</span>
                                    </td>
                                    <td>
                                        @if($schedule->is_provisional)
                                            <span class="badge bg-label-secondary">Provisional</span>
                                        @else
                                            <span class="badge bg-label-success">Official</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No upcoming schedules found.</td>
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
