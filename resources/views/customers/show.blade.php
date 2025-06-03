@extends('layouts.master')

@section('title', 'Customer')

@section('scripts')
    @parent
    <script src="{{ asset('assets/vendor/libs/quill/katex.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/quill/quill.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/dropzone/dropzone.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
    <script>
        function checkaddress() {
            let postcode = $('#modalAddressPostcode').val().replace(/\s/g, '');

            if (!postcode) {
                toastr.error("Please enter a postcode.");
                $('#modalAddressPostcode').focus();
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
                        $('#modalAddressAddress').val("No address found.");
                        return;
                    }

                    let suggestions = response.address.addresses;

                    if (suggestions.length === 0) {
                        $('#modalAddressAddress').val("No address found.");
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
                    $('#basic-default-address').val("Error fetching address.");
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
                            $('#modalAddressAddress').val(selectedAddress);
                            $('#modalAddressCounty').val(response.address.county);
                            $('#modalAddressCountry').val(response.address.country);
                            $('#address-suggestions').hide();
                        } else {
                            $('#modalAddressAddress').val("Error fetching address.");
                        }
                    },
                    error: function() {
                        $('#modalAddressAddress').val("Error fetching address.");
                    }
                });


            });

            // Hide suggestions when clicking outside
            $(document).click(function(event) {
                if (!$(event.target).closest('#basic-default-postcode, #address-suggestions').length) {
                    $('#address-suggestions').hide();
                }
            });

            // Handle form submission for editing user
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const form = $(this);
                const formData = form.serialize();
                const customerId = {{ $customer->id }}; // Pass server-side ID to JS

                $.ajax({
                    url: `/customers/${customerId}/update-basic-info`, // Update to your route
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    beforeSend: function() {
                        // Optional: show loader or disable submit button
                    },
                    success: function(response) {
                        // Handle success (e.g., close modal, show success message)
                        toastr.success('Customer details updated successfully.');
                        $('#editUser').modal('hide');
                        location.reload(); // This will reload the entire page
                    },
                    error: function(xhr) {
                        // Handle errors
                        let errors = xhr.responseJSON.errors;
                        let message = 'Update failed.';

                        if (errors) {
                            message = Object.values(errors).flat().join('\n');
                        }
                        toastr.error(message);
                    }
                });
            });
        });
    </script>
@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/typography.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/katex.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/editor.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/dropzone/dropzone.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}" />

    <style>
        .suggestion-item {
            cursor: pointer;
        }

        .suggestion-item:hover {
            background-color: #f0f0f0;
        }

        #address-suggestions {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            position: relative;
            z-index: 9999;
            background-color: rgb(255, 157, 157);
            color: rgb(0, 0, 0);
            width: 85%;
            margin-left: 2%;
        }
    </style>
@endsection

@section('content')
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div
            class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between mb-6 text-center text-sm-start gap-2">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-1">Customer Details</h4>
            </div>
            <button type="button" class="btn btn-label-danger delete-customer">Delete Customer</button>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-2">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-2">{{ session('error') }}</div>
        @endif

        <div class="row">
            <!-- Customer-detail Sidebar -->
            <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
                <!-- Customer-detail Card -->
                <div class="card mb-6">
                    <div class="card-body pt-12">
                        <div class="customer-avatar-section">
                            <div class="d-flex align-items-center flex-column">
                                <div class="customer-info text-center mb-6">
                                    <h4 class="mb-0">{{ $customer->company_name }}</h4>
                                    <!-- <span>Customer ID #634759</span> -->
                                </div>
                            </div>
                        </div>

                        <div class="card mb-6">
                            <div class="card-header header-elements pb-4 border-bottom text-capitalize mb-4">
                                <h5 class="mb-0 me-2">Info</h5>

                                <div class="card-header-elements ms-auto">
                                    <a href="javascript:;" class="btn btn-xs btn-primary waves-effect waves-light"
                                        data-bs-target="#editUser" data-bs-toggle="modal">
                                        <span class="icon-base ti tabler-edit icon-xs me-1"></span>Edit Details
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="info-container">
                                    <ul class="list-unstyled mb-6">
                                        <li class="mb-2">
                                            <span class="h6 me-1">Contact Person:</span>
                                            <span>{{ $customer->contact_firstname }}
                                                {{ $customer->contact_lastname }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6 me-1">Email:</span>
                                            <span>{{ $customer->contact_email ?? 'N/A' }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6 me-1">Contact Mobile:</span>
                                            <span>{{ $customer->contact_mobile ?? 'N/A' }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6 me-1">Contact Phone:</span>
                                            <span>{{ $customer->contact_phone ?? 'N/A' }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-6">
                            <div class="card-header header-elements">
                                <h5 class="mb-0 me-2">Financial Info</h5>

                                <div class="card-header-elements ms-auto">
                                    <a href="javascript:;" class="btn btn-xs btn-primary waves-effect waves-light"
                                        data-bs-target="#editFininfo" data-bs-toggle="modal">
                                        <span class="icon-base ti tabler-edit icon-xs me-1"></span>Add / Edit
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="info-container">
                                    <ul class="list-unstyled mb-6">
                                        <li class="mb-2">
                                            <span class="h6 me-1">Reg. Number:</span>
                                            <span>{{ $customer->findetails->registration_number ?? 'N/A' }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6 me-1">Assets:</span>
                                            <span>{{ formatPoundNumber($customer->findetails->assets ?? 0) }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6 me-1">Net Assets:</span>
                                            <span>{{ formatPoundNumber($customer->findetails->net_assets ?? 0) }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6 me-1">Liabilities:</span>
                                            <span>{{ formatPoundNumber($customer->findetails->liabilities ?? 0) }}</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="h6 me-1">Cash in Bank:</span>
                                            <span>{{ formatPoundNumber($customer->findetails->cash_in_bank ?? 0) }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- /Customer-detail Card -->
            </div>
            <!--/ Customer Sidebar -->

            <!-- Customer Content -->
            <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
                <!-- Customer Pills -->
                <div class="nav-align-top">
                    <ul class="nav nav-pills flex-column flex-md-row mb-6 row-gap-2 flex-wrap">

                        <li class="nav-item">
                            <button class="nav-link active" data-bs-target="#addNewAddress" data-bs-toggle="modal">
                                <i class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Add Site Address
                            </button>
                            <button class="nav-link active" data-bs-target="#addNewAddress" data-bs-toggle="modal">
                                <i class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Add Billing Address
                            </button>
                        </li>

                    </ul>
                </div>
                <!--/ Customer Pills -->
                <!-- Address -->
                <div class="row">
                    <!-- Billing Address -->
                    @if ($customer->billingaddress)
                        <div class="col-md-6 col-xxl-6 mb-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="bg-label-primary rounded text-center mb-4 pt-4">
                                        <iframe width="100%" height="450" frameborder="0" style="border:0"
                                            referrerpolicy="no-referrer-when-downgrade"
                                            src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ $customer->billingaddress->address }}, {{ $customer->billingaddress->county }}, {{ $customer->billingaddress->postcode }}+{{ $customer->billingaddress->address }}, {{ $customer->billingaddress->county }}, {{ $customer->billingaddress->country }}"
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                    <h5 class="mb-2">Billing Address</h5>
                                    <p class="user-info" style="text-transform: uppercase;">
                                        {{ $customer->billingaddress->address }}, {{ $customer->billingaddress->county }},
                                        {{ $customer->billingaddress->country }}
                                    </p>

                                    <a href="javascript:void(0);" class="btn btn-outline-dark waves-effect">UPDATE</a>
                                    <a href="javascript:void(0);" class="btn btn-outline-danger waves-effect delete-address"
                                        data-id="{{ $customer->billingaddress->id }}">DELETE</a>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!--/ Billing Address -->

                    <!-- Site Address -->
                    @if ($customer->siteaddress)
                        @foreach ($customer->siteaddress as $csaddress)
                            <div class="col-md-6 col-xxl-6 mb-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="bg-label-primary rounded text-center mb-4 pt-4">
                                            <iframe width="100%" height="450" frameborder="0" style="border:0"
                                                referrerpolicy="no-referrer-when-downgrade"
                                                src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ $csaddress->address }}, {{ $csaddress->county }}, {{ $csaddress->postcode }}+{{ $csaddress->address }}, {{ $csaddress->county }}, {{ $csaddress->country }}"
                                                allowfullscreen>
                                            </iframe>
                                        </div>
                                        <h5 class="mb-2">Site Address</h5>
                                        <p class="user-info" style="text-transform: uppercase;">
                                            {{ $csaddress->address }}, {{ $csaddress->county }},
                                            {{ $csaddress->country }}
                                        </p>

                                        <a href="javascript:void(0);" class="btn btn-outline-dark waves-effect">UPDATE</a>
                                        <a href="javascript:void(0);"
                                            class="btn btn-outline-danger waves-effect delete-address"
                                            data-id="{{ $csaddress->id }}">DELETE</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    @endif
                    <!--/ Site Address -->

                </div>

                <!-- Address -->

            </div>
            <!--/ Customer Content -->
        </div>

        <!-- Modal -->
        <!-- Edit User Modal -->
        <div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-simple modal-edit-user">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-6">
                            <h4 class="mb-2">Edit User Information</h4>
                        </div>
                        <form id="editUserForm" class="row g-6" onsubmit="return false">
                            @csrf
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserFirstName">First Name</label>
                                <input type="text" id="modalEditUserFirstName" name="contactPersonFirstName"
                                    class="form-control" placeholder="John" value="{{ $customer->contact_firstname }}"
                                    required />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserLastName">Last Name</label>
                                <input type="text" id="modalEditUserLastName" name="contactPersonLastName"
                                    class="form-control" placeholder="Doe" value="{{ $customer->contact_lastname }}" />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditUserEmail">Email</label>
                                <input type="text" id="modalEditUserEmail" name="contactEmail" class="form-control"
                                    placeholder="example@domain.com" value="{{ $customer->contact_email }}" required />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditPhoneNumber">Phone Number</label>
                                <input type="text" id="modalEditPhoneNumber" name="contactPhone" class="form-control"
                                    placeholder="14169884411" maxlength="11" pattern="\d{11}" inputmode="numeric"
                                    value="{{ $customer->contact_phone }}"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalMobileNumber">Mobile Number</label>
                                <input type="text" id="modalMobileNumber" name="contactMobile" class="form-control"
                                    placeholder="14169884411" maxlength="11" pattern="\d{11}" inputmode="numeric"
                                    value="{{ $customer->contact_mobile }}"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary me-3">Submit</button>
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Edit User Modal -->
        <!-- Edit Financial Info Modal -->
        <div class="modal fade" id="editFininfo" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-simple modal-edit-financial-info">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-6">
                            <h4 class="mb-2">Edit Financial Information</h4>
                        </div>
                        <form accept="application/json" method="POST" class="row g-6"
                            action="{{ route('customer.updatefinancialdetails', $customer->id) }}">
                            @csrf
                            <div class="col-12">
                                <label class="form-label" for="modalEditUserRegNumber">Reg. Number</label>
                                <input type="text" id="modalEditUserRegNumber" name="registration_no"
                                    class="form-control"
                                    value="{{ $customer->findetails->registration_number ?? '' }}" />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditAssets">Assets</label>
                                <input type="text" id="modalEditAssets" name="assets" class="form-control"
                                    placeholder="0.00" value="{{ $customer->findetails->assets ?? 0 }}" />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditnetAssets">Net Assets</label>
                                <input type="text" id="modalEditnetAssets" name="netAssets" class="form-control"
                                    placeholder="0.00" value="{{ $customer->findetails->net_assets ?? 0 }}" />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditLiabilities">Liabilities</label>
                                <input type="number" min="0" id="modalEditLiabilities" name="liabilities"
                                    class="form-control" placeholder="0.00"
                                    value="{{ $customer->findetails->liabilities ?? 0 }}" />
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalEditCashinBank">Cash in Bank</label>
                                <input type="number" min="0" id="modalEditCashinBank" name="cashInBank"
                                    class="form-control" placeholder="0.00"
                                    value="{{ $customer->findetails->cash_in_bank ?? 0 }}" />
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary me-3">Submit</button>
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Edit Financial Info Modal -->

        <!-- Add New Address Modal -->
        <div class="modal fade" id="addNewAddress" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-simple modal-add-new-address">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="text-center mb-6">
                            <h4 class="address-title mb-2">Add New Address</h4>
                        </div>
                        <form class="row g-6" action="{{ route('customer.storeaddress', $customer->id) }}"
                            method="POST">
                            @csrf
                            <div class="col-12 form-control-validation">
                                <div class="row">
                                    <div class="col-md mb-md-0 mb-4">
                                        <div class="form-check custom-option custom-option-icon">
                                            <label class="form-check-label custom-option-content" for="customRadioHome">
                                                <span class="custom-option-body">
                                                    <svg width="28" height="28" viewBox="0 0 28 28"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path opacity="0.2"
                                                            d="M16.625 23.625V16.625H11.375V23.625H4.37501V12.6328C4.37437 12.5113 4.39937 12.391 4.44837 12.2798C4.49737 12.1686 4.56928 12.069 4.65939 11.9875L13.4094 4.03592C13.5689 3.88911 13.7778 3.80762 13.9945 3.80762C14.2113 3.80762 14.4202 3.88911 14.5797 4.03592L23.3406 11.9875C23.4287 12.0706 23.4992 12.1706 23.548 12.2814C23.5969 12.3922 23.6231 12.5117 23.625 12.6328V23.625H16.625Z" />
                                                        <path
                                                            d="M23.625 23.625V12.6328C23.623 12.5117 23.5969 12.3922 23.548 12.2814C23.4992 12.1706 23.4287 12.0706 23.3406 11.9875L14.5797 4.03592C14.4202 3.88911 14.2113 3.80762 13.9945 3.80762C13.7777 3.80762 13.5689 3.88911 13.4094 4.03592L4.65937 11.9875C4.56926 12.069 4.49736 12.1686 4.44836 12.2798C4.39936 12.391 4.37436 12.5113 4.375 12.6328V23.625M1.75 23.625H26.25M16.625 23.625V17.5C16.625 17.2679 16.5328 17.0454 16.3687 16.8813C16.2046 16.7172 15.9821 16.625 15.75 16.625H12.25C12.0179 16.625 11.7954 16.7172 11.6313 16.8813C11.4672 17.0454 11.375 17.2679 11.375 17.5V23.625"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="custom-option-title">Billing</span>
                                                </span>
                                                <input name="type" class="form-check-input" type="radio"
                                                    value="billing" id="customRadioHome" checked />
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md mb-md-0 mb-4">
                                        <div class="form-check custom-option custom-option-icon">
                                            <label class="form-check-label custom-option-content" for="customRadioOffice">
                                                <span class="custom-option-body">
                                                    <svg width="28" height="28" viewBox="0 0 28 28"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path opacity="0.2"
                                                            d="M15.75 23.625V4.375C15.75 4.14294 15.6578 3.92038 15.4937 3.75628C15.3296 3.59219 15.1071 3.5 14.875 3.5H4.375C4.14294 3.5 3.92038 3.59219 3.75628 3.75628C3.59219 3.92038 3.5 4.14294 3.5 4.375V23.625" />
                                                        <path
                                                            d="M1.75 23.625H26.25M15.75 23.625V4.375C15.75 4.14294 15.6578 3.92038 15.4937 3.75628C15.3296 3.59219 15.1071 3.5 14.875 3.5H4.375C4.14294 3.5 3.92038 3.59219 3.75628 3.75628C3.59219 3.92038 3.5 4.14294 3.5 4.375V23.625M24.5 23.625V11.375C24.5 11.1429 24.4078 10.9204 24.2437 10.7563C24.0796 10.5922 23.8571 10.5 23.625 10.5H15.75M7 7.875H10.5M8.75 14.875H12.25M7 19.25H10.5M19.25 19.25H21M19.25 14.875H21"
                                                            stroke-opacity="0.9" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="custom-option-title"> Site </span>
                                                </span>
                                                <input name="type" class="form-check-input" type="radio"
                                                    value="site" id="customRadioOffice" />
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 form-control-validation col-md-6">
                                <label class="form-label" for="modalAddressFirstName">First Name</label>
                                <input type="text" id="modalAddressFirstName" name="contactPersonFirstName"
                                    class="form-control" placeholder="John" required />
                            </div>
                            <div class="col-12 form-control-validation col-md-6">
                                <label class="form-label" for="modalAddressLastName">Last Name</label>
                                <input type="text" id="modalAddressLastName" name="contactPersonLastName"
                                    class="form-control" placeholder="Doe" />
                            </div>

                            <div class="col-12 form-control-validation col-md-4">
                                <label class="form-label" for="modalAddressLastName">eMail</label>
                                <input type="email" id="modalAddressemail" name="contactEmail" class="form-control"
                                    placeholder="info@abc.com" />
                            </div>

                            <div class="col-12 form-control-validation col-md-4">
                                <label class="form-label" for="modalAddressPhone">Phone</label>
                                <input type="text" id="modalAddressPhone" name="contactPhone" class="form-control"
                                    placeholder="14169884411" maxlength="11" pattern="\d{11}" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                            </div>

                            <div class="col-12 form-control-validation col-md-4">
                                <label class="form-label" for="modalAddressMobile">Mobile</label>
                                <input type="text" id="modalAddressMobile" name="contactMobile"
                                    class="form-control" placeholder="14169884411" maxlength="11" pattern="\d{11}"
                                    inputmode="numeric"
                                    oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11);" />
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="modalAddressPostcode">Postcode</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="modalAddressPostcode" name="postcode"
                                        placeholder="WR11 7EJ" aria-label="WR11 7EJ" aria-describedby="button-addon2"
                                        oninput="this.value = this.value.toUpperCase()" required />
                                    <button class="btn btn-outline-primary" type="button" id="button-addon2"
                                        onclick="checkaddress()">Check</button>
                                </div>

                            </div>
                            <div id="loading-spinner" style="display: none;">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Searching...
                            </div>
                            <!-- Suggestions Container -->
                            <div id="address-suggestions" class="list-group mt-1" style="display:none;"></div>
                            <input type="hidden" id="posttown" value="" />

                            <div class="col-12">
                                <label class="form-label" for="modalAddressAddress">Address</label>
                                <textarea id="modalAddressAddress" class="form-control" name="address" placeholder="12, Business Park" required></textarea>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalAddressCounty">County</label>
                                <input type="text" id="modalAddressCounty" name="county" class="form-control"
                                    placeholder="Barkingham" />
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="modalAddressCountry">Country</label>
                                <input type="text" id="modalAddressCountry" name="country" class="form-control"
                                    placeholder="United Kingdom" />
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary me-3">Submit</button>
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Add New Address Modal -->


        <!-- /Modal -->
    </div>
    <!-- / Content -->

@endsection
