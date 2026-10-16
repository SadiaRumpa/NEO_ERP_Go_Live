@extends('layouts.master')

@section('title', 'Leads')

@section('scripts')
    @parent

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

    <script type="text/javascript">
        $(function() {
            let ajaxUrl = "{{ route('leads.index') }}";

            @if (!empty($completed) && $completed === true)
                ajaxUrl = "{{ route('lead.show.completed') }}";
            @elseif (!empty($rejected) && $rejected === true)
                ajaxUrl = "{{ route('lead.show.rejected') }}";
            @elseif (!empty($inprogress) && $inprogress === true)
                ajaxUrl = "{{ route('lead.show.inprogress') }}";
            @elseif (!empty($archived) && $archived === true)
                ajaxUrl = "{{ route('lead.show.archived') }}";
            @endif

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
                        data: 'slug',
                        name: 'slug',
                        render: function(data, type, row) {
                            let badgeClass = 'bg-label-info'; // Default

                            switch (row.lead_status.toLowerCase()) {
                                case 'new':
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
                                <span class="emp_name text-truncate text-heading fw-medium">${row.slug}</span>
                                <small class="emp_post text-truncate">
                                <span class="badge rounded-pill bg-label-${badgeClass}">${row.lead_status}</span>
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
                                html += '<div class="fw-bold text-heading mb-1">' + row.company_name + '</div>';
                            }

                            if (row.customer_name) {
                                html += '<div class="text-secondary small mb-1">' + row.customer_name + '</div>';
                            }

                            let contacts = [];
                            if (row.customer_email && row.customer_email.trim()) {
                                let email = row.customer_email.trim();
                                contacts.push(
                                    '<div class="contact-detail-row">' +
                                        '<span class="contact-icon-badge contact-email-badge" title="Email: ' + email + '">' +
                                            '<i class="bx bx-envelope"></i>' +
                                        '</span>' +
                                        '<a href="mailto:' + email + '" class="contact-detail-text" title="' + email + '">' + email + '</a>' +
                                    '</div>'
                                );
                            }

                            if (row.customer_phone && row.customer_phone.trim()) {
                                let phone = row.customer_phone.trim();
                                contacts.push(
                                    '<div class="contact-detail-row">' +
                                        '<span class="contact-icon-badge contact-phone-badge" title="Office Phone: ' + phone + '">' +
                                            '<i class="bx bx-phone"></i>' +
                                        '</span>' +
                                        '<a href="tel:' + phone + '" class="contact-detail-text" title="' + phone + '">' + phone + '</a>' +
                                    '</div>'
                                );
                            }

                            if (row.customer_mobile && row.customer_mobile.trim()) {
                                let mobile = row.customer_mobile.trim();
                                contacts.push(
                                    '<div class="contact-detail-row">' +
                                        '<span class="contact-icon-badge contact-mobile-badge" title="Mobile: ' + mobile + '">' +
                                            '<i class="bx bx-mobile-alt"></i>' +
                                        '</span>' +
                                        '<a href="tel:' + mobile + '" class="contact-detail-text" title="' + mobile + '">' + mobile + '</a>' +
                                    '</div>'
                                );
                            }

                            if (contacts.length > 0) {
                                html += '<div class="contact-details-list mt-1">' + contacts.join('') + '</div>';
                            }

                            return html;
                        }
                    },
                    {
                        data: 'enquiry',
                        name: 'enquiry',
                        render: function(data, type, row) {
                            // Strip HTML tags
                            var div = document.createElement("div");
                            div.innerHTML = data;
                            var text = div.textContent || div.innerText || "";

                            // Truncate to 200 chars
                            var truncated = text.length > 200 ? text.substring(0, 200) + "..." :
                                text;

                            return truncated;
                        }
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
                <h4 class="mb-1">Leads</h4>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
                <div class="d-flex gap-4">
                    <a href="{{ route('lead.show.inprogress') }}" class="btn btn-label-primary"
                        @if (isset($inprogress) && $inprogress == true) disabled @endif>In-Progress</a>
                    <a href="{{ route('lead.show.rejected') }}" class="btn btn-label-danger"
                        @if (isset($rejected) && $rejected == true) disabled @endif>Rejected</a>
                    <a href="{{ route('lead.show.completed') }}" class="btn btn-label-warning"
                        @if (isset($completed) && $completed == true) disabled @endif>Completed</a>
                    <a href="{{ route('lead.show.archived') }}" class="btn btn-label-secondary"
                        @if (isset($archived) && $archived == true) disabled @endif>Archived</a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-datatable table-responsive p-2">
                <table class="datatables-ajax table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>LEADS</th>
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
