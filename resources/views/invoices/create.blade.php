@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Invoices /</span> Create</h4>
            <p class="text-muted mb-0">Create a new UK tax invoice with itemized line items and CIS deduction options.</p>
        </div>
        <div>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Invoices
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('invoices.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label required">Customer</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Select Customer...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Linked Job (Optional)</label>
                    <select name="job_id" class="form-select">
                        <option value="">None (Standalone Invoice)</option>
                        @foreach($jobs as $j)
                            <option value="{{ $j->id }}" {{ old('job_id') == $j->id ? 'selected' : '' }}>
                                {{ $j->job_name ?: ('Job #' . $j->id) }} ({{ $j->customer->company_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Invoice Date</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Terms</label>
                    <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms', '30 Days Net') }}">
                </div>
            </div>

            <!-- CIS Settings -->
            <div class="card bg-label-secondary border mb-4">
                <div class="card-body">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="cisApplicable" name="cis_applicable" value="1" {{ old('cis_applicable') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="cisApplicable">Apply UK CIS (Construction Industry Scheme) Reverse Charge Deduction</label>
                    </div>
                    <div class="row g-3" id="cisRateContainer" style="{{ old('cis_applicable') ? '' : 'display: none;' }}">
                        <div class="col-md-4">
                            <label class="form-label">CIS Deduction Rate (%)</label>
                            <select name="cis_deduction_rate" class="form-select">
                                <option value="20.00" {{ old('cis_deduction_rate', '20.00') == '20.00' ? 'selected' : '' }}>20% Standard Subcontractor Rate</option>
                                <option value="30.00" {{ old('cis_deduction_rate') == '30.00' ? 'selected' : '' }}>30% Unregistered Higher Rate</option>
                            </select>
                            <small class="text-muted">CIS deduction applies strictly to the labor portion of qualifying line items.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items Table -->
            <h5 class="fw-bold mb-3">Line Items</h5>
            <div class="table-responsive mb-3">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%;">Description / Scope</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 20%;">Unit Price (£)</th>
                            <th style="width: 15%; text-align: center;">Is Labor?</th>
                            <th style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="text" name="items[0][description]" class="form-control" required placeholder="Description of roofing works or materials">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0.01" name="items[0][quantity]" class="form-control" value="1.00" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0.00" name="items[0][unit_price]" class="form-control" value="0.00" required>
                            </td>
                            <td class="text-center align-middle">
                                <input type="checkbox" name="items[0][is_labor]" value="1" class="form-check-input">
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row" disabled><i class="bx bx-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-outline-primary mb-4" id="addRowBtn">
                <i class="bx bx-plus me-1"></i> Add Line Item
            </button>

            <div class="mb-3">
                <label class="form-label">Notes / Payment Advice</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes or payment remittance details...">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="card-footer border-top d-flex justify-content-end gap-2">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Save Invoice</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cisCheck = document.getElementById('cisApplicable');
        const cisContainer = document.getElementById('cisRateContainer');
        cisCheck.addEventListener('change', function() {
            cisContainer.style.display = this.checked ? 'flex' : 'none';
        });

        let rowIndex = 1;
        document.getElementById('addRowBtn').addEventListener('click', function() {
            const tbody = document.querySelector('#itemsTable tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="items[${rowIndex}][description]" class="form-control" required placeholder="Description of works"></td>
                <td><input type="number" step="0.01" min="0.01" name="items[${rowIndex}][quantity]" class="form-control" value="1.00" required></td>
                <td><input type="number" step="0.01" min="0.00" name="items[${rowIndex}][unit_price]" class="form-control" value="0.00" required></td>
                <td class="text-center align-middle"><input type="checkbox" name="items[${rowIndex}][is_labor]" value="1" class="form-check-input"></td>
                <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bx bx-trash"></i></button></td>
            `;
            tbody.appendChild(row);
            rowIndex++;
            attachRemoveHandlers();
        });

        function attachRemoveHandlers() {
            document.querySelectorAll('.remove-row').forEach(btn => {
                btn.onclick = function() {
                    const rows = document.querySelectorAll('#itemsTable tbody tr');
                    if (rows.length > 1) {
                        this.closest('tr').remove();
                    }
                };
            });
        }
        attachRemoveHandlers();
    });
</script>
@endsection
