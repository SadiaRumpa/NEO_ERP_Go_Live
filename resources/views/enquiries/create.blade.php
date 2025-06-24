@extends('layouts.master')

@section('title', 'Create Enquiry')

@section('scripts')
    @parent
    <script src="{{ asset('assets/vendor/libs/quill/katex.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/quill/quill.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
    <!-- Page JS -->
    <script>
        $('.new-customer').hide();
    </script>
    <script>
        $(function() {
            const editors = [];

            $('.enquiry-editor').each(function() {
                const $editor = $(this);
                const hiddenInput = $editor.data('target');
                const toolbarSelector = $editor.data('toolbar');

                const quill = new Quill(this, {

                    placeholder: 'Write here...',
                    theme: 'snow'
                });

                editors.push({
                    quill,
                    hiddenInput
                });
            });

            $('form').on('submit', function() {
                editors.forEach(({
                    quill,
                    hiddenInput
                }) => {
                    const html = quill.root.innerHTML.trim();
                    const input = document.querySelector(hiddenInput);
                    if (input) {
                        input.value = html === '<p><br></p>' ? '' : html;
                    }
                });
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('attachments');
            const previewContainer = document.getElementById('preview');

            if (input) {
                input.addEventListener('change', function(event) {
                    previewContainer.innerHTML = ''; // Clear existing previews

                    Array.from(this.files).forEach(file => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.style.height = '80px';
                                img.style.border = '1px solid #ccc';
                                img.style.padding = '2px';
                                img.style.borderRadius = '4px';
                                previewContainer.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                });
            }
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

                    if (!response || response.error || !response.address || !Array.isArray(response.address
                            .addresses)) {
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
    </script>
@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/typography.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/katex.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/editor.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
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
                                        <select id="customer" class="select2 form-select"
                                            data-placeholder="Select customer" name="customer_id">
                                            <option value="">Select customer</option>
                                            @foreach ($customers as $cust)
                                                <option value="{{ $cust->id }}">{{ $cust->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button"
                                        class="fw-medium btn btn-md btn-label-primary ms-8 toggle-customer-btn"
                                        onclick="toggleNewCustomer(event)">
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
                                                    <button type="button" class="nav-link active" data-bs-toggle="tab"
                                                        data-bs-target="#form-tabs-company"
                                                        aria-controls="form-tabs-company" role="tab"
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
                                                            <label class="form-label"
                                                                for="formtabs-user-company">Company</label>
                                                            <input type="text" id="formtabs-user-company"
                                                                class="form-control" placeholder="Company Name"
                                                                aria-label="jdoe1 ltd" name="companyName" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="row mb-1">
                                                            <label class="form-label"
                                                                for="formtabs-contact-personname">Contact Person</label>
                                                            <div class="col-sm-6">
                                                                <input type="text" class="form-control"
                                                                    id="formtabs-contact-personfirstname"
                                                                    placeholder="First Name" name="contactPersonFirstName"
                                                                    aria-label="First Name" />
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <input type="text" class="form-control"
                                                                    id="formtabs-contact-personlastname"
                                                                    placeholder="Last Name" name="contactPersonLastName"
                                                                    aria-label="Last Name" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="formtabs-contact-email">Contact
                                                                email</label>
                                                            <input type="email" id="formtabs-contact-email"
                                                                class="form-control" placeholder="john.doe@example.com"
                                                                aria-label="john.doe@example.com" name="contactEmail" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="formtabs-contact-phone">Contact
                                                                Phone</label>
                                                            <input type="text" id="formtabs-contact-phone"
                                                                name="contactPhone" class="form-control"
                                                                placeholder="14169884411" maxlength="11" pattern="\d{11}"
                                                                inputmode="numeric"
                                                                oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label"
                                                                for="formtabs-contact-mobile">Contact Mobile</label>
                                                            <input type="text" id="formtabs-contact-mobile"
                                                                name="contactMobile" class="form-control"
                                                                placeholder="14169884411" maxlength="11" pattern="\d{11}"
                                                                inputmode="numeric"
                                                                oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

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
                                    <div class="form-control p-0">
                                        <div id="toolbar_1" class="enquiry-toolbar border-0 border-bottom"></div>
                                        <div class="enquiry-editor border-0 pb-6" data-target="#description_input_1"
                                            data-toolbar="#toolbar_1"></div>
                                        <input type="hidden" name="enquirydescription" id="description_input_1">
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
                                        <div class="col-md-12 mb-4">
                                            <label class="form-label" for="attachments">Attachments (Multiple
                                                Images)</label>
                                            <input type="file" class="form-control" id="attachments"
                                                name="attachments[]" accept="image/*" multiple>

                                            {{-- Preview container --}}
                                            <div class="image-preview mt-2 d-flex flex-wrap gap-2" id="preview"></div>
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
                                            <div class="mb-6">
                                                <div class="form-control p-0">
                                                    <div id="toolbar_2" class="enquiry-toolbar border-0 border-bottom">
                                                    </div>
                                                    <div class="enquiry-editor border-0 pb-6"
                                                        data-target="#description_input_2" data-toolbar="#toolbar_2">
                                                    </div>
                                                    <input type="hidden" name="enquiryNote" id="description_input_2">
                                                </div>
                                            </div>
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
                                                    <input type="text" class="form-control"
                                                        id="formtabs-enquiry-postcode" name="enquiry_postcode"
                                                        placeholder="Check postcode" aria-label="Check postcode"
                                                        aria-describedby="button-addon2" required />
                                                    <button class="btn btn-outline-primary waves-effect" type="button"
                                                        id="button-addon2" onclick="checkaddress()">Check</button>
                                                </div>
                                                <div id="loading-spinner" style="display: none;">
                                                    <span class="spinner-border spinner-border-sm" role="status"
                                                        aria-hidden="true"></span>
                                                    Searching...
                                                </div>
                                                <!-- Suggestions Container -->
                                                <div id="address-suggestions" class="list-group mt-1"
                                                    style="display:none;"></div>
                                                <input type="hidden" id="posttown" value="" />
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label" for="formtabs-enquiry-address">Address</label>
                                                <textarea class="form-control" id="formtabs-enquiry-address" name="enquiry_address" placeholder="30H Burdi Street"
                                                    required></textarea>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label" for="formtabs-enquiry-county">County</label>
                                                <input class="form-control" id="formtabs-enquiry-county"
                                                    name="enquiry_county" placeholder="Birmingham"
                                                    aria-label="Birmingham" required />
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label" for="formtabs-enquiry-country">Country</label>
                                                <input class="form-control" id="formtabs-enquiry-country"
                                                    name="enquiry_country" value="United Kingdom" readonly />
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
                                                    <input name="collapsible-enquiry-type" class="form-check-input"
                                                        type="radio" value="general"
                                                        id="collapsible-enquiry-type-general" checked="" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-type-general">General</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-type" class="form-check-input"
                                                        type="radio" value="sales"
                                                        id="collapsible-enquiry-type-sales" />
                                                    <label class="form-check-label" for="collapsible-enquiry-type-sales">
                                                        Sales
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-type" class="form-check-input"
                                                        type="radio" value="support"
                                                        id="collapsible-enquiry-type-support" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-type-support">
                                                        Support
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-type" class="form-check-input"
                                                        type="radio" value="technical"
                                                        id="collapsible-enquiry-type-technical" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-type-technical">
                                                        Technical
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-6">
                                            <label class="form-check-label">Enquiry Category</label>
                                            <div class="col mt-2">
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-category" class="form-check-input"
                                                        type="radio" value="query"
                                                        id="collapsible-enquiry-category-query" checked="" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-category-query"> Query </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-category" class="form-check-input"
                                                        type="radio" value="complaint"
                                                        id="collapsible-enquiry-category-complaint" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-category-complaint">Complaint</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-category" class="form-check-input"
                                                        type="radio" value="request"
                                                        id="collapsible-enquiry-category-request" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-category-request">
                                                        Request
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-category" class="form-check-input"
                                                        type="radio" value="feedback"
                                                        id="collapsible-enquiry-category-feedback" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-category-feedback">
                                                        Feedback
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-6">
                                            <label class="form-check-label">Priority</label>
                                            <div class="col mt-2">
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-priotiry" class="form-check-input"
                                                        type="radio" value="low"
                                                        id="collapsible-enquiry-priotiry-low" checked="" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-priotiry-low"> Low </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-priotiry" class="form-check-input"
                                                        type="radio" value="medium"
                                                        id="collapsible-enquiry-priotiry-medium" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-priotiry-medium">Medium</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input name="collapsible-enquiry-priotiry" class="form-check-input"
                                                        type="radio" value="high"
                                                        id="collapsible-enquiry-priotiry-high" />
                                                    <label class="form-check-label"
                                                        for="collapsible-enquiry-priotiry-high">
                                                        High
                                                    </label>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="col-12 mb-6">
                                            <label class="form-label" for="collapsible-source">Source</label>
                                            <textarea name="enquiry_source" class="form-control" id="collapsible-source" rows="2"
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
