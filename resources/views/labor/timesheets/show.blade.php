@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Labor / Timesheets /</span> {{ $timesheet->timesheet_number }}
            </h4>
            <p class="text-muted mb-0">Submitted by <strong>{{ $timesheet->worker->name }}</strong> for <strong>{{ $timesheet->work_date?->format('d M Y') }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('labor.timesheets.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-left-arrow-alt me-1"></i> Back to Timesheets
            </a>
            @if($timesheet->status === 'submitted')
                <form method="POST" action="{{ route('labor.timesheets.approve', $timesheet) }}">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Approve & Absorb to WIP</button>
                </form>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bx bx-x me-1"></i> Reject
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Operative & Job Allocation</h6>
                    <h5 class="card-title fw-bold text-primary mb-1">{{ $timesheet->worker->name }}</h5>
                    <p class="text-muted mb-2">{{ $timesheet->worker->email }}</p>
                    <div class="alert alert-primary py-2 mb-2">
                        <i class="bx bx-briefcase me-1"></i>
                        <strong>Allocated to Job #{{ $timesheet->job->id }}</strong>
                        <div class="small">{{ $timesheet->job->job_title ?? $timesheet->job->job_name }}</div>
                    </div>
                    <div class="small">
                        <div><strong>Shift Date:</strong> {{ $timesheet->work_date?->format('d M Y') }}</div>
                        <div><strong>Working Hours:</strong> {{ $timesheet->start_time ?? '08:00' }} – {{ $timesheet->end_time ?? '16:30' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Hours Classification</h6>
                    <div class="fs-3 fw-bold mb-3">{{ number_format($timesheet->total_hours, 1) }} Total Hours</div>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Standard On-Site:</span>
                            <span class="fw-bold">{{ number_format($timesheet->standard_hours, 1) }}h</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Travel Time:</span>
                            <span>{{ number_format($timesheet->travel_hours, 1) }}h</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Overtime 1.5x:</span>
                            <span>{{ number_format($timesheet->overtime_1_5_hours, 1) }}h</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Double Time 2.0x:</span>
                            <span>{{ number_format($timesheet->overtime_2_0_hours, 1) }}h</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Labor Costing & WIP Absorption</h6>
                    <div class="mb-2">
                        @if($timesheet->status === 'approved')
                            <span class="badge bg-success fs-6">Approved (In Job WIP)</span>
                        @elseif($timesheet->status === 'rejected')
                            <span class="badge bg-danger fs-6">Rejected</span>
                        @else
                            <span class="badge bg-warning fs-6">Submitted (Pending Review)</span>
                        @endif
                    </div>
                    <div class="small mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Base Pay Rate:</span>
                            <span class="fw-bold">£{{ number_format($timesheet->cost_rate_per_hour, 2) }}/hr</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Direct Pay Amount:</span>
                            <span class="fw-bold">£{{ number_format($timesheet->direct_pay_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Loaded Burden Factor:</span>
                            <span class="badge bg-label-info">{{ $timesheet->burden_multiplier }}x</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="fw-bold fs-6">Fully Burdened Cost:</span>
                            <span class="fw-bold fs-5 text-success">£{{ number_format($timesheet->fully_burdened_cost, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($timesheet->notes)
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h6 class="card-title mb-0">Daily Shift Summary & Work Log</h6>
        </div>
        <div class="card-body pt-3">
            <pre class="mb-0 bg-light p-3 rounded text-dark" style="white-space: pre-wrap; font-family: inherit;">{{ $timesheet->notes }}</pre>
        </div>
    </div>
    @endif
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('labor.timesheets.reject', $timesheet) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Reject Timesheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Reason for Rejection</label>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Discrepancy with site sign-in sheet on travel hours" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="bx bx-x me-1"></i> Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>
@endsection
