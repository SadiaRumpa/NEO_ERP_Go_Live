@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Procurement / Purchase Orders /</span> Create Order</h4>
        <p class="text-muted mb-0">Raise an official trade Purchase Order linked to a site job or warehouse.</p>
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

    <form method="POST" action="{{ route('procurement.purchase-orders.store') }}" id="poForm">
        @csrf
        <div class="card mb-4">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Order Header & Destination</h5>
            </div>
            <div class="card-body pt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Supplier</label>
                        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                            <option value="">Select Supplier...</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ $s->payment_terms }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Destination Job (Optional)</label>
                        <select name="job_id" class="form-select">
                            <option value="">No Direct Job Allocation</option>
                            @foreach($jobs as $j)
                                <option value="{{ $j->id }}" {{ old('job_id') == $j->id ? 'selected' : '' }}>
                                    Job #{{ $j->id }} - {{ Str::limit($j->job_title ?? $j->job_name, 35) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Commits cost directly into this Job's WIP ledger.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Central Warehouse (Optional)</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">No Warehouse Stock Allocation</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->name ?? $w->warehouse_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Receiving updates central warehouse inventory.</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Order Date</label>
                        <input type="date" name="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Expected Delivery Date</label>
                        <input type="date" name="expected_delivery_date" class="form-control" value="{{ old('expected_delivery_date') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Delivery & Special Site Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="e.g. Deliver to rear access gate, crane required">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Line Items & Agreed Pricing</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                    <i class="bx bx-plus me-1"></i> Add Line Item
                </button>
            </div>
            <div class="table-responsive">
                <table class="table" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 25%;">Catalog Item</th>
                            <th style="width: 35%;">Description / Specification</th>
                            <th style="width: 15%;">Quantity</th>
                            <th style="width: 15%;">Unit Price (£)</th>
                            <th style="width: 10%;" class="text-end">Total (£)</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        <tr class="item-row">
                            <td>
                                <select name="items[0][stock_item_id]" class="form-select stock-item-select">
                                    <option value="">Custom Item</option>
                                    @foreach($stockItems as $item)
                                        <option value="{{ $item->id }}" data-price="{{ $item->unit_price ?? 0 }}">{{ $item->name ?? $item->item_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[0][description]" class="form-control item-desc" placeholder="e.g. Marley Modern Roof Tiles (Smooth Grey)" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][quantity]" class="form-control item-qty text-end" value="1.00" min="0.01" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][unit_price]" class="form-control item-price text-end" value="0.00" min="0" required>
                            </td>
                            <td class="text-end fw-bold align-middle item-total">£0.00</td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-item-btn"><i class="bx bx-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end fw-bold" id="subtotalDisplay">£0.00</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">VAT (20%):</td>
                            <td class="text-end fw-bold" id="vatDisplay">£0.00</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end fw-bold fs-5 text-primary">Total Amount:</td>
                            <td class="text-end fw-bold fs-5 text-primary" id="totalDisplay">£0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('procurement.purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Save Purchase Order</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = 1;
    const container = document.getElementById('itemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = qty * price;
            row.querySelector('.item-total').textContent = '£' + total.toFixed(2);
            subtotal += total;
        });

        const vat = subtotal * 0.20;
        const total = subtotal + vat;

        document.getElementById('subtotalDisplay').textContent = '£' + subtotal.toFixed(2);
        document.getElementById('vatDisplay').textContent = '£' + vat.toFixed(2);
        document.getElementById('totalDisplay').textContent = '£' + total.toFixed(2);
    }

    addItemBtn.addEventListener('click', function () {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][stock_item_id]" class="form-select stock-item-select">
                    <option value="">Custom Item</option>
                    @foreach($stockItems as $item)
                        <option value="{{ $item->id }}" data-price="{{ $item->unit_price ?? 0 }}">{{ $item->item_name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][description]" class="form-control item-desc" placeholder="Item description" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][quantity]" class="form-control item-qty text-end" value="1.00" min="0.01" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control item-price text-end" value="0.00" min="0" required>
            </td>
            <td class="text-end fw-bold align-middle item-total">£0.00</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-item-btn"><i class="bx bx-trash"></i></button>
            </td>
        `;
        container.appendChild(tr);
        rowIndex++;
        calculateTotals();
    });

    container.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
            calculateTotals();
        }
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-item-btn')) {
            if (document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            }
        }
    });

    calculateTotals();
});
</script>
@endsection
