@extends('layouts.master')

@section('title', 'Edit Driver Profile')

@section('scripts')
    @parent
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        $(function() {
            $('.select2').select2();
        });
    </script>
@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="app-ecommerce">
            <form action="{{ route('drivers.update', $driver->id) }}" method="POST">
                @csrf
                @method('PUT')
                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
                    <div class="d-flex flex-column justify-content-center">
                        <h4 class="mb-1">Edit Driver Profile</h4>
                    </div>
                    <div class="d-flex align-content-center flex-wrap gap-4">
                        <div class="d-flex gap-4">
                            <button type="submit" class="btn btn-label-success">Update</button>
                            <a href="{{ route('drivers.index') }}" class="btn btn-label-secondary">Back</a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-lg-12">
                        <div class="card mb-6">
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success mt-2">{{ session('success') }}</div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger mt-2">{{ session('error') }}</div>
                                @endif
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="form-users">Select Staff</label>
                                        <select class="form-select select2" id="form-users" name="user_id" required>
                                            <option value="">Select Staff</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ $driver->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6"></div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="form-license-category">Licence Category</label>
                                        <select class="form-select select2" id="form-license-category" name="license_category" required>
                                            <option value="">Select License Category</option>
                                            <option value="B" {{ $driver->license_category == 'B' ? 'selected' : '' }}>B</option>
                                            <option value="C1" {{ $driver->license_category == 'C1' ? 'selected' : '' }}>C1</option>
                                            <option value="HGV" {{ $driver->license_category == 'HGV' ? 'selected' : '' }}>HGV</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="form-driving-licence">Driving Licence</label>
                                        <input type="text" class="form-control" id="form-driving-licence"
                                            placeholder="Driving Licence No" name="license_no" value="{{ $driver->license_no }}" required />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="form-license-issue">Licence Issue Date</label>
                                        <input type="date" class="form-control flatpickr" id="form-license-issue"
                                            name="license_issue_date" value="{{ $driver->license_issue_date }}" />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="form-license-expiry">Licence Expiry Date</label>
                                        <input type="date" class="form-control flatpickr" id="form-license-expiry"
                                            name="license_expiry" value="{{ $driver->license_expiry }}" />
                                    </div>

                                    <div class="col-md-12 d-flex align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="has-digital-tachograph-card"
                                                name="digital_tacho_card" value="1" {{ $driver->digital_tacho_card ? 'checked' : '' }}>
                                            <label class="form-check-label" for="has-digital-tachograph-card">Has Digital Tachograph Card?</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 d-flex align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="has-dbs-check-passed"
                                                name="dbs_check_passed" value="1" {{ $driver->dbs_check_passed ? 'checked' : '' }}>
                                            <label class="form-check-label" for="has-dbs-check-passed">Has DBS check passed?</label>
                                        </div>
                                    </div>

                                    <div class="col-md-12 d-flex align-items-center">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="has-medical-check-passed"
                                                name="medical_check_passed" value="1" {{ $driver->medical_check_passed ? 'checked' : '' }}>
                                            <label class="form-check-label" for="has-medical-check-passed">Has Medical check passed?</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
