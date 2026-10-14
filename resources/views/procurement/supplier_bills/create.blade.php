@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1">
            <span class="text-muted fw-light">Procurement / Supplier Bills /</span> Enter Bill for {{ $purchaseOrder->po_number }}
        </h4>
        <p class="text-muted mb-0">Record invoice from <strong>{{ $purchaseOrder->supplier->name }}</strong> for 3-Way Match Audit against PO and GRNs.</p>
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

    <form method="POST" action="{{ route('procurement.supplier-bills.store') }}">
        @csrf
        <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Supplier Invoice Header</h5>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Supplier Invoice / Bill Number</label>
                        <input type="text" name="bill_number" class="form-control" value="{{ old('bill_number') }}" placeholder="e.g. INV-990214" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Invoice Date</label>
                        <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Payment Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Invoice Line Items vs. Agreed Order Rates</h5>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Item Description</th>
                            <th style="width: 12%;" class="text-center">PO Agreed Qty</th>
                            <th style="width: 12%;" class="text-center">GRN Received Qty</th>
                            <th style="width: 12%;" class="text-center">Billed Quantity</th>
                            <th style="width: 12%;" class="text-end">Agreed Price (£)</th>
                            <th style="width: 12%;" class="text-end">Billed Price (£)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrder->items as $idx => $item)
                        @php
                            $grnQty = $item->grnItems()->sum('quantity_received');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $item->description }}</strong>
                                <input type="hidden" name="items[{{ $idx }}][purchase_order_item_id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $idx }}][description]" value="{{ $item->description }}">
                            </td>
                            <td class="text-center text-muted">{{ number_format($item->quantity, 2) }}</td>
                            <td class="text-center fw-bold text-success">{{ number_format($grnQty, 2) }}</td>
                            <td>
                                <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][billed_quantity]" class="form-control text-center fw-bold" value="{{ $grnQty > 0 ? $grnQty : $item->quantity }}" required>
                            </td>
                            <td class="text-end text-muted">£{{ number_format($item->unit_price, 2) }}</td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][billed_unit_price]" class="form-control text-end fw-bold" value="{{ $item->unit_price }}" required>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('procurement.purchase-orders.show', $purchaseOrder) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-check-shield me-1"></i> Save & Run 3-Way Match</button>
        </div>
    </form>
</div>
@endsection
