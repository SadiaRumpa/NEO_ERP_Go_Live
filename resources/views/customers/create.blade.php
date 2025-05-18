@extends('layouts.master')

@section('title', 'Create Customer')

@section('scripts')
@parent
<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
<!-- Page JS -->
<script>
    $(function() {
        $('.phone-mask').inputmask({
            mask: '+9 (999) 999-99-99'
        });
        $('.select2').select2();
    });
</script>
@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/typography.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />

@endsection

@section('content')
<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="app-ecommerce">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add a new Customer</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <button type="submit" class="btn btn-label-success">Save</button>
                        <button class="btn btn-label-secondary">Discard</button>
                    </div>

                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-12">
                    <div class="card mb-6">
                        <div class="card-header">
                            <!-- <h5 class="card-title mb-0">New Customer</h5> -->
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                            <div class="alert alert-success mt-2">{{ session('success') }}</div>
                            @endif
                            @if(session('error'))
                            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
                            @endif
                            <div class="row g-6">
                                <div class="col-md-12">
                                    <div class="row">
                                        <label class="col-sm-2 col-form-label text-sm-end" for="formtabs-user-company">Company</label>
                                        <div class="col-sm-10">
                                            <input
                                                type="text"
                                                id="formtabs-user-company"
                                                class="form-control"
                                                placeholder="Company Name"
                                                aria-label="jdoe1 ltd"
                                                name="companyName" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row">
                                        <label class="col-sm-2 col-form-label text-sm-end" for="formtabs-contact-personname">Contact Person</label>
                                        <div class="col-sm-5">
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="formtabs-contact-personfirstname"
                                                placeholder="First Name"
                                                name="contactPersonFirstName"
                                                aria-label="First Name" required />
                                        </div>
                                        <div class="col-sm-5">
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="formtabs-contact-personlastname"
                                                placeholder="Last Name"
                                                name="contactPersonLastName"
                                                aria-label="Last Name" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-contact-phone">Contact Mobile</label>
                                        <div class="col-sm-9">
                                            <input
                                                type="text"
                                                id="formtabs-contact-mobile"
                                                name="contactMobile"
                                                class="form-control"
                                                placeholder="14169884411"
                                                maxlength="11"
                                                pattern="\d{1,11}"
                                                inputmode="numeric"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-contact-phone">Contact Phone</label>
                                        <div class="col-sm-9">
                                            <input
                                                type="text"
                                                id="formtabs-contact-phone"
                                                name="contactPhone"
                                                class="form-control"
                                                placeholder="14169884411"
                                                maxlength="11"
                                                pattern="\d{1,11}"
                                                inputmode="numeric"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-contact-email">Contact email</label>
                                        <div class="col-sm-9">
                                            <input
                                                type="text"
                                                id="formtabs-contact-email"
                                                class="form-control"
                                                placeholder="john.doe@example.com"
                                                aria-label="john.doe@example.com"
                                                name="contactEmail" />
                                        </div>
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
<!-- / Content -->

@endsection