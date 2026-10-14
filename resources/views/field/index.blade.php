@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold py-1 mb-0"><i class="bx bx-mobile-alt me-1 text-primary"></i> Field Engineer Hub</h4>
            <span class="text-muted small">Assigned Roofing Jobs & On-Site Mobile Tools</span>
        </div>
        <div>
            <span class="badge bg-label-primary px-3 py-2"><i class="bx bx-calendar me-1"></i> {{ date('l, d M Y') }}</span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($activeVisit)
    <div class="card bg-primary text-white mb-4 shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-white text-primary mb-2 fw-bold">ACTIVE SITE VISIT IN PROGRESS</span>
                    <h5 class="text-white mb-1 fw-bold">{{ $activeVisit->job->job_name ?: ('Job #' . $activeVisit->job_id) }}</h5>
                    <p class="mb-0 text-white-50 small">
                        <i class="bx bx-time me-1"></i> Arrived: {{ $activeVisit->arrived_at?->format('H:i') }} &bull;
                        {{ $activeVisit->job->customer->company_name ?? 'Client' }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('field.jobs.show', $activeVisit->job_id) }}" class="btn btn-warning fw-bold">
                        <i class="bx bx-right-arrow-alt me-1"></i> Open Site Workspace
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-1"></i> My Active & Upcoming Jobs ({{ count($jobs) }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($jobs as $j)
                <a href="{{ route('field.jobs.show', $j->id) }}" class="list-group-item list-group-item-action p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h6 class="mb-0 fw-bold text-primary">{{ $j->job_name ?: ('Job #' . $j->id) }}</h6>
                                <span class="badge bg-label-info">{{ strtoupper($j->job_status) }}</span>
                            </div>
                            <p class="mb-1 text-muted small">
                                <i class="bx bx-building me-1"></i> <strong>{{ $j->customer->company_name ?? 'Commercial Client' }}</strong>
                            </p>
                            @if($j->jobaddress)
                            <p class="mb-0 text-muted small text-truncate" style="max-width: 450px;">
                                <i class="bx bx-map-pin me-1 text-danger"></i> {{ $j->jobaddress->address }}, {{ $j->jobaddress->postcode }}
                            </p>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="btn btn-sm btn-outline-primary">
                                Launch <i class="bx bx-chevron-right ms-1"></i>
                            </span>
                        </div>
                    </div>
                </a>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bx bx-check-circle fs-1 text-success mb-2 d-block"></i>
                    No pending site jobs assigned for today. You are all caught up!
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
