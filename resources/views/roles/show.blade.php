@extends('layouts.master')

@section('title', 'Role Details - ' . $role->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light"><a href="{{ route('roles.index') }}" class="text-muted">Roles</a> /</span> {{ $role->name }}
            </h4>
            <p class="text-muted mb-0">Role configurations and granted system permissions.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary waves-effect">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Roles
            </a>
            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary waves-effect waves-light">
                <i class="icon-base ti tabler-edit me-1"></i> Edit Role
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Role Info Card -->
        <div class="col-xl-4 col-lg-5 col-md-5 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center pb-3 border-bottom">
                        <div class="avatar avatar-xl mb-3" style="width: 76px; height: 76px;">
                            <span class="avatar-initial rounded-circle bg-label-primary fs-2">
                                <i class="icon-base ti tabler-shield-lock"></i>
                            </span>
                        </div>
                        <h5 class="mb-1 text-heading">{{ ucfirst($role->name) }}</h5>
                        <span class="badge bg-label-primary px-3 py-1">System Role</span>
                    </div>

                    <h6 class="text-uppercase text-muted small fw-semibold mt-4 mb-3">Role Information</h6>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-id me-2 text-muted"></i> Role ID:</span>
                            <span class="badge bg-label-dark">#{{ $role->id }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-tag me-2 text-muted"></i> Role Name:</span>
                            <span class="text-body fw-medium">{{ $role->name }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-shield-check me-2 text-muted"></i> Guard:</span>
                            <span class="badge bg-label-secondary">{{ $role->guard_name }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-key me-2 text-muted"></i> Total Permissions:</span>
                            <span class="badge bg-label-info">{{ count($rolePermissions) }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-medium text-heading"><i class="icon-base ti tabler-calendar me-2 text-muted"></i> Created:</span>
                            <span class="text-body small">{{ $role->created_at ? $role->created_at->format('M d, Y') : 'N/A' }}</span>
                        </li>
                    </ul>

                    <div class="d-flex justify-content-center gap-2 pt-2 border-top">
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary w-100">
                            <i class="icon-base ti tabler-edit me-1"></i> Edit Role Permissions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions List -->
        <div class="col-xl-8 col-lg-7 col-md-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="icon-base ti tabler-key me-2 text-primary"></i> Granted Permissions ({{ count($rolePermissions) }})
                    </h5>
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="icon-base ti tabler-edit me-1"></i> Manage Permissions
                    </a>
                </div>
                <div class="card-body">
                    @if(!empty($rolePermissions) && count($rolePermissions) > 0)
                        <div class="d-flex flex-wrap gap-1 p-3 rounded border bg-light-subtle">
                            @foreach($rolePermissions as $v)
                                <span class="badge bg-label-info m-1">
                                    <i class="icon-base ti tabler-check me-1"></i>{{ $v->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0 d-flex align-items-center">
                            <i class="icon-base ti tabler-alert-triangle fs-4 me-2"></i>
                            <div>No permissions currently assigned to this role.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
