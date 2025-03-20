@extends('layouts.master')

@section('title', 'Dashboard')

@section('scripts')
@parent
<script src="{{asset('assets/vendor/libs/quill/katex.js')}}"></script>
<script src="{{asset('assets/vendor/libs/quill/quill.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/dropzone/dropzone.js')}}"></script>
<script src="{{asset('assets/vendor/libs/jquery-repeater/jquery-repeater.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/tagify/tagify.js')}}"></script>
@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/fonts/flag-icons.css')}}" />
<!-- Page CSS -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/cards-advance.css')}}" />
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6">
        <!-- Card Border Shadow -->
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-truck icon-28px"></i></span>
                        </div>
                        <h4 class="mb-0">42</h4>
                    </div>
                    <p class="mb-1">New Enquiries</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">+18.2%</span>
                        <small class="text-body-secondary">than last week</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-warning"><i class="icon-base ti tabler-alert-triangle icon-28px"></i></span>
                        </div>
                        <h4 class="mb-0">8</h4>
                    </div>
                    <p class="mb-1">Leads under review</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">-8.7%</span>
                        <small class="text-body-secondary">than last week</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-danger"><i class="icon-base ti tabler-git-fork icon-28px"></i></span>
                        </div>
                        <h4 class="mb-0">27</h4>
                    </div>
                    <p class="mb-1">Ongoing Jobs</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">+4.3%</span>
                        <small class="text-body-secondary">than last week</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar me-4">
                            <span class="avatar-initial rounded bg-label-info"><i class="icon-base ti tabler-clock icon-28px"></i></span>
                        </div>
                        <h4 class="mb-0">13</h4>
                    </div>
                    <p class="mb-1">Finished Jobs</p>
                    <p class="mb-0">
                        <span class="text-heading fw-medium me-2">-2.5%</span>
                        <small class="text-body-secondary">than last week</small>
                    </p>
                </div>
            </div>
        </div>
        <!--/ Card Border Shadow -->

        <!-- New Enquiries -->
        <div class="col-md-12 col-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 me-2">New Enquiries</h5>

                    <button
                        class="btn rounded-pill btn-warning waves-effect waves-light"
                        type="button">
                        SEE ALL >>
                    </button>


                </div>
                <div class="table-responsive">
                    <table class="table table-borderless border-top">
                        <thead class="border-bottom">
                            <tr>
                                <th>DATE</th>
                                <th>LEAD FROM</th>
                                <th>TOWN</th>
                                <th>COMPANY</th>
                                <th>CONTACT</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="pt-5">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0 text-heading">Sent</p>
                                        <small class="text-body text-nowrap">1 Mar 2025</small>
                                    </div>
                                </td>
                                <td class="pt-5"><span class="badge bg-label-success">https://coventryindustrialroofing.co.uk/</span></td>
                                <td class="pt-5">
                                    <p class="mb-0 text-heading">Brighton</p>
                                </td>
                                <td class="pt-5">
                                    <p class="mb-0 text-heading">ABC COMPANY</p>
                                </td>
                                <td class="pt-5">
                                    <p class="mb-0 text-heading">Malcom X</p>
                                    <p class="mb-0 text-heading">8088484848</p>
                                </td>
                                <td class="pt-5">
                                    <a href="javascript:void(0);" class="btn btn-xs rounded-pill btn-label-info waves-effect">REVIEW</a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <p class="mb-0 text-heading">Sent</p>
                                        <small class="text-body text-nowrap">1 Mar 2025</small>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-success">https://coventryindustrialroofing.co.uk/</span></td>
                                <td>
                                    <p class="mb-0 text-heading">Brighton</p>
                                </td>
                                <td>
                                    <p class="mb-0 text-heading">ABC COMPANY</p>
                                </td>
                                <td>
                                    <p class="mb-0 text-heading">Malcom X</p>
                                    <p class="mb-0 text-heading">8088484848</p>
                                </td>
                                <td class="pt-5">
                                    <a href="javascript:void(0);" class="btn btn-xs rounded-pill btn-label-info waves-effect">REVIEW</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--/ New Enquiries -->

        <!-- Leads Under Review -->
        <div class="col-md-6 col-12">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 me-2">Leads Under Review</h5>
                    <button
                        class="btn rounded-pill btn-warning waves-effect waves-light"
                        type="button">
                        SEE ALL >>
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless border-top">
                        <thead class="border-bottom">
                            <tr>
                                <th>ID</th>
                                <th>COMPANY</th>
                                <th>JOB TYPE</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="pt-5">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0 text-heading">Enquiry 1830</p>
                                    </div>
                                </td>
                                <td class="pt-5">
                                    <p class="mb-0 text-heading">ABC COMPANY</p>
                                </td>
                                <td class="pt-5">
                                    <p class="mb-0 text-heading">Roof hole repire</p>
                                </td>
                                <td class="pt-5"><span class="badge bg-label-warning">Under Review</span></td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--/ Leads Under Review -->





    </div>
</div>

@endsection