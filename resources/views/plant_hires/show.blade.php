@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial Plant /</span> {{ $plantHire->hire_reference }}
            </h4>
            <p class="text-muted mb-0">{{ $plantHire->item_name }} &bull; Supplier: {{ $plantHire->supplier->name ?? 'N/A' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('plant-hires.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Register
            </a>
            @if($plantHire->status === 'active')
            <a href="{{ route('plant-hires.edit', $plantHire) }}" class="btn btn-secondary">
                <i class="bx bx-edit me-1"></i> Edit
            </a>
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
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="card-title mb-0">Equipment Hire Contract Details</h5>
                    @if($plantHire->status === 'active')
                        @if($plantHire->isOverdue())
                            <span class="badge bg-danger fs-6">OVERDUE (STANDBY COST EROSION)</span>
                        @elseif($plantHire->isExpiringSoon())
                            <span class="badge bg-warning fs-6">OFF-HIRE DUE WITHIN 48H</span>
                        @else
                            <span class="badge bg-success fs-6">ACTIVE ON-HIRE</span>
                        @endif
                    @elseif($plantHire->status === 'off_hired')
                        <span class="badge bg-secondary fs-6">OFF-HIRED</span>
                    @else
                        <span class="badge bg-info fs-6">{{ ucfirst($plantHire->status) }}</span>
                    @endif
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Equipment Item</small>
                            <span class="fw-bold fs-6">{{ $plantHire->item_name }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Category</small>
                            <span class="badge bg-label-primary text-capitalize">{{ str_replace('_', ' ', $plantHire->category) }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Serial / Asset Tag</small>
                            <span class="font-monospace fw-semibold">{{ $plantHire->serial_asset_tag ?? 'None' }}</span>
                        </div>

                        <div class="col-md-6">
                            <small class="text-muted d-block">Plant Supplier</small>
                            <span class="fw-bold text-primary">{{ $plantHire->supplier->name ?? 'N/A' }}</span>
                            <div class="small text-muted">{{ $plantHire->supplier->email ?? '' }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Allocated Job (Cost Allocation)</small>
                            <span class="fw-bold">Job #{{ $plantHire->job_id }} - {{ $plantHire->job->job_title ?? $plantHire->job->job_name }}</span>
                        </div>

                        <div class="col-md-4">
                            <small class="text-muted d-block">Hire Start Date</small>
                            <span class="fw-bold"><i class="bx bx-calendar me-1"></i>{{ $plantHire->start_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Scheduled Off-Hire Date</small>
                            <span class="fw-bold {{ $plantHire->isOverdue() ? 'text-danger' : '' }}">
                                <i class="bx bx-calendar-event me-1"></i>{{ $plantHire->expected_end_date->format('d M Y') }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Actual Off-Hire Date</small>
                            <span class="fw-bold">
                                {{ $plantHire->actual_end_date ? $plantHire->actual_end_date->format('d M Y') : 'Active / Pending Return' }}
                            </span>
                        </div>

                        <div class="col-12 border-top pt-3">
                            <h6 class="fw-bold mb-2">Contract Rates & Transportation</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Daily Rate</small>
                                    <span class="fw-bold">£{{ number_format($plantHire->daily_rate, 2) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Weekly Rate</small>
                                    <span class="fw-bold">£{{ number_format($plantHire->weekly_rate, 2) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Delivery Fee</small>
                                    <span class="fw-bold">£{{ number_format($plantHire->delivery_charge, 2) }}</span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Collection Fee</small>
                                    <span class="fw-bold">£{{ number_format($plantHire->collection_charge, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        @if($plantHire->notes)
                        <div class="col-12 border-top pt-3">
                            <small class="text-muted d-block">Notes & Logs</small>
                            <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $plantHire->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions & Cost Accrual -->
        <div class="col-lg-4">
            <!-- WIP Live Accrual Card -->
            <div class="card mb-4 bg-label-primary border-primary">
                <div class="card-body">
                    <h6 class="card-subtitle text-primary mb-1 fw-bold">Live Job WIP Accrued Cost</h6>
                    <h2 class="card-title fw-bold mb-0 text-primary">£{{ number_format($currentAccrued, 2) }}</h2>
                    <small class="text-muted d-block mt-2">
                        Includes hire days elapsed &bull; delivery fee &bull; collection charge.
                    </small>
                </div>
            </div>

            <!-- Off-Hire Action Card -->
            @if($plantHire->status === 'active')
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0 text-danger"><i class="bx bx-check-shield me-1"></i> Off-Hire Equipment</h5>
                </div>
                <div class="card-body pt-3">
                    <p class="small text-muted mb-3">
                        Call supplier to request off-hire reference. Logging off-hire reference caps further hire cost accrual.
                    </p>
                    <form method="POST" action="{{ route('plant-hires.off-hire', $plantHire) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Supplier Off-Hire Reference Number</label>
                            <input type="text" name="off_hire_reference" class="form-control" placeholder="e.g. OH-99482-REF" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Actual Return Date</label>
                            <input type="date" name="actual_end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Return Notes / Condition</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Plant inspected clean, keys handed over..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bx bx-power-off me-1"></i> Confirm Off-Hire
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0 text-success"><i class="bx bx-check-circle me-1"></i> Off-Hire Confirmation</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="mb-2">
                        <small class="text-muted d-block">Off-Hire Reference</small>
                        <span class="fw-bold font-monospace text-primary fs-6">{{ $plantHire->off_hire_reference ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Off-Hired At</small>
                        <span>{{ $plantHire->off_hired_at ? $plantHire->off_hired_at->format('d M Y H:i') : 'N/A' }}</span>
                    </div>
                    <div>
                        <small class="text-muted d-block">Processed By</small>
                        <span>{{ $plantHire->offHiredByUser->name ?? 'System' }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
