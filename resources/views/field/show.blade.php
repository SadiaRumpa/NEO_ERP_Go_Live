@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('field.jobs.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bx bx-arrow-back me-1"></i> Field Hub
            </a>
            <h4 class="fw-bold mb-0">{{ $job->job_name ?: ('Job #' . $job->id) }}</h4>
            <span class="text-muted small">{{ $job->customer->company_name ?? 'Client' }}</span>
        </div>
        <div>
            <span class="badge bg-label-info fs-6">{{ strtoupper($job->job_status) }}</span>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Step 1: Site Arrival -->
    <div class="card mb-3 shadow-sm border-start border-4 {{ $currentVisit ? 'border-success' : 'border-warning' }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-1"><i class="bx bx-map-pin me-1 text-primary"></i> 1. Site Arrival & Geo Check-In</h6>
                    @if($currentVisit && $currentVisit->arrived_at)
                        <p class="mb-0 text-success small">
                            <i class="bx bx-check-circle"></i> Arrived on site at <strong>{{ $currentVisit->arrived_at->format('H:i:s (d M Y)') }}</strong>
                            @if($currentVisit->latitude)
                                <br><span class="text-muted">GPS: {{ $currentVisit->latitude }}, {{ $currentVisit->longitude }}</span>
                            @endif
                        </p>
                    @else
                        <p class="mb-0 text-muted small">Log your arrival at the client site to clock in and initialize the safety checklist.</p>
                    @endif
                </div>
                <div>
                    @if(!$currentVisit || !$currentVisit->arrived_at)
                    <form method="POST" action="{{ route('field.jobs.arrive', $job->id) }}" id="arriveForm">
                        @csrf
                        <input type="hidden" name="latitude" id="arriveLat">
                        <input type="hidden" name="longitude" id="arriveLng">
                        <button type="button" class="btn btn-warning fw-bold" onclick="recordArrival()">
                            <i class="bx bx-log-in-circle me-1"></i> Clock In / Arrived
                        </button>
                    </form>
                    @else
                    <span class="badge bg-label-success px-3 py-2"><i class="bx bx-check me-1"></i> Arrived</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Site RAMS & Safety Checklist -->
    <div class="card mb-3 shadow-sm border-start border-4 {{ ($currentVisit && $currentVisit->safetyChecklist) ? 'border-success' : 'border-primary' }}">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="bx bx-shield-quarter me-1 text-primary"></i> 2. Site Safety / RAMS Verification</h6>
            @if($currentVisit && $currentVisit->safetyChecklist)
                <span class="badge bg-label-success"><i class="bx bx-check"></i> Passed</span>
            @endif
        </div>
        <div class="card-body pt-3">
            @if(!$currentVisit)
                <p class="text-muted small mb-0">Please clock in / arrive on site first to unlock the safety checklist.</p>
            @elseif($currentVisit->safetyChecklist)
                <div class="alert alert-label-success mb-2 py-2">
                    <p class="mb-0 small">
                        <strong>RAMS Signed off by:</strong> {{ $currentVisit->safetyChecklist->signed_by_worker }} &bull;
                        {{ $currentVisit->safetyChecklist->completed_at?->format('d/m/Y H:i') }}
                    </p>
                    @if($currentVisit->safetyChecklist->risk_assessment_notes)
                        <p class="mb-0 small text-muted mt-1">Notes: {{ $currentVisit->safetyChecklist->risk_assessment_notes }}</p>
                    @endif
                </div>
            @else
                <form method="POST" action="{{ route('field.jobs.safety', $job->id) }}">
                    @csrf
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="working_at_height_passed" value="1" id="checkHeight" checked required>
                                <label class="form-check-label small fw-bold" for="checkHeight">Working at Height Inspection Verified</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ppe_compliant" value="1" id="checkPPE" checked required>
                                <label class="form-check-label small fw-bold" for="checkPPE">Full PPE In Use (Hard Hat, Harness, Boots)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="edge_protection_verified" value="1" id="checkEdge" checked required>
                                <label class="form-check-label small fw-bold" for="checkEdge">Scaffolding / Edge Protection Verified</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="weather_acceptable" value="1" id="checkWeather" checked required>
                                <label class="form-check-label small fw-bold" for="checkWeather">Wind & Weather Conditions Acceptable</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Risk Assessment Notes (Optional)</label>
                        <input type="text" name="risk_assessment_notes" class="form-control form-control-sm" placeholder="Site observations, access constraints, hazards...">
                    </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-sm-8">
                            <label class="form-label small required">Lead Engineer Sign-off Name</label>
                            <input type="text" name="signed_by_worker" class="form-control form-control-sm" value="{{ auth()->user()->name }}" required>
                        </div>
                        <div class="col-sm-4 text-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bx bx-check-shield me-1"></i> Sign Off RAMS
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Step 3: Site Photo Documentation -->
    <div class="card mb-3 shadow-sm border-start border-4 border-info">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="bx bx-camera me-1 text-primary"></i> 3. On-Site Photo Documentation</h6>
            <span class="badge bg-label-info">{{ count($photos) }} Photos</span>
        </div>
        <div class="card-body pt-3">
            <form method="POST" action="{{ route('field.jobs.photo', $job->id) }}" enctype="multipart/form-data" class="row g-2 mb-3" id="photoForm">
                @csrf
                <input type="hidden" name="latitude" id="photoLat">
                <input type="hidden" name="longitude" id="photoLng">
                <div class="col-md-4">
                    <label class="form-label small required">Select / Capture Photo</label>
                    <input type="file" name="photo" class="form-control form-control-sm" accept="image/*" capture="environment" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small required">Stage</label>
                    <select name="stage" class="form-select form-select-sm" required>
                        <option value="before">Before Works</option>
                        <option value="during" selected>During Works</option>
                        <option value="after">After Completion</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Caption</label>
                    <input type="text" name="caption" class="form-control form-control-sm" placeholder="e.g. Parapet gutter joint repair">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-info w-100" onclick="uploadWithGeo()">
                        <i class="bx bx-upload me-1"></i> Upload
                    </button>
                </div>
            </form>

            <div class="row g-2">
                @forelse($photos as $p)
                <div class="col-6 col-md-3">
                    <div class="card border h-100">
                        <img src="{{ asset('storage/' . $p->photo_path) }}" class="card-img-top" style="height: 120px; object-fit: cover;" alt="Site photo">
                        <div class="card-body p-2">
                            <span class="badge bg-label-secondary small mb-1">{{ strtoupper($p->stage) }}</span>
                            <p class="card-text small text-truncate mb-0">{{ $p->caption ?? 'Site Photo' }}</p>
                            <small class="text-muted d-block">{{ $p->created_at->format('d/m H:i') }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted small text-center my-2">No photos uploaded yet. Take Before, During, and After photos of the roof work.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Step 4: Customer Electronic Sign-Off & Visit Completion -->
    @if($currentVisit && $currentVisit->status === 'in_progress')
    <div class="card mb-4 shadow-sm border-start border-4 border-success">
        <div class="card-header border-bottom py-2">
            <h6 class="mb-0 fw-bold"><i class="bx bx-pen me-1 text-primary"></i> 4. Client Handover & E-Signature</h6>
        </div>
        <div class="card-body pt-3">
            <form method="POST" action="{{ route('field.jobs.complete', $job->id) }}" id="completeVisitForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label small required">Client / Site Contact Name</label>
                    <input type="text" name="customer_signer_name" class="form-control form-control-sm" value="{{ $job->customer->contact_name ?? '' }}" required placeholder="e.g. David Evans">
                </div>
                <div class="mb-3">
                    <label class="form-label small">Handover Notes / Client Feedback</label>
                    <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Any comments or client satisfaction notes..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small required">Client Electronic Signature (Draw Below):</label>
                    <div class="border rounded bg-light p-1">
                        <canvas id="fieldSigCanvas" width="500" height="150" style="width: 100%; height: 150px; background-color: #fff; touch-action: none; border-radius: 4px;"></canvas>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">Sign with finger or stylus on mobile.</small>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearSigBtn">Clear Signature</button>
                    </div>
                    <input type="hidden" name="customer_signature" id="customerSigInput" required>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="mark_job_completed" value="1" id="markJobComp" checked>
                    <label class="form-check-label small fw-bold" for="markJobComp">
                        Mark Job as 100% Completed (Moves to Ready for Invoicing)
                    </label>
                </div>

                <button type="button" class="btn btn-success fw-bold w-100 py-2" onclick="submitCompleteVisit()">
                    <i class="bx bx-check-double me-1"></i> Complete Visit & Submit Sign-Off
                </button>
            </form>
        </div>
    </div>
    @elseif($currentVisit && $currentVisit->status === 'completed')
    <div class="card mb-4 bg-label-success shadow-sm">
        <div class="card-body">
            <h6 class="fw-bold mb-1 text-success"><i class="bx bx-check-circle"></i> Site Visit Completed & Signed Off</h6>
            <p class="mb-0 small">
                Client Signature recorded for <strong>{{ $currentVisit->customer_signer_name }}</strong> on {{ $currentVisit->signed_at?->format('d/m/Y H:i') }}.
            </p>
        </div>
    </div>
    @endif
</div>

<script>
    function recordArrival() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    document.getElementById('arriveLat').value = pos.coords.latitude;
                    document.getElementById('arriveLng').value = pos.coords.longitude;
                    document.getElementById('arriveForm').submit();
                },
                function() {
                    document.getElementById('arriveForm').submit();
                },
                { timeout: 5000 }
            );
        } else {
            document.getElementById('arriveForm').submit();
        }
    }

    function uploadWithGeo() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    document.getElementById('photoLat').value = pos.coords.latitude;
                    document.getElementById('photoLng').value = pos.coords.longitude;
                    document.getElementById('photoForm').submit();
                },
                function() {
                    document.getElementById('photoForm').submit();
                },
                { timeout: 5000 }
            );
        } else {
            document.getElementById('photoForm').submit();
        }
    }

    // Signature Canvas logic
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('fieldSigCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';

        let drawing = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDraw(e) {
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

        function stopDraw() {
            drawing = false;
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDraw);

        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDraw);

        document.getElementById('clearSigBtn').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('customerSigInput').value = '';
        });
    });

    function submitCompleteVisit() {
        const canvas = document.getElementById('fieldSigCanvas');
        const input = document.getElementById('customerSigInput');
        if (canvas) {
            input.value = canvas.toDataURL('image/png');
        }
        if (!input.value || input.value.length < 100) {
            alert('Please provide the client signature before completing the visit.');
            return;
        }
        document.getElementById('completeVisitForm').submit();
    }
</script>
@endsection
