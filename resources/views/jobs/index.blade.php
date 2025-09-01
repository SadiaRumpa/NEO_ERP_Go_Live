@extends('layouts.master')

@section('title', 'Jobs')

@section('scripts')
    @parent

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

    <script type="text/javascript">
        $(function() {
            let ajaxUrl = "{{ route('job.index') }}";
            @if (!empty($completed) && $completed === true)
                ajaxUrl = "{{ route('job.show.completed') }}";
            @elseif (!empty($rejected) && $rejected === true)
                ajaxUrl = "{{ route('job.show.rejected') }}";
            @endif
console.log(ajaxUrl);

            var table = $('.datatables-ajax').DataTable({
                processing: true,
                serverSide: true,
                ajax: ajaxUrl,

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'job_name',
                        name: 'job_name',
                        render: function(data, type, row) {
                            let badgeClass = 'bg-label-info'; // Default

                            switch (row.job_status.toLowerCase()) {
                                case 'new':
                                case 'accepted':
                                    badgeClass = 'info';
                                    break;
                                case 'inprogress':
                                    badgeClass = 'warning';
                                    break;
                                case 'completed':
                                    badgeClass = 'success';
                                    break;
                                case 'rejected':
                                    badgeClass = 'danger';
                                    break;
                                default:
                                    badgeClass = 'secondary';
                            }

                            return `
                            <div class="d-flex flex-column">
                                <span class="emp_name text-truncate text-heading fw-medium">${row.job_name}</span>
                                <small class="emp_post text-truncate">
                                <span class="badge rounded-pill bg-label-${badgeClass}">${row.job_status}</span>
                                ${moment(row.created_at).format('DD/MM/YYYY')}
                                </small>
                            </div>
                        `;

                        }
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name',
                        render: function(data, type, row) {
                            let html = '';

                            if (row.company_name) {
                                html += '<strong>' + row.company_name + '</strong><br>';
                            }

                            if (row.customer_name) {
                                html += row.customer_name + '<br>';
                            }

                            if (row.customer_email) {
                                html += '<i class="icon-base fas fa-envelope mb-2 mr-2"></i> ' + row
                                    .customer_email + '<br>';
                            }

                            if (row.customer_phone) {
                                html += '<i class="icon-base fas fa-phone mb-2 mr-2"></i> ' + row
                                    .customer_phone + '<br>';
                            }

                            if (row.customer_mobile) {
                                html += '<i class="icon-base fas fa-mobile mb-2"></i> ' + row
                                    .customer_mobile;
                            }

                            return html;
                        }
                    },
                    {
                        data: 'enquiry',
                        name: 'enquiry',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endsection

@section('content')

    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
                <h4 class="mb-1">Jobs</h4>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <div class="d-flex gap-4">
                    <a href="{{ route('job.show.rejected') }}" class="btn btn-label-danger"
                        @if (isset($rejected) && $rejected == true) disabled @endif>Rejected</a>
                    <a href="{{ route('job.show.completed') }}" class="btn btn-label-warning"
                        @if (isset($completed) && $completed == true) disabled @endif>Completed</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-datatable table-responsive p-2">
                <table class="datatables-ajax table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>JOB TITLE</th>
                            <th width="20%">COMPANY INFO</th>
                            <th>ENQUIRY</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!-- / Content -->

@endsection
