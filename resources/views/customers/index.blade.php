@extends('layouts.master')

@section('title', 'Customers')

@section('scripts')
@parent

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script type="text/javascript">
    $(function() {
        let ajaxUrl = "{{ route('customers.index') }}";

        @if (!empty($archived) && $archived === true)
            ajaxUrl = "{{ route('customer.show.archived') }}";
        @endif
        
        var table = $('.datatables-ajax').DataTable({
            processing: true,
            serverSide: true,
            ajax: ajaxUrl,

            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'company_name',
                    name: 'company_name'
                },
                {
                    data: 'contact_name',
                    name: 'contact_name',
                    render: function(data, type, row) {
                        let name = ((row.contact_firstname || '') + ' ' + (row.contact_lastname || '')).trim();
                        return name ? '<strong class="text-heading">' + name + '</strong>' : '<span class="text-muted">-</span>';
                    },
                    searchable: false
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let items = [];
                        if (row.contact_email && row.contact_email.trim()) {
                            let email = row.contact_email.trim();
                            items.push(
                                '<div class="contact-detail-row">' +
                                    '<span class="contact-icon-badge contact-email-badge" title="Email: ' + email + '">' +
                                        '<i class="bx bx-envelope"></i>' +
                                    '</span>' +
                                    '<a href="mailto:' + email + '" class="contact-detail-text" title="' + email + '">' + email + '</a>' +
                                '</div>'
                            );
                        }
                        if (row.contact_phone && row.contact_phone.trim()) {
                            let phone = row.contact_phone.trim();
                            items.push(
                                '<div class="contact-detail-row">' +
                                    '<span class="contact-icon-badge contact-phone-badge" title="Office Phone: ' + phone + '">' +
                                        '<i class="bx bx-phone"></i>' +
                                    '</span>' +
                                    '<a href="tel:' + phone + '" class="contact-detail-text" title="' + phone + '">' + phone + '</a>' +
                                '</div>'
                            );
                        }
                        if (row.contact_mobile && row.contact_mobile.trim()) {
                            let mobile = row.contact_mobile.trim();
                            items.push(
                                '<div class="contact-detail-row">' +
                                    '<span class="contact-icon-badge contact-mobile-badge" title="Mobile: ' + mobile + '">' +
                                        '<i class="bx bx-mobile-alt"></i>' +
                                    '</span>' +
                                    '<a href="tel:' + mobile + '" class="contact-detail-text" title="' + mobile + '">' + mobile + '</a>' +
                                '</div>'
                            );
                        }
                        if (items.length === 0) {
                            return '<span class="text-muted fst-italic small">No contact details</span>';
                        }
                        return '<div class="contact-details-list">' + items.join('') + '</div>';
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
    $(document).on('click', '.delete', function() {
        let customerId = $(this).data('id');
        let url = `/customers/${customerId}`;

        if (confirm("Are you sure you want to delete this customer?")) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content') // Get CSRF token
                },
                success: function(response) {
                    toastr.success(response.message);
                    $('.datatables-ajax').DataTable().ajax.reload(); // Reload DataTable
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message);
                }
            });
        }
    });
</script>
@endsection

@section('styles')
@parent
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

@endsection

@section('content')

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
        <div class="d-flex flex-column justify-content-center">
            <h4 class="mb-1">Customers</h4>
        </div>
        <div class="d-flex align-content-center flex-wrap gap-4">

            <a href="{{ url('customers/create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1_5"></i>
                New Customer
            </a>
            <div class="d-flex gap-4">
                @if (isset($archived))
                <a href="{{ route('customers.index') }}" class="btn btn-label-primary"
                        >Active Customers</a>
                @else
                <a href="{{ route('customer.show.archived') }}" class="btn btn-label-warning"
                        @if (isset($archived) && $archived == true) disabled @endif>Archived</a>
                <!-- <a class="btn btn-label-warning">Archived</a> -->
                 @endif
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-datatable table-responsive p-2">
            <table class="datatables-ajax table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>COMPANY</th>
                        <th>CONTACT PERSON</th>
                        <th>CONTACT</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- / Content -->

@endsection