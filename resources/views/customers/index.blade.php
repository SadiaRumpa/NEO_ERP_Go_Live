@extends('layouts.master')

@section('title', 'Customers')

@section('scripts')
@parent

<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>

<script type="text/javascript">
    $(function() {
        var table = $('.datatables-ajax').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('customers.index') }}",

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
                        return '<strong>' + row.contact_firstname + ' ' + row.contact_lastname + '</strong>';
                    }
                },
                {
                    data: 'contact_no',
                    name: 'contact_no',
                    render: function(data, type, row) {
                        return '<i class="icon-base fas fa-envelope mb-2 mr-2"></i> ' + row.contact_email + '<br>' + '<i class="icon-base fas fa-phone mb-2 mr-2"></i> ' + row.contact_phone + '<br>' + '<i class="icon-base fas fa-mobile mb-2"></i> ' + row.contact_mobile;
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
                    alert(response.message);
                    $('.datatables-ajax').DataTable().ajax.reload(); // Reload DataTable
                },
                error: function(xhr) {
                    alert("Error deleting customer");
                }
            });
        }
    });
</script>
@endsection

@section('styles')
@parent
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />

<!-- Row Group CSS -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css')}}" />

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
                <a class="btn btn-label-warning">Archived</a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-datatable table-responsive pt-0">
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