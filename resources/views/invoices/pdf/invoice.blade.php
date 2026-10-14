<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 25mm 20mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2d3748;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .invoice-tag {
            font-size: 26px;
            font-weight: 700;
            color: #3b82f6;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 25px;
            width: 100%;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table-items th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 2px solid #cbd5e1;
        }
        .table-items td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .table-items tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .totals-table td {
            padding: 6px 12px;
        }
        .totals-table .total-row td {
            border-top: 2px solid #3b82f6;
            border-bottom: 2px solid #3b82f6;
            font-weight: 700;
            font-size: 15px;
            color: #1e3a8a;
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-paid { background-color: #dcfce7; color: #166534; }
        .badge-draft { background-color: #f3f4f6; color: #374151; }
        .badge-sent { background-color: #e0f2fe; color: #0369a1; }
        .remittance {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="logo-title">{{ $business->business_name ?? 'NeoERP Roofing Solutions' }}</h1>
                <p style="margin: 4px 0 0 0; color: #64748b; font-size: 12px;">
                    {{ $business->business_address ?? 'Industrial Roofing & Cladding Works' }}<br>
                    Phone: {{ $business->business_phone ?? '+44 (0) 20 7946 0000' }} | Email: {{ $business->business_email ?? 'accounts@neoerp.local' }}<br>
                    VAT No: GB 982 7162 09 | Company Reg: 12345678
                </p>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <h2 class="invoice-tag">INVOICE</h2>
                <div style="margin-top: 8px;">
                    <span class="badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-box">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Invoice To:</strong><br>
                <strong style="font-size: 14px; color: #0f172a;">{{ $invoice->customer->company_name ?? 'Commercial Client' }}</strong><br>
                <span>Attn: {{ $invoice->customer->contact_name ?? 'Accounts Payable' }}</span><br>
                <span>{{ $invoice->customer->contact_email }}</span><br>
                <span>{{ $invoice->customer->contact_phone }}</span>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <table style="width: 100%; text-align: right;">
                    <tr>
                        <td style="color: #64748b; font-size: 12px;">Invoice Number:</td>
                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 12px;">Invoice Date:</td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 12px;">Payment Terms:</td>
                        <td>{{ $invoice->payment_terms ?? '30 Days Net' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 12px;">Due Date:</td>
                        <td><strong style="color: #dc2626;">{{ $invoice->due_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    @if($invoice->job)
                    <tr>
                        <td style="color: #64748b; font-size: 12px;">Job Reference:</td>
                        <td><strong>{{ $invoice->job->job_name ?? ('Job #' . $invoice->job_id) }}</strong></td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 50%;">Description / Scope of Works</th>
                <th style="text-align: center; width: 12%;">Qty</th>
                <th style="text-align: right; width: 18%;">Unit Price (£)</th>
                <th style="text-align: right; width: 20%;">Total (£)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->description }}</strong>
                    @if($item->is_labor)
                        <span style="font-size: 10px; color: #2563eb; font-weight: 600;">[Labor - CIS Qualifying]</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                <td style="text-align: right;">£{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right; font-weight: 600;">£{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td style="color: #64748b;">Subtotal (Net):</td>
            <td style="text-align: right; font-weight: 600;">£{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #64748b;">UK VAT ({{ number_format($invoice->tax_rate, 0) }}%):</td>
            <td style="text-align: right; font-weight: 600;">£{{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #64748b; font-weight: 600;">Gross Total:</td>
            <td style="text-align: right; font-weight: 600;">£{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        @if($invoice->cis_applicable && $invoice->cis_deduction_amount > 0)
        <tr>
            <td style="color: #dc2626;">Less CIS Deduction ({{ number_format($invoice->cis_deduction_rate, 0) }}% on Labor):</td>
            <td style="text-align: right; color: #dc2626; font-weight: 600;">-£{{ number_format($invoice->cis_deduction_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Net Amount Payable:</td>
            <td style="text-align: right;">£{{ number_format($invoice->net_payable, 2) }}</td>
        </tr>
    </table>

    <div class="remittance">
        <strong style="color: #1e40af; font-size: 13px;">Bank Remittance Details (BACS / Faster Payments):</strong><br>
        <span style="display: inline-block; margin-top: 4px;">
            Bank: <strong>Barclays Bank UK PLC</strong> &bull;
            Account Name: <strong>{{ $business->business_name ?? 'NeoERP Roofing Ltd' }}</strong> &bull;
            Sort Code: <strong>20-04-15</strong> &bull;
            Account No: <strong>83920194</strong><br>
            Payment Reference: <strong>{{ $invoice->invoice_number }}</strong>
        </span>
    </div>

</body>
</html>
