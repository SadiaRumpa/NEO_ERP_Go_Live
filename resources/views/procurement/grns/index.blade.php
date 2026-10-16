@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Procurement /</span> Goods Received Notes (GRN)</h4>
            <p class="text-muted mb-0">Delivery notes, physical verification, paper ticket photo proof, and warehouse receipts.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
            @include('partials.per-page-selector', ['paginator' => $grns])
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>GRN Number</th>
                        <th>Purchase Order</th>
                        <th>Supplier</th>
                        <th>Delivery Ref</th>
                        <th>Date Received</th>
                        <th>Receiver</th>
                        <th>Proof Photo</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($grns as $grn)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.grns.show', $grn) }}" class="fw-bold text-success">
                                {{ $grn->grn_number }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('procurement.purchase-orders.show', $grn->purchaseOrder) }}" class="text-primary fw-bold">
                                {{ $grn->purchaseOrder->po_number }}
                            </a>
                        </td>
                        <td>{{ $grn->purchaseOrder->supplier->name ?? '—' }}</td>
                        <td>{{ $grn->delivery_note_number ?? '—' }}</td>
                        <td>{{ $grn->received_date?->format('d M Y') }}</td>
                        <td>{{ $grn->receiver->name ?? '—' }}</td>
                        <td>
                            @if($grn->delivery_photo_path)
                                <a href="{{ asset('storage/' . $grn->delivery_photo_path) }}" target="_blank" class="badge bg-label-info">
                                    <i class="bx bx-image me-1"></i> View Ticket
                                </a>
                            @else
                                <span class="text-muted small">No photo</span>
                            @endif
                        </td>
                        <td>
                            @if($grn->status === 'received')
                                <span class="badge bg-label-success">Verified</span>
                            @else
                                <span class="badge bg-label-danger">Disputed</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('procurement.grns.show', $grn) }}" class="btn btn-sm btn-icon btn-outline-info" title="View GRN">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bx bx-package fs-1 d-block mb-2"></i>
                            No Goods Received Notes logged yet. Open an approved Purchase Order to log received goods.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $grns->firstItem() ?? 0 }} to {{ $grns->lastItem() ?? 0 }} of {{ $grns->total() }} results
            </div>
            <div>
                {{ $grns->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
