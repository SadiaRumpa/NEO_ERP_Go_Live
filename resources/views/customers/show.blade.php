@extends('layouts.master')

@section('title', 'Customer')

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
    // $(function() {
    //     $('.phone-mask').inputmask({
    //         mask: '+9 (999) 999-99-99'
    //     });
    //     $('.select2').select2();
    // });
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
    <div
        class="d-flex flex-column flex-sm-row align-items-center justify-content-sm-between mb-6 text-center text-sm-start gap-2">
        <div class="mb-2 mb-sm-0">
            <h4 class="mb-1">Customer Details</h4>
        </div>
        <button type="button" class="btn btn-label-danger delete-customer">Delete Customer</button>
    </div>

    @if(session('success'))
    <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
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
                                <a
                                    href="javascript:;"
                                    class="btn btn-xs btn-primary waves-effect waves-light"
                                    data-bs-target="#editUser"
                                    data-bs-toggle="modal">
                                    <span class="icon-base ti tabler-edit icon-xs me-1"></span>Edit Details
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-container">
                                <ul class="list-unstyled mb-6">
                                    <li class="mb-2">
                                        <span class="h6 me-1">Contact Person:</span>
                                        <span>{{ $customer->contact_firstname }} {{ $customer->contact_lastname }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="h6 me-1">Email:</span>
                                        <span>{{ $customer->contact_email }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="h6 me-1">Contact Mobile:</span>
                                        <span class="badge bg-label-success">{{ $customer->contact_mobile }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="h6 me-1">Contact Phone:</span>
                                        <span>{{ $customer->contact_phone }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-6">
                        <div class="card-header header-elements">
                            <h5 class="mb-0 me-2">Financial Info</h5>

                            <div class="card-header-elements ms-auto">
                                <a
                                    href="javascript:;"
                                    class="btn btn-xs btn-primary waves-effect waves-light"
                                    data-bs-target="#editFininfo"
                                    data-bs-toggle="modal">
                                    <span class="icon-base ti tabler-edit icon-xs me-1"></span>Add / Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-container">
                                <ul class="list-unstyled mb-6">
                                    <li class="mb-2">
                                        <span class="h6 me-1">Reg. Number:</span>
                                        <span>{{ $customer->findetails->registration_number  ?? 'N/A' }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="h6 me-1">Assets:</span>
                                        <span>{{ $customer->findetails->assets ?? 'N/A' }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="h6 me-1">Net Assets:</span>
                                        <span class="badge bg-label-success">{{ $customer->findetails->net_assets ?? 'N/A' }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="h6 me-1">Liabilities:</span>
                                        <span>{{ $customer->findetails->liabilities ?? 'N/A' }}</span>
                                    </li>
                                    <li class="mb-2">
                                        <span class="h6 me-1">Cash in Bank:</span>
                                        <span>{{ $customer->findetails->cash_in_bank ?? 'N/A' }}</span>
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
                        <a class="nav-link active" href="app-ecommerce-customer-details-billing.html">
                            <i class="icon-base ti tabler-map-pin icon-sm me-1_5"></i>Add Site & Billing Address</a>
                    </li>

                </ul>
            </div>
            <!--/ Customer Pills -->
            <!-- Address -->
            <div class="row">
                <!-- Billing Address -->
                @if($customer->billingaddress)
                <div class="col-md-6 col-xxl-6 mb-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="bg-label-primary rounded text-center mb-4 pt-4">
                                <iframe
                                    width="100%"
                                    height="450"
                                    frameborder="0"
                                    style="border:0"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google.maps_api_key') }}&q={{ $lead->leadaddress->address }}, {{ $lead->leadaddress->county }}, {{ $lead->leadaddress->postcode }}+{{ $lead->leadaddress->address }}, {{ $lead->leadaddress->county }}, {{ $lead->leadaddress->country }}"
                                    allowfullscreen>
                                </iframe>
                            </div>
                            <h5 class="mb-2">Site Address</h5>
                            <p class="small">
                                Next Generation Frontend Architecture Using Layout Engine And React Native Web.
                            </p>
                            <div class="row mb-4 g-3">
                                <div class="col-6">
                                    <div class="d-flex">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-calendar-event icon-28px"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-nowrap">17 Nov 23</h6>
                                            <small>Date</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-clock icon-28px"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-nowrap">32 minutes</h6>
                                            <small>Duration</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary w-100">Join the event</a>
                        </div>
                    </div>
                </div>
                @endif
                <!--/ Billing Address -->
                <!-- Site Address -->
                <div class="col-md-6 col-xxl-6 mb-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="bg-label-primary rounded text-center mb-4 pt-4">
                                <img
                                    class="img-fluid"
                                    src="../../assets/img/illustrations/girl-with-laptop.png"
                                    alt="Card girl image"
                                    width="140" />
                            </div>
                            <h5 class="mb-2">Upcoming Webinar</h5>
                            <p class="small">
                                Next Generation Frontend Architecture Using Layout Engine And React Native Web.
                            </p>
                            <div class="row mb-4 g-3">
                                <div class="col-6">
                                    <div class="d-flex">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-calendar-event icon-28px"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-nowrap">17 Nov 23</h6>
                                            <small>Date</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex">
                                        <div class="avatar flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-clock icon-28px"></i></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-nowrap">32 minutes</h6>
                                            <small>Duration</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="javascript:void(0);" class="btn btn-primary w-100">Join the event</a>
                        </div>
                    </div>
                </div>
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
                            <input
                                type="text"
                                id="modalEditUserFirstName"
                                name="contactPersonFirstName"
                                class="form-control"
                                placeholder="John"
                                value="{{ $customer->contact_firstname }}"
                                required />
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserLastName">Last Name</label>
                            <input
                                type="text"
                                id="modalEditUserLastName"
                                name="contactPersonLastName"
                                class="form-control"
                                placeholder="Doe"
                                value="{{ $customer->contact_lastname }}" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserEmail">Email</label>
                            <input
                                type="text"
                                id="modalEditUserEmail"
                                name="contactEmail"
                                class="form-control"
                                placeholder="example@domain.com"
                                value="{{ $customer->contact_email }}" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditMobileNumber">Mobile Number</label>
                            <input
                                type="text"
                                id="modalMobileNumber"
                                name="contactMobile"
                                class="form-control modal-edit-mobile-number"
                                placeholder="123 456 7890"
                                value="{{ $customer->contact_mobile }}" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditUserPhone">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text">UK (+1)</span>
                                <input
                                    type="text"
                                    id="modalEditUserPhone"
                                    name="contactPhone"
                                    class="form-control phone-number-mask"
                                    placeholder="202 555 0111"
                                    value="{{ $customer->contact_phone }}"
                                    required />
                            </div>
                        </div>

                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-3">Submit</button>
                            <button
                                type="reset"
                                class="btn btn-label-secondary"
                                data-bs-dismiss="modal"
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
                    <form accept="application/json" method="POST" class="row g-6" action="{{ route('customer.updatefinancialdetails', $customer->id) }}">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="modalEditUserRegNumber">Reg. Number</label>
                            <input
                                type="text"
                                id="modalEditUserRegNumber"
                                name="registration_no"
                                class="form-control"
                                value="{{ $customer->findetails->registration_number ?? '' }}" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditAssets">Assets</label>
                            <input
                                type="text"
                                id="modalEditAssets"
                                name="assets"
                                class="form-control"
                                placeholder="0.00"
                                value="{{ $customer->findetails->assets ?? 0 }}" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditnetAssets">Net Assets</label>
                            <input
                                type="text"
                                id="modalEditnetAssets"
                                name="netAssets"
                                class="form-control"
                                placeholder="0.00"
                                value="{{ $customer->findetails->net_assets ?? 0 }}" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditLiabilities">Liabilities</label>
                            <input type="number"
                                min="0"
                                id="modalEditLiabilities"
                                name="liabilities"
                                class="form-control"
                                placeholder="0.00"
                                value="{{ $customer->findetails->liabilities ?? 0 }}" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalEditCashinBank">Cash in Bank</label>
                            <input type="number"
                                min="0"
                                id="modalEditCashinBank"
                                name="cashInBank"
                                class="form-control"
                                placeholder="0.00"
                                value="{{ $customer->findetails->cash_in_bank ?? 0 }}" />
                        </div>

                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-3">Submit</button>
                            <button
                                type="reset"
                                class="btn btn-label-secondary"
                                data-bs-dismiss="modal"
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
                        <p class="address-subtitle">Add new address for express delivery</p>
                    </div>
                    <form id="addNewAddressForm" class="row g-6" onsubmit="return false">
                        <div class="col-12 form-control-validation">
                            <div class="row">
                                <div class="col-md mb-md-0 mb-4">
                                    <div class="form-check custom-option custom-option-icon">
                                        <label class="form-check-label custom-option-content" for="customRadioHome">
                                            <span class="custom-option-body">
                                                <svg
                                                    width="28"
                                                    height="28"
                                                    viewBox="0 0 28 28"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        opacity="0.2"
                                                        d="M16.625 23.625V16.625H11.375V23.625H4.37501V12.6328C4.37437 12.5113 4.39937 12.391 4.44837 12.2798C4.49737 12.1686 4.56928 12.069 4.65939 11.9875L13.4094 4.03592C13.5689 3.88911 13.7778 3.80762 13.9945 3.80762C14.2113 3.80762 14.4202 3.88911 14.5797 4.03592L23.3406 11.9875C23.4287 12.0706 23.4992 12.1706 23.548 12.2814C23.5969 12.3922 23.6231 12.5117 23.625 12.6328V23.625H16.625Z" />
                                                    <path
                                                        d="M23.625 23.625V12.6328C23.623 12.5117 23.5969 12.3922 23.548 12.2814C23.4992 12.1706 23.4287 12.0706 23.3406 11.9875L14.5797 4.03592C14.4202 3.88911 14.2113 3.80762 13.9945 3.80762C13.7777 3.80762 13.5689 3.88911 13.4094 4.03592L4.65937 11.9875C4.56926 12.069 4.49736 12.1686 4.44836 12.2798C4.39936 12.391 4.37436 12.5113 4.375 12.6328V23.625M1.75 23.625H26.25M16.625 23.625V17.5C16.625 17.2679 16.5328 17.0454 16.3687 16.8813C16.2046 16.7172 15.9821 16.625 15.75 16.625H12.25C12.0179 16.625 11.7954 16.7172 11.6313 16.8813C11.4672 17.0454 11.375 17.2679 11.375 17.5V23.625"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <span class="custom-option-title">Home</span>
                                                <small> Delivery time (9am – 9pm) </small>
                                            </span>
                                            <input
                                                name="customRadioIcon"
                                                class="form-check-input"
                                                type="radio"
                                                value=""
                                                id="customRadioHome"
                                                checked />
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md mb-md-0 mb-4">
                                    <div class="form-check custom-option custom-option-icon">
                                        <label class="form-check-label custom-option-content" for="customRadioOffice">
                                            <span class="custom-option-body">
                                                <svg
                                                    width="28"
                                                    height="28"
                                                    viewBox="0 0 28 28"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        opacity="0.2"
                                                        d="M15.75 23.625V4.375C15.75 4.14294 15.6578 3.92038 15.4937 3.75628C15.3296 3.59219 15.1071 3.5 14.875 3.5H4.375C4.14294 3.5 3.92038 3.59219 3.75628 3.75628C3.59219 3.92038 3.5 4.14294 3.5 4.375V23.625" />
                                                    <path
                                                        d="M1.75 23.625H26.25M15.75 23.625V4.375C15.75 4.14294 15.6578 3.92038 15.4937 3.75628C15.3296 3.59219 15.1071 3.5 14.875 3.5H4.375C4.14294 3.5 3.92038 3.59219 3.75628 3.75628C3.59219 3.92038 3.5 4.14294 3.5 4.375V23.625M24.5 23.625V11.375C24.5 11.1429 24.4078 10.9204 24.2437 10.7563C24.0796 10.5922 23.8571 10.5 23.625 10.5H15.75M7 7.875H10.5M8.75 14.875H12.25M7 19.25H10.5M19.25 19.25H21M19.25 14.875H21"
                                                        stroke-opacity="0.9"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                </svg>
                                                <span class="custom-option-title"> Office </span>
                                                <small> Delivery time (9am – 5pm) </small>
                                            </span>
                                            <input
                                                name="customRadioIcon"
                                                class="form-check-input"
                                                type="radio"
                                                value=""
                                                id="customRadioOffice" />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 form-control-validation col-md-6">
                            <label class="form-label" for="modalAddressFirstName">First Name</label>
                            <input
                                type="text"
                                id="modalAddressFirstName"
                                name="modalAddressFirstName"
                                class="form-control"
                                placeholder="John" />
                        </div>
                        <div class="col-12 form-control-validation col-md-6">
                            <label class="form-label" for="modalAddressLastName">Last Name</label>
                            <input
                                type="text"
                                id="modalAddressLastName"
                                name="modalAddressLastName"
                                class="form-control"
                                placeholder="Doe" />
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="modalAddressCountry">Country</label>
                            <select
                                id="modalAddressCountry"
                                name="modalAddressCountry"
                                class="select2 form-select"
                                data-allow-clear="true">
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
                        <div class="col-12">
                            <label class="form-label" for="modalAddressAddress1">Address Line 1</label>
                            <input
                                type="text"
                                id="modalAddressAddress1"
                                name="modalAddressAddress1"
                                class="form-control"
                                placeholder="12, Business Park" />
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="modalAddressAddress2">Address Line 2</label>
                            <input
                                type="text"
                                id="modalAddressAddress2"
                                name="modalAddressAddress2"
                                class="form-control"
                                placeholder="Mall Road" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalAddressLandmark">Landmark</label>
                            <input
                                type="text"
                                id="modalAddressLandmark"
                                name="modalAddressLandmark"
                                class="form-control"
                                placeholder="Nr. Hard Rock Cafe" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalAddressCity">City</label>
                            <input
                                type="text"
                                id="modalAddressCity"
                                name="modalAddressCity"
                                class="form-control"
                                placeholder="Los Angeles" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalAddressLandmark">State</label>
                            <input
                                type="text"
                                id="modalAddressState"
                                name="modalAddressState"
                                class="form-control"
                                placeholder="California" />
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="modalAddressZipCode">Zip Code</label>
                            <input
                                type="text"
                                id="modalAddressZipCode"
                                name="modalAddressZipCode"
                                class="form-control"
                                placeholder="99950" />
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="billingAddress" />
                                <label for="billingAddress" class="form-switch-label">Use as a billing address?</label>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-primary me-3">Submit</button>
                            <button
                                type="reset"
                                class="btn btn-label-secondary"
                                data-bs-dismiss="modal"
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