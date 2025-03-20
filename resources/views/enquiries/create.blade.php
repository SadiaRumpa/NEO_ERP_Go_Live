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
<script src="{{asset('assets/js/app-ecommerce-product-add.js')}}"></script>
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
        <!-- Add Product -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Add a new Enquiry</h4>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <div class="d-flex gap-4">
                    <button class="btn btn-label-secondary">Discard</button>
                    <button class="btn btn-label-primary">Save draft</button>
                </div>
                <button type="submit" class="btn btn-primary">Send Management for Review </button>
            </div>
        </div>

        <div class="row">
            <!-- First column-->
            <div class="col-12 col-lg-8">
                <!-- Enquiry Information -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-tile mb-0">Enquiry information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-6">
                            <label class="form-label" for="enquiry-id">Enquiry ID</label>
                            <input
                                type="text"
                                class="form-control"
                                id="enquiry-id"
                                placeholder="Enquiry ID"
                                name="enquiry_id"
                                aria-label="enquiry-id" />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="mb-1">Description (Optional)</label>
                            <div class="form-control p-0">
                                <div class="comment-toolbar border-0 border-bottom">
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
                                <div class="comment-editor border-0 pb-6" id="enquiry-description"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Enquiry Information -->



            </div>
            <!-- /Second column -->

            <!-- Second column -->
            <div class="col-12 col-lg-4">
                <!-- Media -->
                <div class="card mb-6">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 card-title">Enquiry Image</h5>
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
            <!-- /Second column -->

            <!-- Third column -->
            <div class="col-12 col-lg-12">
                <!-- Customer Card -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Select Customer or Create New</h5>
                    </div>
                    <div class="card-body">
                        <!-- Customers -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="mb-6 col ecommerce-select2-dropdown">
                                <label class="form-label mb-1" for="customer">
                                    <span>Select Customer</span>
                                </label>
                                <select id="customer" class="select2 form-select" data-placeholder="Select customer">
                                    <option value="">Select customer</option>
                                    @foreach ($customers as $cust)
                                    <option value="{{ $cust->id }}">{{ $cust->company_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <a href="javascript:void(0);" class="fw-medium btn btn-icon btn-label-primary ms-4">
                                <i class="icon-base ti tabler-plus icon-md"></i>
                            </a>
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
                                                <span class="icon-base ti tabler-user icon-lg d-sm-none"></span><span class="d-none d-sm-block">Company Info</span>
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button
                                                type="button"
                                                class="nav-link"
                                                data-bs-toggle="tab"
                                                data-bs-target="#form-tabs-address"
                                                aria-controls="form-tabs-address"
                                                role="tab"
                                                aria-selected="false">
                                                <span class="icon-base ti tabler-link icon-lg d-sm-none"></span><span class="d-none d-sm-block">Address</span>
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button
                                                type="button"
                                                class="nav-link"
                                                data-bs-toggle="tab"
                                                data-bs-target="#form-tabs-site-details"
                                                aria-controls="form-tabs-site-details"
                                                role="tab"
                                                aria-selected="false">
                                                <span class="icon-base ti tabler-link icon-lg d-sm-none"></span><span class="d-none d-sm-block">Site Details</span>
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
                                                <span class="icon-base ti tabler-user-cog icon-lg d-sm-none"></span><span class="d-none d-sm-block">Account Details</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="tab-content p-0">
                                    <!-- Compnay Info -->
                                    <div class="tab-pane fade active show" id="form-tabs-company" role="tabpanel">
                                        <form>
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
                                                                name="contactPersonname"
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

                                        </form>
                                    </div>

                                    <!-- Address -->
                                    <div class="tab-pane fade" id="form-tabs-address" role="tabpanel">
                                        <form>
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-office-address">Office Address</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-office-address"
                                                                placeholder="30H Burdi Street"
                                                                name="office-address"
                                                                aria-label="30H Burdi Street" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-office-city">City</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-office-city" class="form-select" name="office-city">
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
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-office-state">State</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-office-state"
                                                                placeholder="Birmingham"
                                                                name="state"
                                                                aria-label="Birmingham" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-office-country">Country</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-office-country" class="select2 form-select" data-allow-clear="true">
                                                                <option value="">Select</option>
                                                                <option value="Australia">Australia</option>
                                                                <option value="Bangladesh">Bangladesh</option>
                                                                <option value="Belarus">Belarus</option>
                                                                <option value="Brazil">Brazil</option>
                                                                <option value="Canada">Canada</option>
                                                                <option value="China">China</option>
                                                                <option value="France">France</option>
                                                                <option value="Germany">Germany</option>
                                                                <option value="India">India</option>
                                                                <option value="Indonesia">Indonesia</option>
                                                                <option value="Israel">Israel</option>
                                                                <option value="Italy">Italy</option>
                                                                <option value="Japan">Japan</option>
                                                                <option value="Korea">Korea, Republic of</option>
                                                                <option value="Mexico">Mexico</option>
                                                                <option value="Philippines">Philippines</option>
                                                                <option value="Russia">Russian Federation</option>
                                                                <option value="South Africa">South Africa</option>
                                                                <option value="Thailand">Thailand</option>
                                                                <option value="Turkey">Turkey</option>
                                                                <option value="Ukraine">Ukraine</option>
                                                                <option value="United Arab Emirates">United Arab Emirates</option>
                                                                <option value="United Kingdom">United Kingdom</option>
                                                                <option value="United States">United States</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-office-postcode">Postcode</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-office-postcode"
                                                                placeholder="A1 1AA"
                                                                name="postcode"
                                                                aria-label="A1 1AA" />
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </form>
                                    </div>

                                    <!-- Address -->
                                    <div class="tab-pane fade" id="form-tabs-site-details" role="tabpanel">
                                        <form>
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-address">Office Address</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-billing-address"
                                                                placeholder="30H Burdi Street"
                                                                name="billing-address"
                                                                aria-label="30H Burdi Street" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-city">City</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-billing-city" class="form-select" name="billing-city">
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
                                                                name="billing-state"
                                                                aria-label="Birmingham" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-billing-country">Country</label>
                                                        <div class="col-sm-9">
                                                            <select id="formtabs-billing-country" name="billing-country" class="select2 form-select" data-allow-clear="true">
                                                                <option value="">Select</option>
                                                                <option value="Australia">Australia</option>
                                                                <option value="Bangladesh">Bangladesh</option>
                                                                <option value="Belarus">Belarus</option>
                                                                <option value="Brazil">Brazil</option>
                                                                <option value="Canada">Canada</option>
                                                                <option value="China">China</option>
                                                                <option value="France">France</option>
                                                                <option value="Germany">Germany</option>
                                                                <option value="India">India</option>
                                                                <option value="Indonesia">Indonesia</option>
                                                                <option value="Israel">Israel</option>
                                                                <option value="Italy">Italy</option>
                                                                <option value="Japan">Japan</option>
                                                                <option value="Korea">Korea, Republic of</option>
                                                                <option value="Mexico">Mexico</option>
                                                                <option value="Philippines">Philippines</option>
                                                                <option value="Russia">Russian Federation</option>
                                                                <option value="South Africa">South Africa</option>
                                                                <option value="Thailand">Thailand</option>
                                                                <option value="Turkey">Turkey</option>
                                                                <option value="Ukraine">Ukraine</option>
                                                                <option value="United Arab Emirates">United Arab Emirates</option>
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
                                                                name="billing-postcode"
                                                                aria-label="A1 1AA" />
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                        </form>
                                    </div>

                                    <!-- Account Details -->
                                    <div class="tab-pane fade" id="form-tabs-account" role="tabpanel">
                                        <form>
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-assets">Assets</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                id="formtabs-assets"
                                                                class="form-control"
                                                                placeholder="0.00" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-net-assets">Net Assets</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="formtabs-net-assets" class="form-control" placeholder="0.00" />
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
                                                                class="form-control"
                                                                placeholder="0.00" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-cash-in-bank">Cash in Bank</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" id="formtabs-cash-in-bank" class="form-control" placeholder="0.00" />
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- /Variants -->

            </div>
            <!-- /Third column -->

        </div>
    </div>
</div>
<!-- / Content -->

@endsection