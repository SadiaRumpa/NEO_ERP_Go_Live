@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Labor & Operations /</span> Operative Timesheets</h4>
            <p class="text-muted mb-0">Daily site hours, travel time, overtime tracking, and fully burdened labor costing.</p>
        </div>
        <div>
            <a href="{{ route('labor.timesheets.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Submit Timesheet
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <form method="GET" action="{{ route('labor.timesheets.index') }}" class="row g-3">
                <div class="col-md-3">
                    <select name="user_id" class="form-select">
                        <option value="">All Workers</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->id }}" {{ request('user_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="job_id" class="form-select">
                        <option value="">All Jobs</option>
                        @foreach($jobs as $j)
                            <option value="{{ $j->id }}" {{ request('job_id') == $j->id ? 'selected' : '' }}>
                                Job #{{ $j->id }} - {{ Str::limit($j->job_title ?? $j->job_name, 25) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted (Pending)</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved (WIP Allocated)</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    <a href="{{ route('labor.timesheets.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $timesheets])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Timesheet Ref</th>
                        <th>Operative</th>
                        <th>Job</th>
                        <th>Work Date</th>
                        <th>Hours (Std / OT / Trv)</th>
                        <th>Gross Pay</th>
                        <th>Burdened Cost (WIP)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timesheets as $ts)
                    <tr>
                        <td>
                            <a href="{{ route('labor.timesheets.show', $ts) }}" class="fw-bold text-primary font-monospace">
                                {{ $ts->timesheet_number }}
                            </a>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $ts->worker->name ?? '—' }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary">Job #{{ $ts->job->id }} &bull; {{ Str::limit($ts->job->job_title ?? $ts->job->job_name, 20) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bx-calendar text-muted"></i>
                                <span>{{ $ts->work_date?->format('d M Y') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="fw-bold text-dark tabular-nums">{{ number_format($ts->total_hours, 1) }}h</span>
                            <small class="text-muted d-block tabular-nums">
                                Std: {{ number_format($ts->standard_hours, 1) }}h | OT: {{ number_format($ts->overtime_1_5_hours + $ts->overtime_2_0_hours, 1) }}h | Trv: {{ number_format($ts->travel_hours, 1) }}h
                            </small>
                        </td>
                        <td class="tabular-nums">£{{ number_format($ts->direct_pay_amount, 2) }}</td>
                        <td class="fw-bold text-success fs-6 tabular-nums">
                            £{{ number_format($ts->fully_burdened_cost, 2) }}
                        </td>
                        <td>
                            @if($ts->status === 'approved')
                                <span class="badge bg-label-success">
                                    <span class="status-indicator-dot dot-healthy me-1"></span> Approved
                                </span>
                            @elseif($ts->status === 'rejected')
                                <span class="badge bg-label-danger">
                                    <span class="status-indicator-dot dot-critical me-1"></span> Rejected
                                </span>
                            @else
                                <span class="badge bg-label-warning">
                                    <span class="status-indicator-dot dot-warning me-1"></span> Submitted
                                </span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('labor.timesheets.show', $ts) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View Timesheet">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bx bx-time fs-1 d-block mb-2"></i>
                            No timesheets found. Click "Submit Timesheet" to log operative site hours.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $timesheets->firstItem() ?? 0 }} to {{ $timesheets->lastItem() ?? 0 }} of {{ $timesheets->total() }} results
            </div>
            <div>
                {{ $timesheets->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
