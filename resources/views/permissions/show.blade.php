@extends('layouts.master')

@section('title', 'Permission Details - ' . $permission->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light"><a href="{{ route('permissions.index') }}" class="text-muted">Permissions</a> /</span> {{ $permission->name }}
            </h4>
            <p class="text-muted mb-0">System security permission definition and role assignments.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary waves-effect">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Permissions
            </a>
            <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-primary waves-effect waves-light">
                <i class="icon-base ti tabler-edit me-1"></i> Edit Permission
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Permission Info Card -->
        <div class="col-xl-6 col-lg-7 col-md-8 mx-auto mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center pb-3 border-bottom">
                        <div class="avatar avatar-xl mb-3" style="width: 76px; height: 76px;">
                            <span class="avatar-initial rounded-circle bg-label-info fs-2">
                                <i class="icon-base ti tabler-key"></i>
                            </span>
                        </div>
                        <h5 class="mb-1 text-heading">{{ $permission->name }}</h5>
                        <span class="badge bg-label-info px-3 py-1">Guard: {{ $permission->guard_name }}</span>
                    </div>

                    <h6 class="text-uppercase text-muted small fw-semibold mt-4 mb-3">Permission Information</h6>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-id me-2 text-muted"></i> ID:</span>
                            <span class="badge bg-label-dark">#{{ $permission->id }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-tag me-2 text-muted"></i> Permission Key:</span>
                            <code class="text-primary fw-medium">{{ $permission->name }}</code>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-shield-check me-2 text-muted"></i> Guard:</span>
                            <span class="badge bg-label-secondary">{{ $permission->guard_name }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-calendar me-2 text-muted"></i> Created:</span>
                            <span class="text-body small">{{ $permission->created_at ? $permission->created_at->format('M d, Y') : 'N/A' }}</span>
                        </li>
                    </ul>

                    <div class="d-flex justify-content-center gap-2 pt-2 border-top">
                        <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-primary w-100">
                            <i class="icon-base ti tabler-edit me-1"></i> Edit Permission
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
