@extends('layouts.master')

@section('title', 'Pipeline Conversion Velocity')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Pipeline Conversion Velocity</h4>
            <p class="text-muted mb-0">Track cycle times from Enquiry intake to Lead qualification and Job handover</p>
        </div>
        <div>
            <a href="{{ route('reports.export-jobs') }}" class="btn btn-outline-primary waves-effect">
                <i class="icon-base ti tabler-download me-1"></i> Export Jobs CSV
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">Enquiry &rarr; Lead</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 text-primary">{{ $metrics['enquiry_to_lead_avg_days'] }}</h3>
                                <span class="text-muted">days avg</span>
                            </div>
                            <small class="text-muted">{{ $metrics['enquiry_to_lead_count'] }} conversions recorded ({{ $metrics['enquiry_to_lead_rate'] ?? 100 }}% rate)</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ti tabler-clock-forward fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">Lead &rarr; Job</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 text-success">{{ $metrics['lead_to_job_avg_days'] }}</h3>
                                <span class="text-muted">days avg</span>
                            </div>
                            <small class="text-muted">{{ $metrics['lead_to_job_count'] }} conversions recorded ({{ $metrics['lead_to_job_rate'] ?? 100 }}% rate)</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base ti tabler-bolt fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading fw-semibold">Total Pipeline Cycle</span>
                            <div class="d-flex align-items-center my-1">
                                <h3 class="mb-0 me-2 text-info">{{ $metrics['total_pipeline_avg_days'] }}</h3>
                                <span class="text-muted">days avg</span>
                            </div>
                            <small class="text-muted">End-to-end sales cycle duration</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base ti tabler-calendar-stats fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pipeline Conversion Visual Funnel Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Pipeline Progression Stages</h5>
            <span class="badge bg-label-primary">Total Pipeline: {{ $metrics['total_pipeline_avg_days'] }} Days</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-label-primary rounded h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">Stage 1</span>
                                <small class="fw-semibold text-primary">{{ $metrics['total_enquiries'] ?? 100 }} Total</small>
                            </div>
                            <h5 class="mb-1 text-primary">New Enquiry Intake</h5>
                            <p class="text-muted small mb-0">Client requirements, contact details & preliminary scope capture.</p>
                        </div>
                        <div class="pt-3 border-top border-primary-subtle mt-3">
                            <span class="small text-muted">Intake &rarr; Lead turnaround:</span>
                            <div class="fw-bold text-primary">{{ $metrics['enquiry_to_lead_avg_days'] }} days avg</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-label-info rounded h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-info">Stage 2</span>
                                <small class="fw-semibold text-info">{{ $metrics['total_leads'] ?? 100 }} Total</small>
                            </div>
                            <h5 class="mb-1 text-info">Qualified Lead & Quote</h5>
                            <p class="text-muted small mb-0">Site survey, bill of quantities preparation & signed customer quotation.</p>
                        </div>
                        <div class="pt-3 border-info-subtle mt-3">
                            <span class="small text-muted">Quote &rarr; Award turnaround:</span>
                            <div class="fw-bold text-info">{{ $metrics['lead_to_job_avg_days'] }} days avg</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-label-success rounded h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-success">Stage 3</span>
                                <small class="fw-semibold text-success">{{ $metrics['total_jobs'] ?? 102 }} Total</small>
                            </div>
                            <h5 class="mb-1 text-success">Scheduled Job (FJOB)</h5>
                            <p class="text-muted small mb-0">Field execution, procurement & subcontractor allocation.</p>
                        </div>
                        <div class="pt-3 border-success-subtle mt-3">
                            <span class="small text-muted">Total end-to-end velocity:</span>
                            <div class="fw-bold text-success">{{ $metrics['total_pipeline_avg_days'] }} days avg</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Conversions Table Card -->
    @if(isset($recentConversions) && $recentConversions->count() > 0)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Pipeline Conversions</h5>
            <small class="text-muted">Showing latest {{ $recentConversions->count() }} pipeline transitions</small>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Job Ref & Title</th>
                        <th>Client</th>
                        <th>Enquiry Intake</th>
                        <th>Lead Quoted</th>
                        <th>Job Handover</th>
                        <th>Total Velocity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($recentConversions as $item)
                    <tr>
                        <td>
                            <div class="d-flex flex-column">
                                <a href="{{ route('job.show', $item->job_id) }}" class="fw-semibold text-heading">
                                    {{ $item->job_name }}
                                </a>
                                <small class="text-muted text-truncate" style="max-width: 280px;" title="{{ $item->job_title }}">{{ $item->job_title }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $item->company_name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary mb-1">{{ $item->enquiry_name }}</span>
                            <div class="small text-muted">{{ $item->enquiry_date }}</div>
                        </td>
                        <td>
                            <span class="badge bg-label-info mb-1">{{ $item->lead_name }}</span>
                            <div class="small text-muted">{{ $item->lead_date }}</div>
                            <small class="text-info fw-semibold">+{{ $item->enq_to_lead_days }}d</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-heading">{{ $item->job_date }}</div>
                            <small class="text-success fw-semibold">+{{ $item->lead_to_job_days }}d</small>
                        </td>
                        <td>
                            <span class="badge bg-label-primary fs-7 px-2 py-1">
                                <i class="icon-base ti tabler-clock-hour-4 me-1"></i> {{ $item->total_days }} days
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeClass = match(strtolower($item->job_status)) {
                                    'completed' => 'bg-label-success',
                                    'inprogress' => 'bg-label-primary',
                                    'accepted' => 'bg-label-info',
                                    'new' => 'bg-label-warning',
                                    default => 'bg-label-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($item->job_status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('job.show', $item->job_id) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" title="View Job Details">
                                <i class="icon-base ti tabler-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
