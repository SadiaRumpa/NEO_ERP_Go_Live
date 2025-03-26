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
                                                                <option value="">Select</option>
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
                                                                <option value="">Select</option>
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
                                                            <input type="text"
                                                                id="formtabs-assets"
                                                                name="assets"
                                                                class="form-control"
                                                                placeholder="0.00" />
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
                                                                class="form-control"
                                                                placeholder="0.00" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-liabilities">Liabilities</label>
                                                        <div class="col-sm-9">
                                                            <input type="text"
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
                                                                class="form-control"
                                                                placeholder="0.00" />
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
                </div>
            </div>
        </form>
    </div>
</div>
<!-- / Content -->

@endsection