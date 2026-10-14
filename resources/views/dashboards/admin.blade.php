@extends('layouts.master')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- KPI Row -->
    <div class="row g-6 mb-6">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Total Enquiries</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalEnquiries ?? 0 }}</h4>
                                <span class="badge bg-label-info">{{ $newEnquiries ?? 0 }} New</span>
                            </div>
                            <small class="text-muted">Weekly trend: {{ $enquiryTrend['trend'] ?? 'stable' }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="icon-base ti tabler-messages icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Pipeline Leads</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalLeads ?? 0 }}</h4>
                                <span class="badge bg-label-warning">{{ $ongoingLeads ?? 0 }} In Progress</span>
                            </div>
                            <small class="text-muted">Weekly trend: {{ $leadTrend['trend'] ?? 'stable' }}</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base ti tabler-user-check icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Active Jobs</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $activeJobs ?? 0 }}</h4>
                                <span class="badge bg-label-success">{{ $completedJobs ?? 0 }} Done</span>
                            </div>
                            <small class="text-muted">Total: {{ $totalJobs ?? 0 }} jobs</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="icon-base ti tabler-briefcase icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-heading">Customers</span>
                            <div class="d-flex align-items-center my-1">
                                <h4 class="mb-0 me-2">{{ $totalCustomers ?? 0 }}</h4>
                            </div>
                            <small class="text-muted">Active client accounts</small>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="icon-base ti tabler-users icon-26px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory & Procurement Alerts -->
    <div class="row g-6 mb-6">
        <div class="col-md-6">
            <div class="card h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-primary">Pending Stock Requisitions</h6>
                        <h3 class="mb-0">{{ $pendingRequisitions ?? 0 }}</h3>
                        <small class="text-muted">Requisitions awaiting approval or issuing</small>
                    </div>
                    <a href="{{ route('stockitemrequisitions.index') }}" class="btn btn-primary btn-sm">View Requisitions</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 border-start border-danger border-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-1 text-danger">Low Stock Item Variants</h6>
                        <h3 class="mb-0">{{ $lowStockItems ?? 0 }}</h3>
                        <small class="text-muted">Variants with quantity less than 10 units</small>
                    </div>
                    <a href="{{ route('stockitemsinventory.index') }}" class="btn btn-outline-danger btn-sm">Manage Inventory</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tables -->
    <div class="row g-6">
        <!-- Recent Enquiries -->
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Enquiries</h5>
                    <a href="{{ route('enquiries.index') }}" class="btn btn-sm btn-label-primary">View All</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEnquiries ?? [] as $enquiry)
                                <tr>
                                    <td>
                                        <a href="{{ route('enquiries.show', $enquiry->slug ?? $enquiry->id) }}" class="fw-medium text-body">
                                            {{ Str::limit($enquiry->enquiry_name, 25) }}
                                        </a>
                                    </td>
                                    <td>{{ $enquiry->customer->company_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $enquiry->enquiry_status == 'new' ? 'info' : ($enquiry->enquiry_status == 'converted_to_lead' ? 'success' : 'secondary') }}">
                                            {{ beautify_status($enquiry->enquiry_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $enquiry->created_at ? $enquiry->created_at->format('d/m/Y') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent enquiries found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Active & Recent Jobs</h5>
                    <a href="{{ route('job.index') }}" class="btn btn-sm btn-label-primary">View All</a>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentJobs ?? [] as $job)
                                <tr>
                                    <td>
                                        <a href="{{ route('job.show', $job->slug ?? $job->id) }}" class="fw-medium text-body">
                                            {{ Str::limit($job->job_title ?? $job->job_name, 25) }}
                                        </a>
                                    </td>
                                    <td>{{ $job->customer->company_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $job->job_status == 'inprogress' ? 'warning' : ($job->job_status == 'completed' ? 'success' : 'primary') }}">
                                            {{ beautify_status($job->job_status) }}
                                        </span>
                                    </td>
                                    <td>{{ $job->created_at ? $job->created_at->format('d/m/Y') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent jobs found.</td>
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