@extends('layouts.master')

@section('title', 'Vehicles')

@section('scripts')
    @parent

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script type="text/javascript">
        $(function() {
            let ajaxUrl = "{{ route('vehicles.index') }}";

            var table = $('.datatables-ajax').DataTable({
                processing: true,
                serverSide: true,
                ajax: ajaxUrl,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '50px'
                    },
                    {
                        data: 'registration_display',
                        name: 'registration_no'
                    },
                    {
                        data: 'make_model_display',
                        name: 'manufacturer'
                    },
                    {
                        data: 'category_name',
                        name: 'category_id'
                    },
                    {
                        data: 'mot_due',
                        name: 'mot_expiry_date'
                    },
                    {
                        data: 'tax_due',
                        name: 'tax_expiry_date'
                    },
                    {
                        data: 'service_due',
                        name: 'next_service_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        width: '140px',
                        className: 'text-center'
                    }
                ]
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $(document).on('click', '.delete', function() {
                var id = $(this).data('id');

                // SweetAlert confirmation
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This vehicle record will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('vehicles.destroy', ':id') }}'.replace(':id', id),
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire('Deleted!', response.message, 'success');
                                $('#Vehicle-table').DataTable().ajax.reload();
                            },
                            error: function() {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>


@endsection

@section('styles')
    @parent
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 row-gap-3">
            <div>
                <h4 class="fw-bold mb-1">Fleet & Commercial Vehicles</h4>
                <p class="text-muted mb-0 small">Active company vehicles, roadworthiness compliance, MOT and service schedules</p>
            </div>

            <div class="d-flex justify-content-end">
                <a type="button" class="btn btn-primary" href="{{ route('vehicles.create') }}">
                    <i class="icon-base ti tabler-plus me-1"></i>
                    Add Vehicle
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-datatable table-responsive p-3">
                <table class="datatables-ajax table table-hover" id="Vehicle-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Vehicle & Registration</th>
                            <th>Make & Model</th>
                            <th>Category</th>
                            <th>MOT Expiry</th>
                            <th>Tax Expiry</th>
                            <th>Next Service</th>
                            <th style="width: 140px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!-- / Content -->

@endsection
