@extends('layouts.master')

@section('title', 'Business Info')

@section('scripts')
@parent

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script type="text/javascript">
    $(function() {
        var table = $('.datatables-ajax').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('businessinfo.index') }}",

            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'business_name',
                    name: 'business_name',
                    render: function(data, type, row) {
                        return '<img src="' + row.business_logo + '" style="max-height: 70px; max-width:100px;" /> <br>' + row.business_name;
                    }
                },
                {
                    data: 'business_address',
                    name: 'business_address',
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let items = [];
                        if (row.business_email && row.business_email.trim()) {
                            let email = row.business_email.trim();
                            items.push(
                                '<div class="contact-detail-row">' +
                                    '<span class="contact-icon-badge contact-email-badge" title="Email: ' + email + '">' +
                                        '<i class="bx bx-envelope"></i>' +
                                    '</span>' +
                                    '<a href="mailto:' + email + '" class="contact-detail-text" title="' + email + '">' + email + '</a>' +
                                '</div>'
                            );
                        }
                        if (row.business_phone && row.business_phone.trim()) {
                            let phone = row.business_phone.trim();
                            items.push(
                                '<div class="contact-detail-row">' +
                                    '<span class="contact-icon-badge contact-phone-badge" title="Phone: ' + phone + '">' +
                                        '<i class="bx bx-phone"></i>' +
                                    '</span>' +
                                    '<a href="tel:' + phone + '" class="contact-detail-text" title="' + phone + '">' + phone + '</a>' +
                                '</div>'
                            );
                        }
                        if (row.business_website && row.business_website.trim()) {
                            let web = row.business_website.trim();
                            let url = web.startsWith('http://') || web.startsWith('https://') ? web : 'https://' + web;
                            items.push(
                                '<div class="contact-detail-row">' +
                                    '<span class="contact-icon-badge contact-website-badge" title="Website: ' + web + '">' +
                                        '<i class="bx bx-globe"></i>' +
                                    '</span>' +
                                    '<a href="' + url + '" target="_blank" rel="noopener noreferrer" class="contact-detail-text" title="' + web + '">' + web + '</a>' +
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
        let businessinfoId = $(this).data('id');
        let url = `/businessinfo/${businessinfoId}`;

        if (confirm("Are you sure you want to delete this record?")) {
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
            <h4 class="mb-1">Business Info</h4>
        </div>
        <div class="d-flex align-content-center flex-wrap gap-4">

            <a href="{{ url('businessinfo/create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1_5"></i>
                New Business Info
            </a>
            <div class="d-flex gap-4">
                <a class="btn btn-label-warning">Archived</a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-datatable table-responsive p-2">
            <table class="datatables-ajax table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Busines Name</th>
                        <th>Address</th>
                        <th>Contact</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<!-- / Content -->

@endsection