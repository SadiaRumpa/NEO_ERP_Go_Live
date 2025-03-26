@extends('layouts.master')

@section('title', 'Create Enquiry')

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
<!-- <script src="{{asset('assets/js/forms-file-upload.js')}}"></script> -->
<script src="{{asset('assets/js/app-ecommerce-product-add.js')}}"></script>
<script>
    function addNewCustomer(event) {
        if (event) event.preventDefault(); // Prevent form submission
        $('.new-customer').show();
    };
</script>
@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/typography.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/katex.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/quill/editor.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/tagify/tagify.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/dropzone/dropzone.css')}}" />
@endsection

@section('content')
<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="app-ecommerce">
        <form action="{{ route('enquiries.store') }}" method="POST">
            @csrf
            <div
                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
                <div class="d-flex flex-column justify-content-center">
                    <h4 class="mb-1">Add a new Enquiry</h4>
                </div>
                <div class="d-flex align-content-center flex-wrap gap-4">
                    <div class="d-flex gap-4">
                        <button class="btn btn-label-secondary">Discard</button>
                        <button type="submit" class="btn btn-label-success">Save draft</button>
                    </div>

                </div>
            </div>

            <div class="row">

                <!-- First column -->
                <div class="col-12 col-lg-12">
                    <!-- Customer Card -->
                    <div class="card mb-6">
                        <div class="card-body">
                            <!-- Customers -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="mb-6 col ecommerce-select2-dropdown">
                                    <label class="form-label mb-1" for="customer">
                                        <span>Select Customer or Create New</span>
                                    </label>
                                    <select id="customer" class="select2 form-select" data-placeholder="Select customer" name="customer_id">
                                        <option value="">Select customer</option>
                                        @foreach ($customers as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="fw-medium btn btn-md btn-label-primary ms-8" onclick="addNewCustomer(event)">
                                    <i class="icon-base ti tabler-plus icon-md"></i> Add New Customer
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- /Organize Card -->

                    <!-- Variants -->
                    <div class="card mb-6 new-customer">
                        <div class="card-header">
                            <h5 class="card-title mb-0">New Customer</h5>
                        </div>
                        <div class="card-body">
                            <div class="card mb-6">
                                <div class="card-header px-0 pt-0">
                                    <div class="nav-align-top">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <button
                                                    type="button"
                                                    class="nav-link active"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#form-tabs-company"
                                                    aria-controls="form-tabs-company"
                                                    role="tab"
                                                    aria-selected="true">
                                                    <span class="icon-base ti tabler-user icon-lg d-sm-none"></span>
                                                    <span class="d-none d-sm-block">Company Info</span>
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    type="button"
                                                    class="nav-link"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#form-tabs-billing-address"
                                                    aria-controls="form-tabs-billing-address"
                                                    role="tab"
                                                    aria-selected="false">
                                                    <span class="icon-base ti tabler-link icon-lg d-sm-none"></span>
                                                    <span class="d-none d-sm-block">Billing Address</span>
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    type="button"
                                                    class="nav-link"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#form-tabs-site-address"
                                                    aria-controls="form-tabs-site-address"
                                                    role="tab"
                                                    aria-selected="false">
                                                    <span class="icon-base ti tabler-link icon-lg d-sm-none"></span>
                                                    <span class="d-none d-sm-block">Site Address</span>
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button
                                                    type="button"
                                                    class="nav-link"
                                                    data-bs-toggle="tab"
                                                    data-bs-target="#form-tabs-account"
                                                    aria-controls="form-tabs-account"
                                                    role="tab"
                                                    aria-selected="false">
                                                    <span class="icon-base ti tabler-user-cog icon-lg d-sm-none"></span>
                                                    <span class="d-none d-sm-block">Account Details</span>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="tab-content p-0">
                                        <!-- Compnay Info -->
                                        <div class="tab-pane fade active show" id="form-tabs-company" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-user-company">Company</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                id="formtabs-user-company"
                                                                class="form-control"
                                                                placeholder="Company Name"
                                                                aria-label="jdoe1 ltd"
                                                                name="companyName" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-contact-personname">Contact Person</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-contact-personname"
                                                                placeholder="John Doe"
                                                                name="contactPersonName"
                                                                aria-label="John Doe" />
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
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-contact-phone">Contact Phone</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                id="formtabs-contact-phone"
                                                                class="form-control phone-mask"
                                                                placeholder="+1 (609) 988-44-11"
                                                                aria-label="john.doe@example.com"
                                                                name="contactPhone" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Billing Address -->
                                        <div class="tab-pane fade" id="form-tabs-billing-address" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-address">Billing Address</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-billing-address"
                                                                placeholder="30H Burdi Street"
                                                                name="billingAddress"
                                                                aria-label="30H Burdi Street" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-city">City</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-billing-city" class="form-select" name="billingCity">
                                                                <option value="london">London</option>
                                                                <option value="birmingham">Birmingham</option>
                                                                <option value="portsmouth">Portsmouth</option>
                                                                <option value="southampton">Southampton</option>
                                                                <option value="nottingham">Nottingham</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-state">State</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-billing-state"
                                                                placeholder="Birmingham"
                                                                name="billingState"
                                                                aria-label="Birmingham" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-country">Country</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-billing-country" class="select2 form-select" data-allow-clear="true" name="billingCountry">
                                                                <option value="">Select</option>
                                                                <option value="United Kingdom">United Kingdom</option>
                                                                <option value="United States">United States</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-postcode">Postcode</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-billing-postcode"
                                                                placeholder="A1 1AA"
                                                                name="billingPostcode"
                                                                aria-label="A1 1AA" />
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Site Address -->
                                        <div class="tab-pane fade" id="form-tabs-site-address" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-site-address">Site Address</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-site-address"
                                                                placeholder="30H Burdi Street"
                                                                name="siteAddress"
                                                                aria-label="30H Burdi Street" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-site-city">City</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-site-city" class="form-select" name="siteCity">
                                                                <option value="london">London</option>
                                                                <option value="birmingham">Birmingham</option>
                                                                <option value="portsmouth">Portsmouth</option>
                                                                <option value="southampton">Southampton</option>
                                                                <option value="nottingham">Nottingham</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-site-state">State</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-site-state"
                                                                placeholder="Birmingham"
                                                                name="siteState"
                                                                aria-label="Birmingham" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-site-country">Country</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-site-country" name="siteCountry" class="select2 form-select" data-allow-clear="true">
                                                                <option value="">Select</option>
                                                                <option value="United Kingdom">United Kingdom</option>
                                                                <option value="United States">United States</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-site-postcode">Postcode</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-site-postcode"
                                                                placeholder="A1 1AA"
                                                                name="sitePostcode"
                                                                aria-label="A1 1AA" />
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Account Details -->
                                        <div class="tab-pane fade" id="form-tabs-account" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-assets">Assets</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                id="formtabs-assets"
                                                                class="form-control"
                                                                placeholder="0.00"
                                                                name="assets" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-net-assets">Net Assets</label>
                                                        <div class="col-sm-9">
                                                            <input type="text"
                                                                id="formtabs-net-assets"
                                                                name="netAssets"
                                                                class="form-control" placeholder="0.00" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-liabilities">Liabilities</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                id="formtabs-liabilities"
                                                                name="liabilities"
                                                                class="form-control"
                                                                placeholder="0.00" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-cash-in-bank">Cash in Bank</label>
                                                        <div class="col-sm-9">
                                                            <input type="text"
                                                                id="formtabs-cash-in-bank"
                                                                name="cashInBank"
                                                                class="form-control" placeholder="0.00" />
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /Variants -->

                </div>
                <!-- /First column -->

                <!-- Second column-->
                <div class="col-12 col-lg-8">
                    <!-- Enquiry Information -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-tile mb-0">Enquiry information</h5>
                        </div>
                        <div class="card-body">

                            <!-- Description -->
                            <div class="mb-6">
                                <label class="mb-1">Description</label>
                                <div class="form-control p-0">
                                    <div class="enquiry-toolbar border-0 border-bottom">
                                        <div class="d-flex justify-content-start">
                                            <span class="ql-formats me-0">
                                                <button class="ql-bold"></button>
                                                <button class="ql-italic"></button>
                                                <button class="ql-underline"></button>
                                                <button class="ql-list" value="ordered"></button>
                                                <button class="ql-list" value="bullet"></button>
                                                <button class="ql-link"></button>
                                                <button class="ql-image"></button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="enquiry-editor border-0 pb-6" id="enquiry-description" name="enquirydescription"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Enquiry Information -->

                </div>
                <!-- /Second column -->

                <!-- Third column -->
                <div class="col-12 col-lg-4">
                    <div class="row mb-6 gy-6">
                        <div class="col-xl">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Other Information</h5>
                                    <small class="text-body-secondary float-end">(optional)</small>
                                </div>
                                <div class="card-body">

                                    <div class="mb-6">
                                        <label class="form-check-label">Enquiry Type</label>
                                        <div class="col mt-2">
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-type"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="general"
                                                    id="collapsible-enquiry-type-general"
                                                    checked="" />
                                                <label class="form-check-label" for="collapsible-enquiry-type-general">General</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-type"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="sales"
                                                    id="collapsible-enquiry-type-sales" />
                                                <label class="form-check-label" for="collapsible-enquiry-type-sales">
                                                    Sales
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-type"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="support"
                                                    id="collapsible-enquiry-type-support" />
                                                <label class="form-check-label" for="collapsible-enquiry-type-support">
                                                    Support
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-type"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="technical"
                                                    id="collapsible-enquiry-type-technical" />
                                                <label class="form-check-label" for="collapsible-enquiry-type-technical">
                                                    Technical
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label class="form-check-label">Enquiry Category</label>
                                        <div class="col mt-2">
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-category"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="query"
                                                    id="collapsible-enquiry-category-query"
                                                    checked="" />
                                                <label class="form-check-label" for="collapsible-enquiry-category-query"> Query </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-category"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="complaint"
                                                    id="collapsible-enquiry-category-complaint" />
                                                <label class="form-check-label" for="collapsible-enquiry-category-complaint">Complaint</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-category"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="request"
                                                    id="collapsible-enquiry-category-request" />
                                                <label class="form-check-label" for="collapsible-enquiry-category-request">
                                                    Request
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-category"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="feedback"
                                                    id="collapsible-enquiry-category-feedback" />
                                                <label class="form-check-label" for="collapsible-enquiry-category-feedback">
                                                    Feedback
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label class="form-check-label">Priority</label>
                                        <div class="col mt-2">
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-priotiry"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="low"
                                                    id="collapsible-enquiry-priotiry-low"
                                                    checked="" />
                                                <label class="form-check-label" for="collapsible-enquiry-priotiry-low"> Low </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-priotiry"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="medium"
                                                    id="collapsible-enquiry-priotiry-medium" />
                                                <label class="form-check-label" for="collapsible-enquiry-priotiry-medium">Medium</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input
                                                    name="collapsible-enquiry-priotiry"
                                                    class="form-check-input"
                                                    type="radio"
                                                    value="high"
                                                    id="collapsible-enquiry-priotiry-high" />
                                                <label class="form-check-label" for="collapsible-enquiry-priotiry-high">
                                                    High
                                                </label>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="col-12 mb-6">
                                        <label class="form-label" for="collapsible-source">Source</label>
                                        <textarea
                                            name="enquirySource"
                                            class="form-control"
                                            id="collapsible-source"
                                            rows="2"
                                            placeholder="https://roofcoatingindustrial.co.uk/"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row mb-6 gy-6">
                        <div class="col-xl">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Notes</h5>
                                    <small class="text-body-secondary float-end">(optional)</small>
                                </div>
                                <div class="card-body">

                                    <div class="col-12 mb-6">

                                        <textarea
                                            name="enquiryNote"
                                            class="form-control"
                                            id="collapsible-note"
                                            rows="2"
                                            placeholder="notes here"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- /Third column -->
            </div>
        </form>

        <!-- Media -->
        <div class="card mb-6">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 card-title">Related Images</h5>
                <a href="javascript:void(0);" class="fw-medium">Add media from URL</a>
            </div>
            <div class="card-body">
                <form action="/upload" class="dropzone needsclick p-0" id="dropzone-basic">
                    <div class="dz-message needsclick">
                        <p class="h4 needsclick pt-3 mb-2">Drag and drop your image here</p>
                        <p class="h6 text-body-secondary d-block fw-normal mb-2">or</p>
                        <span class="needsclick btn btn-sm btn-label-primary" id="btnBrowse">Browse image</span>
                    </div>
                    <div class="fallback">
                        <input name="file" type="file" />
                    </div>
                </form>
            </div>
        </div>
        <!-- /Media -->
    </div>
</div>
<!-- / Content -->

@endsection