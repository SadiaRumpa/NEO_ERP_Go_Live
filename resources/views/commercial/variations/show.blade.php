@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Commercial Variations /</span> {{ $variation->variation_number }}
            </h4>
            <p class="text-muted mb-0">{{ $variation->title }} &bull; Job #{{ $variation->job_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('commercial.variations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Register
            </a>
            <button type="button" class="btn btn-outline-primary" onclick="window.print();">
                <i class="bx bx-printer me-1"></i> Print
            </button>
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

    <div class="row">
        <!-- Variation Scope & Financial Details -->
        <div class="col-lg-7">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-git-branch text-primary"></i> Scope & Commercial Breakdown
                    </h5>
                    @if($variation->status === 'approved')
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bx bx-check-shield me-1"></i> APPROVED &bull; CONTRACT SUM UPDATED
                        </span>
                    @elseif($variation->status === 'rejected')
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bx bx-x-circle me-1"></i> REJECTED
                        </span>
                    @else
                        <span class="badge bg-label-warning fs-6 px-3 py-2">
                            <span class="status-indicator-dot dot-warning me-1"></span> DRAFT / PENDING CLIENT SIGN-OFF
                        </span>
                    @endif
                </div>
                <div class="card-body pt-4">
                    <div class="mb-3">
                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Variation Title</small>
                        <span class="fw-bold fs-4 text-dark">{{ $variation->title }}</span>
                    </div>

                    <div class="mb-4">
                        <small class="text-muted d-block text-uppercase fw-semibold mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Scope Description & Technical Specification</small>
                        <div class="p-3 border rounded bg-slate-50 text-secondary" style="white-space: pre-line; background-color: #f8fafc; font-size: 0.925rem;">
                            {{ $variation->description ?? 'No scope notes recorded.' }}
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="row g-2 text-center">
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Internal Cost</small>
                                    <span class="fs-5 fw-bold text-secondary tabular-nums">£{{ number_format($variation->cost_amount, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Markup Margin</small>
                                    <span class="fs-5 fw-bold text-info tabular-nums">{{ number_format($variation->markup_percentage, 1) }}%</span>
                                    <div class="small text-muted">+£{{ number_format($variation->margin_amount, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-label-primary border-primary">
                                    <small class="text-primary d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Quoted Value</small>
                                    <span class="fs-4 fw-bold text-primary tabular-nums">£{{ number_format($variation->quoted_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($variation->rejection_reason)
                    <div class="alert alert-danger mt-3 mb-0 d-flex align-items-center">
                        <i class="bx bx-error-circle fs-4 me-2"></i>
                        <div>
                            <strong>Rejection Reason:</strong> {{ $variation->rejection_reason }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Digital Approval & Sign-Off -->
        <div class="col-lg-5">
            @if($variation->status === 'draft')
            <div class="card mb-4 border-primary shadow-sm">
                <div class="card-header bg-label-primary border-bottom py-3">
                    <h5 class="card-title text-primary mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-pen"></i> Client Digital Sign-Off
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <p class="small text-muted mb-3">
                        Approve this variation with the client's representative digital sign-off to update the project revised contract value.
                    </p>
                    <form method="POST" action="{{ route('commercial.variations.approve', $variation) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Client Signer Full Name</label>
                            <input type="text" name="client_signer_name" class="form-control" placeholder="e.g. Marcus Sterling" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client Email</label>
                            <input type="email" name="client_signer_email" class="form-control" placeholder="e.g. marcus@client.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Digital E-Signature Confirmation</label>
                            <div class="p-3 border rounded bg-light text-center">
                                <i class="bx bx-check-double text-success fs-1"></i>
                                <div class="small text-muted mt-1">Legally binding change order authorization under JCT / NEC contract terms.</div>
                                <input type="hidden" name="signature_data" value="ESIGN_DIGITAL_AUTH_{{ uniqid() }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-2 shadow-sm">
                            <i class="bx bx-check-circle me-1"></i> Confirm & Authorize Variation
                        </button>
                    </form>

                    <hr>
                    <!-- Reject Option -->
                    <form method="POST" action="{{ route('commercial.variations.reject', $variation) }}">
                        @csrf
                        <div class="mb-2">
                            <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Reason for decline..." required>
                        </div>
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="bx bx-x-circle me-1"></i> Decline Variation
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="card mb-4 border-success shadow-sm">
                <div class="card-header bg-label-success border-bottom py-3">
                    <h5 class="card-title text-success mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-shield-check fs-5"></i> Electronic Authorization Certificate
                    </h5>
                </div>
                <div class="card-body pt-3">
                    <div class="p-3 border rounded mb-3 bg-light">
                        <div class="mb-2">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Authorized Signer</small>
                            <span class="fw-bold fs-6 text-dark">{{ $variation->client_signer_name ?? 'Client Authorizer' }}</span>
                            @if($variation->client_signer_email)
                                <div class="small text-muted"><i class="bx bx-envelope me-1"></i>{{ $variation->client_signer_email }}</div>
                            @endif
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Execution Timestamp</small>
                            <span class="fw-semibold font-monospace text-dark">{{ $variation->signed_at ? $variation->signed_at->format('d M Y H:i:s') : 'N/A' }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Internal Approval By</small>
                            <span class="text-dark">{{ $variation->approver->name ?? 'Commercial Manager' }}</span>
                        </div>
                    </div>
                    <div class="alert alert-success py-2 mb-0 small d-flex align-items-center">
                        <i class="bx bx-check-shield fs-4 me-2 text-success"></i>
                        <div>
                            Contract Sum increased by <strong class="fs-6 tabular-nums">£{{ number_format($variation->quoted_amount, 2) }}</strong>.
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
