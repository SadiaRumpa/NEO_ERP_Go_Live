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
                <i class="ti ti-download me-1"></i> Export Jobs CSV
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
                            <span class="text-heading">Enquiry &rarr; Lead</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $metrics['enquiry_to_lead_avg_days'] }}</h4>
                                <span class="text-muted">days avg</span>
                            </div>
                            <small class="text-muted">{{ $metrics['enquiry_to_lead_count'] }} conversions recorded</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-clock-forward ti-md"></i>
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
                            <span class="text-heading">Lead &rarr; Job</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $metrics['lead_to_job_avg_days'] }}</h4>
                                <span class="text-muted">days avg</span>
                            </div>
                            <small class="text-muted">{{ $metrics['lead_to_job_count'] }} conversions recorded</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-bolt ti-md"></i>
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
                            <span class="text-heading">Total Pipeline Cycle</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $metrics['total_pipeline_avg_days'] }}</h4>
                                <span class="text-muted">days avg</span>
                            </div>
                            <small class="text-muted">End-to-end sales cycle duration</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-calendar-stats ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pipeline Conversion Visual Funnel Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Pipeline Progression Stages</h5>
        </div>
        <div class="card-body">
            <div class="row text-center py-3 g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-label-primary rounded">
                        <h6 class="mb-1 text-primary">1. New Enquiry</h6>
                        <small class="text-muted">Capture requirements & contact details</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-label-info rounded">
                        <h6 class="mb-1 text-info">2. Qualified Lead & Quote</h6>
                        <small class="text-muted">Site survey & customer signed quote</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-label-success rounded">
                        <h6 class="mb-1 text-success">3. Scheduled Job (`Fjob`)</h6>
                        <small class="text-muted">Field execution & material allocation</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
