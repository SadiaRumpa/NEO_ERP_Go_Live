@extends('layouts.master')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light"><a href="{{ route('users.index') }}" class="text-muted">Users</a> /</span> {{ $user->name }}
            </h4>
            <p class="text-muted mb-0">System user account details, role assignments, and permissions.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary waves-effect">
                <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Users
            </a>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary waves-effect waves-light">
                <i class="icon-base ti tabler-edit me-1"></i> Edit User
            </a>
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center mb-4" role="alert">
        <i class="icon-base ti tabler-circle-check fs-4 me-2"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- User Profile Card (Left Column) -->
        <div class="col-xl-4 col-lg-5 col-md-5 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    @php
                        $words = preg_split('/\s+/', trim($user->name));
                        $initials = '';
                        foreach (array_slice($words, 0, 2) as $w) {
                            $initials .= mb_substr($w, 0, 1);
                        }
                        $initials = strtoupper($initials) ?: 'U';
                        $roles = $user->getRoleNames();
                        $permissions = $user->getAllPermissions();
                    @endphp

                    <!-- User Avatar & Identity -->
                    <div class="d-flex flex-column align-items-center text-center pb-3 border-bottom">
                        <div class="avatar avatar-xl mb-3" style="width: 76px; height: 76px;">
                            <span class="avatar-initial rounded-circle bg-label-primary fs-2 fw-semibold">
                                {{ $initials }}
                            </span>
                        </div>
                        <h5 class="mb-1 text-heading">{{ $user->name }}</h5>
                        <p class="text-muted mb-2 small">{{ $user->email }}</p>

                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            @forelse ($roles as $roleName)
                                <span class="badge bg-label-primary px-3 py-1">{{ ucfirst($roleName) }}</span>
                            @empty
                                <span class="badge bg-label-secondary px-3 py-1">No Role Assigned</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- User Information Details -->
                    <h6 class="text-uppercase text-muted small fw-semibold mt-4 mb-3">Account Information</h6>
                    <div class="info-container">
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-heading"><i class="icon-base ti tabler-id me-2 text-muted"></i> User ID:</span>
                                <span class="badge bg-label-dark">#{{ $user->id }}</span>
                            </li>
                            <li class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-heading"><i class="icon-base ti tabler-mail me-2 text-muted"></i> Email:</span>
                                <span class="text-body text-break">{{ $user->email }}</span>
                            </li>
                            <li class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-heading"><i class="icon-base ti tabler-building me-2 text-muted"></i> Business:</span>
                                <span class="text-body fw-medium">{{ $user->business?->business_name ?? 'Default Organization' }}</span>
                            </li>
                            <li class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-heading"><i class="icon-base ti tabler-badge-check me-2 text-muted"></i> Email Verified:</span>
                                @if($user->email_verified_at)
                                    <span class="badge bg-label-success"><i class="icon-base ti tabler-check me-1"></i> Verified</span>
                                @else
                                    <span class="badge bg-label-secondary">Unverified</span>
                                @endif
                            </li>
                            <li class="mb-3 d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-heading"><i class="icon-base ti tabler-calendar me-2 text-muted"></i> Member Since:</span>
                                <span class="text-body small">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</span>
                            </li>
                            <li class="mb-2 d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-heading"><i class="icon-base ti tabler-clock me-2 text-muted"></i> Last Updated:</span>
                                <span class="text-body small">{{ $user->updated_at ? $user->updated_at->diffForHumans() : 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="d-flex justify-content-center gap-2 pt-2 border-top">
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary w-100">
                            <i class="icon-base ti tabler-edit me-1"></i> Edit Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role & Permissions / System Details (Right Column) -->
        <div class="col-xl-8 col-lg-7 col-md-7 mb-4">
            <!-- Summary Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base ti tabler-shield-lock fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Roles Assigned</small>
                                <h5 class="mb-0 fw-bold">{{ $roles->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="icon-base ti tabler-key fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Total Permissions</small>
                                <h5 class="mb-0 fw-bold">{{ $permissions->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base ti tabler-user-check fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Account Status</small>
                                <h5 class="mb-0 fw-bold text-success">Active</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role & Permissions Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="icon-base ti tabler-shield-check me-2 text-primary"></i> Assigned Roles & Access Level
                    </h5>
                    @if ($roles->isEmpty())
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="icon-base ti tabler-plus me-1"></i> Assign Role
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if ($roles->isNotEmpty())
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-semibold text-uppercase">Assigned Roles</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($roles as $r)
                                    <span class="badge bg-primary px-3 py-2 fs-6">
                                        <i class="icon-base ti tabler-shield me-1"></i> {{ ucfirst($r) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="form-label text-muted small fw-semibold text-uppercase">Effective Capabilities & Permissions ({{ $permissions->count() }})</label>
                            @if ($permissions->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1 p-3 rounded border bg-light-subtle" style="max-height: 280px; overflow-y: auto;">
                                    @foreach ($permissions as $perm)
                                        <span class="badge bg-label-info m-1">
                                            <i class="icon-base ti tabler-check me-1"></i>{{ $perm->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-secondary mb-0">
                                    No explicit permissions linked directly to these role(s).
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                            <i class="icon-base ti tabler-alert-triangle fs-4 me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1 fw-bold">No Roles Assigned</h6>
                                <span>This user currently has no specific role assigned. They have basic authenticated access. Click below to assign a role.</span>
                                <div class="mt-2">
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                        <i class="icon-base ti tabler-user-cog me-1"></i> Edit User & Assign Roles
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Job Schedules / Operational Activity Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="icon-base ti tabler-briefcase me-2 text-primary"></i> Field Work & Operational Assignments
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $assignedSchedules = $user->jobSchedules()->with('job')->latest()->take(5)->get();
                    @endphp

                    @if ($assignedSchedules->isNotEmpty())
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Schedule / Job</th>
                                        <th>Assigned Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($assignedSchedules as $sched)
                                        <tr>
                                            <td>
                                                <strong>#{{ $sched->id }}</strong> - {{ $sched->job?->job_title ?? 'Job Assignment' }}
                                            </td>
                                            <td>{{ $sched->pivot->assigned_date ?? $sched->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <span class="badge bg-label-primary">{{ ucfirst($sched->pivot->status ?? 'Assigned') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="icon-base ti tabler-calendar-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <h6 class="mb-1 text-heading">No Job Schedules Assigned</h6>
                            <p class="small text-muted mb-0">This user is not currently scheduled on active job sites or field tasks.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
