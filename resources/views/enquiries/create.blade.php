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
<script>
    $('.new-customer').hide();
    $(function() {
        // Comment editor
        const commentEditor = document.querySelector('.enquiry-editor');
        const hiddenInput = document.getElementById('enquirydescription_input');
        let quill;

        if (commentEditor) {
            quill = new Quill(commentEditor, {
                modules: {
                    toolbar: '.enquiry-toolbar'
                },
                placeholder: 'Enquiry Description',
                theme: 'snow'
            });
        }

        // Sync Quill content on form submit
        $('form').on('submit', function() {
            if (quill) {
                const html = quill.root.innerHTML.trim();
                if (html === '<p><br></p>') {
                    hiddenInput.value = '';
                } else {
                    hiddenInput.value = html;
                }
            }
        });
    });
</script>
<script>
    function toggleNewCustomer(event) {
        if (event) event.preventDefault();

        const $section = $('.new-customer');
        const $button = $('.toggle-customer-btn'); // Use a class to select the button
        const $select = $('#customer'); // Directly using ID

        $section.toggle(); // Show/hide the form

        if ($section.is(':visible')) {
            // If section is visible, change to 'Cancel' with cross icon
            $button.html('<i class="icon-base ti tabler-x icon-md"></i> Cancel');
            $select.prop('disabled', true); // Enable the select field

        } else {
            // If section is hidden, revert to 'Add New Customer' with plus icon
            $button.html('<i class="icon-base ti tabler-plus icon-md"></i> Add New Customer');
            $select.prop('disabled', false); // Enable the select field
        }
    }

    function checkaddress() {
        let postcode = $('#formtabs-enquiry-postcode').val().replace(/\s/g, '');

        if (!postcode) {
            toastr.error("Please enter a postcode.");
            return;
        }

        $('#loading-spinner').show();
        $('#address-suggestions').hide().empty(); // Clear old suggestions

        $.ajax({
            url: `/checkpostcode/${postcode}`,
            method: 'GET',
            success: function(response) {
                $('#loading-spinner').hide();

                if (!response || response.error || !response.address || !Array.isArray(response.address.addresses)) {
                    $('#formtabs-enquiry-address').val("No address found.");
                    return;
                }

                let suggestions = response.address.addresses;

                if (suggestions.length === 0) {
                    $('#formtabs-enquiry-address').val("No address found.");
                    return;
                }

                let suggestionList = suggestions.map(address =>
                    `<a href="#" class="list-group-item list-group-item-action suggestion-item">${address}</a>`
                ).join('');

                $('#address-suggestions').html(suggestionList).show();
                $('#posttown').val(response.address.post_town);
            },
            error: function() {
                $('#loading-spinner').hide();
                $('#address-suggestions').hide();
                $('#formtabs-enquiry-address').val("Error fetching address.");
            }
        });
    }

    $(document).ready(function() {
        // Event listener to handle clicking on a suggestion (run this once on page load)
        $(document).on('click', '.suggestion-item', function(e) {
            e.preventDefault();
            let selectedAddress = $(this).text();
            let posttown = $('#posttown').val();
            $.ajax({
                url: `/checkposttown/${posttown}`,
                method: 'GET',
                success: function(response) {
                    if (response && response.address) {
                        $('#formtabs-enquiry-address').val(selectedAddress);
                        $('#formtabs-enquiry-county').val(response.address.county);
                        $('#formtabs-enquiry-country').val(response.address.country);
                        $('#address-suggestions').hide();
                    } else {
                        $('#formtabs-enquiry-address').val("Error fetching address.");
                    }
                },
                error: function() {
                    $('#formtabs-enquiry-address').val("Error fetching address.");
                }
            });

        });

        // Hide suggestions when clicking outside
        $(document).click(function(event) {
            if (!$(event.target).closest('#formtabs-enquiry-postcode, #address-suggestions').length) {
                $('#address-suggestions').hide();
            }
        });
    });
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
<style>
    .new-customer {
        display: none;
        /* Hidden initially */
        padding: 15px;
        border: 1px solid #ccc;
        margin-top: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
    }
</style>
@endsection

@section('content')
<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="app-ecommerce">
        <form action="{{ route('enquiries.store') }}" method="POST" enctype="multipart/form-data" class="row g-6">
            @csrf
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
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
                                <button type="button" class="fw-medium btn btn-md btn-label-primary ms-8 toggle-customer-btn" onclick="toggleNewCustomer(event)">
                                    <i class="icon-base ti tabler-plus icon-md"></i> Add New Customer
                                </button>

                            </div>
                        </div>
                    </div>
                    <!-- /Organize Card -->

                    <!-- Customers -->
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
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="tab-content p-0">
                                        <!-- Compnay Info -->
                                        <div class="tab-pane fade active show" id="form-tabs-company" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-user-company">Company</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-user-company"
                                                            class="form-control"
                                                            placeholder="Company Name"
                                                            aria-label="jdoe1 ltd"
                                                            name="companyName" />
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row mb-1">
                                                        <label class="form-label" for="formtabs-contact-personname">Contact Person</label>
                                                        <div class="col-sm-6">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-contact-personfirstname"
                                                                placeholder="First Name"
                                                                name="contactPersonFirstName"
                                                                aria-label="First Name" />
                                                        </div>
                                                        <div class="col-sm-6">
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

                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-email">Contact email</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-email"
                                                            class="form-control"
                                                            placeholder="john.doe@example.com"
                                                            aria-label="john.doe@example.com"
                                                            name="contactEmail" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-phone">Contact Phone</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-phone"
                                                            class="form-control phone-mask"
                                                            placeholder="1 669 988 411"
                                                            aria-label="1 669 988 411"
                                                            name="contactPhone" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-mobile">Contact Mobile</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-mobile"
                                                            class="form-control phone-mask"
                                                            placeholder="1 669 988 411"
                                                            aria-label="1 669 988 411"
                                                            name="contactMobile" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Billing Address -->
                                        <!-- <div class="tab-pane fade" id="form-tabs-billing-address" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-12">
                                                    <div class="row mb-1">
                                                        <label class="form-label" for="formtabs-contact-billingContactfirstname">Contact Person</label>
                                                        <div class="col-sm-6">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-contact-billingContactfirstname"
                                                                placeholder="First Name"
                                                                name="billingContactFirstName"
                                                                aria-label="First Name" />
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-contact-personlastname"
                                                                placeholder="Last Name"
                                                                name="billingContactLastName"
                                                                aria-label="Last Name" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-email">Contact email</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-email"
                                                            class="form-control"
                                                            placeholder="john.doe@example.com"
                                                            aria-label="john.doe@example.com"
                                                            name="billingcontactEmail" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-phone">Contact Phone</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-phone"
                                                            class="form-control phone-mask"
                                                            placeholder="+1 (609) 988-44-11"
                                                            aria-label="john.doe@example.com"
                                                            name="billingcontactPhone" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-mobile">Contact Mobile</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-mobile"
                                                            class="form-control phone-mask"
                                                            placeholder="+1 (609) 988-44-11"
                                                            aria-label="john.doe@example.com"
                                                            name="billingcontactMobile" />
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-billing-address">Billing Address</label>

                                                        <textarea
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-billing-address"
                                                            placeholder="30H Burdi Street"
                                                            name="billingAddress"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-billing-county">County</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-billing-county"
                                                            placeholder="Birmingham"
                                                            name="billingCounty"
                                                            aria-label="Birmingham" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-billing-postcode">Postcode</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-billing-postcode"
                                                            placeholder="A1 1AA"
                                                            name="billingPostcode"
                                                            aria-label="A1 1AA" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-billing-country">Country</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-billing-country"
                                                            placeholder="United Kingdom"
                                                            name="billingCountry"
                                                            aria-label="United Kingdom"
                                                            value="United Kingdom" disabled />
                                                    </div>
                                                </div>


                                            </div>
                                        </div> -->

                                        <!-- Site Address -->
                                        <!-- <div class="tab-pane fade" id="form-tabs-site-address" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-12">G
                                                    <div class="row mb-1">
                                                        <label class="form-label" for="formtabs-contact-siteContactfirstname">Contact Person</label>
                                                        <div class="col-sm-6">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-contact-siteContactfirstname"
                                                                placeholder="First Name"
                                                                name="siteContactFirstName"
                                                                aria-label="First Name" />
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <input
                                                                type="text"
                                                                class="form-control"
                                                                id="formtabs-contact-personlastname"
                                                                placeholder="Last Name"
                                                                name="siteContactLastName"
                                                                aria-label="Last Name" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-email">Contact email</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-email"
                                                            class="form-control"
                                                            placeholder="john.doe@example.com"
                                                            aria-label="john.doe@example.com"
                                                            name="sitecontactEmail" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-phone">Contact Phone</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-phone"
                                                            class="form-control phone-mask"
                                                            placeholder="+1 (609) 988-44-11"
                                                            aria-label="john.doe@example.com"
                                                            name="sitecontactPhone" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="formtabs-contact-mobile">Contact Mobile</label>
                                                        <input
                                                            type="text"
                                                            id="formtabs-contact-mobile"
                                                            class="form-control phone-mask"
                                                            placeholder="+1 (609) 988-44-11"
                                                            aria-label="john.doe@example.com"
                                                            name="sitecontactMobile" />
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-site-address">site Address</label>

                                                        <textarea
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-site-address"
                                                            placeholder="30H Burdi Street"
                                                            name="siteAddress"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-site-county">County</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-site-county"
                                                            placeholder="Birmingham"
                                                            name="siteCounty"
                                                            aria-label="Birmingham" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-site-postcode">Postcode</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-site-postcode"
                                                            placeholder="A1 1AA"
                                                            name="sitePostcode"
                                                            aria-label="A1 1AA" />
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="">
                                                        <label class="form-label" for="formtabs-site-country">Country</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            id="formtabs-site-country"
                                                            placeholder="United Kingdom"
                                                            name="siteCountry"
                                                            aria-label="United Kingdom"
                                                            value="United Kingdom" disabled />
                                                    </div>
                                                </div>


                                            </div>
                                        </div> -->

                                        <!-- Account Details -->
                                        <!-- <div class="tab-pane fade" id="form-tabs-account" role="tabpanel">
                                            <div class="row g-6">
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-assetsCompanyName">Company Name</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                id="formtabs-assetsCompanyName"
                                                                class="form-control"
                                                                placeholder="name"
                                                                name="assetsCompanyName" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="row">
                                                        <label class="col-sm-3 col-form-label text-sm-end" for="formtabs-assetsRegistrationNo">Registration No.</label>
                                                        <div class="col-sm-9">
                                                            <input
                                                                type="text"
                                                                id="formtabs-assetsRegistrationNo"
                                                                class="form-control"
                                                                placeholder="Registration no"
                                                                name="assetsRegistrationNo" />
                                                        </div>
                                                    </div>
                                                </div>
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
                                        </div> -->

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /Customers -->
                </div>
                <!-- /First column -->

                <!-- Second column-->
                <div class="col-12 col-lg-8">
                    <!-- Enquiry Information -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h5 class="card-tile mb-0">Enquiry information*</h5>
                        </div>
                        <div class="card-body">
                            <!-- Description -->
                            <div class="mb-6">

                                <input type="hidden" name="enquirydescription" id="enquirydescription_input">
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
                                            </span>
                                        </div>
                                    </div>
                                    <div class="enquiry-editor border-0 pb-6" id="enquiry-description"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Enquiry Information -->
                    <!-- Enquiry Images -->
                    <div class="row mb-6 gy-6">
                        <div class="col-xl">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Upload Images</h5>
                                    <small class="text-body-secondary float-end">(optional)</small>
                                </div>
                                <div class="card-body">

                                    <div class="mb-4">
                                        <label for="formFile" class="form-label">Image 1</label>
                                        <input class="form-control" type="file" name="photo1">
                                    </div>
                                    <div class="mb-4">
                                        <label for="formFile" class="form-label">Image 2</label>
                                        <input class="form-control" type="file" name="photo2">
                                    </div>
                                    <div class="mb-4">
                                        <label for="formFile" class="form-label">Image 3</label>
                                        <input class="form-control" type="file" name="photo3">
                                    </div>
                                    <div class="mb-4">
                                        <label for="formFile" class="form-label">Image 4</label>
                                        <input class="form-control" type="file" name="photo4">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Enquiry Images -->
                    <!-- Enquiry Notes -->
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
                    <!-- /Enquiry Notes -->

                </div>
                <!-- /Second column -->

                <!-- Third column -->
                <div class="col-12 col-lg-4">
                    <div class="row mb-6 gy-6">
                        <div class="col-xl">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Enquiry Location*</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-6">
                                        <div class="col-md-12">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="formtabs-enquiry-postcode" name="enquiry_postcode" placeholder="Check postcode" aria-label="Check postcode" aria-describedby="button-addon2" required />
                                                <button class="btn btn-outline-primary waves-effect" type="button" id="button-addon2" onclick="checkaddress()">Check</button>
                                            </div>
                                            <div id="loading-spinner" style="display: none;">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                Searching...
                                            </div>
                                            <!-- Suggestions Container -->
                                            <div id="address-suggestions" class="list-group mt-1" style="display:none;"></div>
                                            <input type="hidden" id="posttown" value="" />
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label" for="formtabs-enquiry-address">Address</label>
                                            <textarea class="form-control" id="formtabs-enquiry-address" name="enquiry_address" placeholder="30H Burdi Street" required></textarea>
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label" for="formtabs-enquiry-county">County</label>
                                            <input class="form-control" id="formtabs-enquiry-county" name="enquiry_county" placeholder="Birmingham" aria-label="Birmingham" required />
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="form-label" for="formtabs-enquiry-country">Country</label>
                                            <input class="form-control" id="formtabs-enquiry-country" name="enquiry_country" value="United Kingdom" readonly />
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Enquiry Other Info -->
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
                                            name="enquiry_source"
                                            class="form-control"
                                            id="collapsible-source"
                                            rows="2"
                                            placeholder="https://roofcoatingindustrial.co.uk/"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /Enquiry Other Info -->
                </div>
                <!-- /Third column -->
            </div>
        </form>

    </div>
</div>
<!-- / Content -->

@endsection