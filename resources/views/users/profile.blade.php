@extends('layouts.master')

@section('title', 'My Profile')

@section('scripts')
@parent
<script src="{{asset('assets/vendor/libs/cleave-zen/cleave-zen.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Multi Column with Form Separator -->
    <div class="row mb-6 gy-6">
        <!-- Form Separator -->
        <div class="col-xxl">
            <div class="card">
                <div class="card-header header-elements">
                    <h5 class="card-title mb-0">My Profile</h5>
                </div>
                @if (session('success'))
                <div class="alert alert-success alert-dismissible d-flex align-items-center mb-4" role="alert">
                    <i class="icon-base ti tabler-circle-check fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger alert-dismissible d-flex align-items-center mb-4" role="alert">
                    <i class="icon-base ti tabler-alert-triangle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form class="card-body" method="POST" action="{{ route('users.profile.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-full-name">Full Name</label>
                        <div class="col-sm-9">
                            <input type="text" id="multicol-full-name" name="name" class="form-control" placeholder="John Doe" value="{{ $user->name }}" />
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-email">Email</label>
                        <div class="col-sm-9">
                            <input
                                type="email"
                                id="multicol-email"
                                class="form-control"
                                name="email"
                                placeholder="name@company.com"
                                value="{{ $user->email }}" required />
                        </div>
                    </div>
                    <div class="row form-password-toggle mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-current-password">Current Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="multicol-current-password"
                                    class="form-control"
                                    name="current_password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="multicol-current-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-current-password2"><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="row form-password-toggle mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-password">New Password (leave blank to keep)</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="multicol-password"
                                    class="form-control"
                                    name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="multicol-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="row form-password-toggle mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-confirm-password">Confirm New Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="multicol-confirm-password"
                                    class="form-control"
                                    name="confirm-password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    aria-describedby="multicol-confirm-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <div class="row justify-content-end">
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary me-4">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>




</div>
@endsection