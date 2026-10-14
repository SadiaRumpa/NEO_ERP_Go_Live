<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Job Pack - #{{ $job->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 15px;
        }
        .header {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table { width: 100%; }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 8px;
            vertical-align: top;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 15px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #475569;
        }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            background: #e2e8f0;
            color: #334155;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .signoff-box {
            margin-top: 30px;
            border: 1px solid #cbd5e1;
            padding: 12px;
            background: #fafafa;
        }
        .signoff-table { width: 100%; }
        .signoff-table td { width: 50%; padding: 10px; }
        .sig-line {
            border-bottom: 1px solid #64748b;
            height: 35px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">Field Work Order & Job Pack</div>
                    <div style="font-size: 11px; color: #64748b;">Generated on: {{ now()->format('d M Y H:i') }} UTC</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-size: 16px; font-weight: bold;">JOB #{{ $job->id }}</div>
                    <div style="font-size: 12px; color: #475569;">Status: <strong>{{ strtoupper($job->job_status ?? 'NEW') }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Customer & Site Address -->
    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong style="color: #475569;">CUSTOMER DETAILS:</strong><br>
                <strong>{{ $job->customer?->company_name ?? 'Client' }}</strong><br>
                Contact: {{ $job->customer?->contact_name ?? 'N/A' }}<br>
                Email: {{ $job->customer?->contact_email ?? 'N/A' }}<br>
                Phone: {{ $job->customer?->contact_phone ?? 'N/A' }}
            </td>
            <td style="width: 50%;">
                <strong style="color: #475569;">SITE LOCATION:</strong><br>
                @php
                    $siteAddress = $job->addresses?->first();
                @endphp
                @if($siteAddress)
                    {{ $siteAddress->address_line_1 }}<br>
                    @if($siteAddress->address_line_2) {{ $siteAddress->address_line_2 }}<br> @endif
                    {{ $siteAddress->city }}, {{ $siteAddress->postcode }}
                @else
                    Customer primary office or on-file address.
                @endif
            </td>
        </tr>
    </table>

    <!-- Roofing Scope & Flags -->
    <div class="section-title">Scope of Roofing Works</div>
    <table class="data-table">
        <tr>
            <th>Annual Maintenance</th>
            <th>Installations</th>
            <th>Repairs</th>
            <th>Testing & Inspection</th>
        </tr>
        <tr>
            <td>{{ $job->annual_maintenance ? 'YES (Active)' : 'No' }}</td>
            <td>{{ $job->installations ? 'YES (Active)' : 'No' }}</td>
            <td>{{ $job->repairs ? 'YES (Active)' : 'No' }}</td>
            <td>{{ $job->testing ? 'YES (Active)' : 'No' }}</td>
        </tr>
    </table>

    <!-- Schedules & Field Workers -->
    <div class="section-title">Schedule & Field Crew Assignments</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Schedule Window</th>
                <th>Status</th>
                <th>Assigned Engineers / Field Operatives</th>
            </tr>
        </thead>
        <tbody>
            @forelse($job->schedules ?? [] as $sched)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($sched->start_date)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($sched->end_date)->format('d M Y') }}</td>
                    <td>
                        <span class="badge {{ $sched->status === 'completed' ? 'badge-success' : '' }}">
                            {{ ucfirst($sched->status) }}
                            @if($sched->is_provisional) (Provisional) @endif
                        </span>
                    </td>
                    <td>
                        @if($sched->workers && $sched->workers->isNotEmpty())
                            {{ $sched->workers->map(fn($w) => $w->user?->name ?? "Worker #{$w->user_id}")->implode(', ') }}
                        @else
                            <em style="color: #94a3b8;">No individual workers assigned to this window</em>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8;">No schedules currently allocated.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Quotation / Line Items -->
    @if($job->quotation)
        <div class="section-title">Approved Bill of Quantities / Scope Pricing</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $hasItems = false; @endphp
                @if($job->quotation->sections)
                    @foreach($job->quotation->sections as $sec)
                        @foreach($sec->items as $item)
                            @php $hasItems = true; @endphp
                            <tr>
                                <td>{{ $item->item_name ?? $item->description }}</td>
                                <td class="text-right">{{ $item->quantity ?? 1 }}</td>
                                <td class="text-right">£{{ number_format((float)($item->unit_price ?? 0), 2) }}</td>
                                <td class="text-right">£{{ number_format((float)($item->total_price ?? (($item->quantity ?? 1) * ($item->unit_price ?? 0))), 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
                @if(!$hasItems)
                    <tr>
                        <td colspan="3">Approved Quotation Scope</td>
                        <td class="text-right">£{{ number_format((float)($job->quotation->total_amount ?? 0), 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <!-- Health & Safety / Site Sign-Off -->
    <div class="signoff-box">
        <div style="font-weight: bold; margin-bottom: 8px; text-transform: uppercase; color: #1e293b;">
            Health & Safety RAMS Compliance & Site Handover
        </div>
        <div style="font-size: 10px; color: #475569; margin-bottom: 12px;">
            By signing below, the Lead Engineer confirms that site risk assessments (RAMS) and working-at-height controls have been fully implemented, and the Client confirms satisfactory completion or work order commencement.
        </div>
        <table class="signoff-table">
            <tr>
                <td>
                    <strong>Lead Roofing Engineer</strong>
                    <div class="sig-line"></div>
                    <div style="font-size: 10px; color: #64748b;">Printed Name & Date</div>
                </td>
                <td>
                    <strong>Customer Site Representative</strong>
                    <div class="sig-line"></div>
                    <div style="font-size: 10px; color: #64748b;">Printed Name & Date</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
