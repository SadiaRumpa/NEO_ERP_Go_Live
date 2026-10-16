@extends('layouts.master')

@section('title', 'Create User')

@section('scripts')
@parent
<script src="{{asset('assets/vendor/libs/cleave-zen/cleave-zen.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(!$errors->any() && !old('email'))
        setTimeout(function() {
            var emailInput = document.getElementById('multicol-email');
            var passInput = document.getElementById('multicol-password');
            var confirmPassInput = document.getElementById('multicol-confirm-password');
            if (emailInput) emailInput.value = '';
            if (passInput) passInput.value = '';
            if (confirmPassInput) confirmPassInput.value = '';
        }, 50);
        @endif
    });
</script>
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
                    <h5 class="card-title mb-0">Create new user</h5>
                    <div class="card-header-elements ms-auto py-0">
                        <a class="btn btn-dark waves-effect waves-light mb-2" href="{{ route('users.index') }}">Back</a>
                    </div>
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
                <form class="card-body" method="POST" action="{{ route('users.store') }}" autocomplete="off">
                    @csrf
                    <div class="row mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-full-name">Full Name</label>
                        <div class="col-sm-9">
                            <input type="text" id="multicol-full-name" name="name" class="form-control" placeholder="e.g. John Doe" value="{{ old('name') }}" autocomplete="off" />
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
                                placeholder="e.g. name@company.com"
                                value="{{ old('email') }}"
                                autocomplete="off" />
                        </div>
                    </div>
                    <div class="row form-password-toggle mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-password">Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="multicol-password"
                                    class="form-control"
                                    name="password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                    value=""
                                    aria-describedby="multicol-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-password2"><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                            <small class="text-muted d-block mt-1">Must be at least 8 characters and include uppercase, lowercase, numbers, and signs/symbols.</small>
                        </div>
                    </div>
                    <div class="row form-password-toggle mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-confirm-password">Confirm Password</label>
                        <div class="col-sm-9">
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="multicol-confirm-password"
                                    class="form-control"
                                    name="confirm-password"
                                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                    value=""
                                    aria-describedby="multicol-confirm-password2" />
                                <span class="input-group-text cursor-pointer" id="multicol-confirm-password2"><i class="icon-base ti tabler-eye-off"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-sm-3 col-form-label" for="multicol-country">Roles</label>
                        <div class="col-sm-9">
                            <select id="multicol-country" class="select2 form-select" data-allow-clear="true" data-placeholder="Select a role" name="roles[]">
                                <option value="" disabled {{ old('roles') ? '' : 'selected' }}>Select a role</option>
                                @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ (is_array(old('roles')) && in_array($value, old('roles'))) || old('roles') == $value ? 'selected' : '' }} >
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="pt-6">
                        <div class="row justify-content-end">
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary me-4">Submit</button>
                                <!-- <button type="reset" class="btn btn-label-secondary">Cancel</button> -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>




</div>
@endsection