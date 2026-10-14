<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quotation #{{ $quotation->id }} - Acceptance Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --success: #16a34a;
            --danger: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-900);
            line-height: 1.5;
            padding: 2rem 1rem;
        }
        .container {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: #ffffff;
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .header h1 { font-size: 1.5rem; font-weight: 700; }
        .header .badge {
            background: rgba(255,255,255,0.15);
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #dcfce7 !important; color: #15803d !important; }
        .badge-rejected { background: #fee2e2 !important; color: #b91c1c !important; }
        .content { padding: 2rem; }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .card-box {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1.25rem;
        }
        .card-box h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .card-box p { font-size: 0.9375rem; margin-bottom: 0.25rem; }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            margin-bottom: 2rem;
        }
        .items-table th, .items-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        .items-table th {
            background: var(--gray-50);
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        .text-right { text-align: right; }
        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 2rem;
        }
        .total-card {
            min-width: 250px;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 1rem;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary);
        }
        .signature-section {
            background: #f8fafc;
            border: 2px dashed var(--gray-300);
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.375rem;
        }
        .form-control {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            font-size: 0.9375rem;
        }
        .canvas-container {
            position: relative;
            background: #ffffff;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }
        canvas { display: block; width: 100%; height: 160px; cursor: crosshair; }
        .btn-clear {
            font-size: 0.75rem;
            color: var(--gray-600);
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: underline;
            margin-bottom: 0.5rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-primary { background: var(--primary); color: #ffffff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-danger { background: #fee2e2; color: var(--danger); border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fecaca; }
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9375rem;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-info { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
        .audit-info {
            font-size: 0.8125rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>Quotation #{{ $quotation->id }}</h1>
            <p style="opacity: 0.8; font-size: 0.875rem;">Version {{ $quotation->quotation_version ?? '1.0' }} &bull; Date: {{ $quotation->quotation_date ?? $quotation->created_at?->format('d M Y') }}</p>
        </div>
        <div>
            @if($quotation->status === 'accepted')
                <span class="badge badge-success">Accepted & Signed</span>
            @elseif($quotation->status === 'rejected')
                <span class="badge badge-rejected">Declined</span>
            @else
                <span class="badge">{{ ucfirst($quotation->status ?? 'Pending') }}</span>
            @endif
        </div>
    </div>

    <div class="content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca;">
                <ul style="padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Customer & Job Details -->
        <div class="grid-2">
            <div class="card-box">
                <h3>Customer Information</h3>
                <p><strong>{{ $quotation->customer?->company_name ?? 'Client' }}</strong></p>
                <p>{{ $quotation->customer?->contact_name ?? '' }}</p>
                <p>{{ $quotation->customer?->contact_email ?? '' }}</p>
                <p>{{ $quotation->customer?->contact_phone ?? '' }}</p>
            </div>
            <div class="card-box">
                <h3>Quotation Overview</h3>
                <p><strong>Job Type:</strong> {{ $quotation->jobType?->name ?? 'Roofing Works' }}</p>
                <p><strong>Valid Until:</strong> {{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') : '30 days from issue' }}</p>
                <p><strong>Reference Lead:</strong> #{{ $quotation->lead_id ?? 'N/A' }}</p>
            </div>
        </div>

        @if($quotation->cover_letter)
            <div class="card-box" style="margin-bottom: 2rem;">
                <h3>Scope of Work & Specifications</h3>
                <div style="font-size: 0.9375rem; color: var(--gray-700);">
                    {!! nl2br(e($quotation->cover_letter)) !!}
                </div>
            </div>
        @endif

        <!-- Items Table -->
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Quotation Breakdown</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $hasItems = false; @endphp
                @if($quotation->sections && $quotation->sections->isNotEmpty())
                    @foreach($quotation->sections as $section)
                        @if($section->section_name)
                            <tr style="background: #f1f5f9;">
                                <td colspan="4" style="font-weight: 600; color: var(--gray-700);">{{ $section->section_name }}</td>
                            </tr>
                        @endif
                        @foreach($section->items as $item)
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
                        <td colspan="3">Total Project Works</td>
                        <td class="text-right">£{{ number_format((float)($quotation->total_amount ?? 0), 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="total-box">
            <div class="total-card">
                <div class="total-row">
                    <span>Total Amount (GBP):</span>
                    <span>£{{ number_format((float)($quotation->total_amount ?? 0), 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Acceptance / Digital Signature Block -->
        @if($quotation->status === 'accepted')
            <div class="signature-section" style="border-color: #86efac; background: #f0fdf4;">
                <h3 style="color: #15803d; margin-bottom: 0.5rem;">Digitally Signed & Accepted</h3>
                <p>This quotation was formally approved and legally accepted by:</p>
                <p style="font-size: 1.125rem; font-weight: 700; margin-top: 0.5rem;">{{ $quotation->signer_name }}</p>
                @if($quotation->signer_signature)
                    <div style="margin: 0.75rem 0;">
                        <img src="{{ $quotation->signer_signature }}" alt="Digital Signature" style="max-height: 80px; border: 1px solid #bbf7d0; background:#fff; padding: 4px; border-radius: 4px;">
                    </div>
                @endif
                <div class="audit-info">
                    Signed at: {{ \Carbon\Carbon::parse($quotation->signed_at)->format('d M Y H:i:s') }} UTC &bull; Verified IP: {{ $quotation->signer_ip ?? 'Recorded' }}
                </div>
            </div>
        @elseif($quotation->status === 'rejected')
            <div class="signature-section" style="border-color: #fca5a5; background: #fef2f2;">
                <h3 style="color: #b91c1c; margin-bottom: 0.5rem;">Quotation Declined</h3>
                <p>This quotation has been declined by the client.</p>
                @if($quotation->rejection_reason)
                    <p style="margin-top: 0.5rem; font-style: italic;">Reason: {{ $quotation->rejection_reason }}</p>
                @endif
            </div>
        @else
            <div class="signature-section">
                <h3 style="margin-bottom: 0.5rem;">Digital Signature & Acceptance</h3>
                <p style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 1.25rem;">
                    Please type your full legal name, provide your digital signature below, and agree to the terms to accept this quotation.
                </p>

                <form id="acceptForm" method="POST" action="{{ request()->fullUrlWithQuery([]) }}">
                    @csrf
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="signer_signature" id="signer_signature">

                    <div class="form-group">
                        <label for="signer_name">Full Legal Name *</label>
                        <input type="text" name="signer_name" id="signer_name" class="form-control" placeholder="e.g. John Doe" value="{{ old('signer_name', $quotation->customer?->contact_name) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Draw Your Signature *</label>
                        <div class="canvas-container">
                            <canvas id="signatureCanvas"></canvas>
                        </div>
                        <button type="button" class="btn-clear" id="clearCanvas">Clear Signature</button>
                    </div>

                    <div class="form-group" style="display: flex; align-items: flex-start; gap: 0.5rem;">
                        <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" required style="margin-top: 0.25rem;">
                        <label for="terms_accepted" style="font-size: 0.875rem; font-weight: normal; margin-bottom: 0;">
                            I confirm that I am authorized to accept this quotation on behalf of the customer and agree to the scope, pricing, and terms outlined above.
                        </label>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
                        <button type="submit" class="btn btn-primary" id="btnAccept">Accept & Sign Quotation</button>
                    </div>
                </form>

                <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--gray-200);">

                <div>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectForm').style.display = 'block';">
                        Request Revision or Decline
                    </button>

                    <form id="rejectForm" method="POST" action="{{ request()->fullUrlWithQuery([]) }}" style="display: none; margin-top: 1rem;">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <div class="form-group">
                            <label for="rejection_reason">Reason for Revision / Decline (Optional)</label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" placeholder="Tell us what needs adjustments..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Submit Decline</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let drawing = false;

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#1e293b';
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDrawing(e) {
            drawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!drawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            e.preventDefault();
        }

        function stopDrawing() {
            drawing = false;
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDrawing);

        document.getElementById('clearCanvas')?.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        document.getElementById('acceptForm')?.addEventListener('submit', function(e) {
            const dataUrl = canvas.toDataURL('image/png');
            document.getElementById('signer_signature').value = dataUrl;
        });
    }
</script>

</body>
</html>
