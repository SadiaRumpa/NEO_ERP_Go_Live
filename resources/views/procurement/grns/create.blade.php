@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Procurement / Goods Received Notes /</span> Log Delivery for {{ $purchaseOrder->po_number }}
        </h4>
        <p class="text-muted mb-0">Record physical delivery from <strong>{{ $purchaseOrder->supplier->name }}</strong>.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('procurement.grns.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Delivery Details & Paper Proof</h5>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">Date Received</label>
                        <input type="date" name="received_date" class="form-control" value="{{ old('received_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Driver Delivery Note # / Ticket Ref</label>
                        <input type="text" name="delivery_note_number" class="form-control" value="{{ old('delivery_note_number') }}" placeholder="e.g. DEL-88491">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Proof of Delivery Photo (Paper Ticket)</label>
                        <input type="file" name="delivery_photo" class="form-control" accept="image/*,.pdf">
                        <small class="text-muted">Take a photo of the signed physical paper delivery docket.</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Receiving Notes & Gate / Bay Comments</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="e.g. Delivered on 2 pallets, no transport damage">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Items Verification</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%;">Item Description</th>
                            <th style="width: 15%;" class="text-center">Ordered Qty</th>
                            <th style="width: 15%;" class="text-center">Already Received</th>
                            <th style="width: 15%;" class="text-center">Quantity Delivered Now</th>
                            <th style="width: 20%;">Material Condition</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $idx => $item)
                        @php
                            $remaining = max(0, $item->quantity - $item->received_quantity);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $item->description }}</strong>
                                <input type="hidden" name="items[{{ $idx }}][purchase_order_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="text-center fw-bold">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-center text-muted">{{ number_format($item->received_quantity, 2) }}</td>
                            <td>
                                <input type="number" step="0.01" min="0" max="{{ $remaining > 0 ? $remaining * 2 : 1000 }}" name="items[{{ $idx }}][quantity_received]" class="form-control text-center fw-bold text-success" value="{{ $remaining > 0 ? $remaining : 0 }}">
                            </td>
                            <td>
                                <select name="items[{{ $idx }}][condition]" class="form-select form-select-sm">
                                    <option value="good">Good Condition (Accepted)</option>
                                    <option value="damaged">Damaged / Reject</option>
                                    <option value="shortage">Shortage Logged</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('procurement.purchase-orders.show', $purchaseOrder) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Confirm Goods Receipt</button>
        </div>
    </form>
</div>
@endsection
