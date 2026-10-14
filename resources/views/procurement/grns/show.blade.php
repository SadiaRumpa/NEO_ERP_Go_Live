@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <span class="text-muted fw-light">Procurement / Goods Received Notes /</span> {{ $grn->grn_number }}
            </h4>
            <p class="text-muted mb-0">Received on {{ $grn->received_date?->format('d M Y') }} by {{ $grn->receiver->name ?? 'Staff' }}</p>
        </div>
        <div>
            <a href="{{ route('procurement.purchase-orders.show', $grn->purchaseOrder) }}" class="btn btn-outline-primary">
                <i class="bx bx-left-arrow-alt me-1"></i> Back to PO {{ $grn->purchaseOrder->po_number }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Delivery Information</h5>
                    <div class="row g-2">
                        <div class="col-6"><strong>Supplier:</strong> {{ $grn->purchaseOrder->supplier->name }}</div>
                        <div class="col-6"><strong>Purchase Order:</strong> {{ $grn->purchaseOrder->po_number }}</div>
                        <div class="col-6"><strong>Delivery Note Ref:</strong> {{ $grn->delivery_note_number ?? '—' }}</div>
                        <div class="col-6"><strong>Verified By:</strong> {{ $grn->receiver->name ?? '—' }}</div>
                        <div class="col-12 mt-2"><strong>Notes:</strong> {{ $grn->notes ?? 'No comments logged.' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Physical Proof of Delivery</h5>
                    @if($grn->delivery_photo_path)
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $grn->delivery_photo_path) }}" alt="Delivery Docket" class="img-fluid rounded border" style="max-height: 220px;">
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $grn->delivery_photo_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-fullscreen me-1"></i> Open Full Image
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bx bx-image-alt fs-1 d-block mb-1"></i>
                            No paper delivery docket was uploaded for this receipt.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Verified Line Items</h5>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>Item Description</th>
                        <th class="text-center">Quantity Received</th>
                        <th class="text-center">Condition</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grn->items as $item)
                    <tr>
                        <td><strong>{{ $item->purchaseOrderItem->description ?? 'Item' }}</strong></td>
                        <td class="text-center fw-bold fs-6 text-success">+{{ number_format($item->quantity_received, 2) }}</td>
                        <td class="text-center">
                            @if($item->condition === 'good')
                                <span class="badge bg-label-success">Good Condition</span>
                            @elseif($item->condition === 'damaged')
                                <span class="badge bg-label-danger">Damaged</span>
                            @else
                                <span class="badge bg-label-warning">Shortage</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $item->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
