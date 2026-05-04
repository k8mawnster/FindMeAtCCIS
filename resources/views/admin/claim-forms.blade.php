@extends('layouts.app')

@section('title', 'Claim Forms')

@section('navbar-right')
    <i class="fas fa-cog"></i>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    <div class="admin-list-container">
        <a href="{{ route('admin.dashboard') }}" class="back-link" style="margin-bottom: 25px; display: inline-flex;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        <div class="page-header-text">
            <h2>CLAIM FORMS</h2>
            <p>Review and process pending and verified claim requests.</p>
        </div>

        {{-- PENDING CLAIMS --}}
        <h3 style="margin: 10px 0 15px;">Pending Claims</h3>
        @php $pendingClaims = $claims->where('claim_status', 'Pending'); @endphp
        @if($pendingClaims->isEmpty())
            <p style="color: #666; margin-bottom: 20px;">No pending claims.</p>
        @else
            @foreach($pendingClaims as $claim)
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            <img src="{{ $claim->item->primaryImageUrl() ?? asset('img/no-image.png') }}"
                                alt="{{ $claim->item->name }}">
                        </div>
                        <div class="item-details">
                            <h4>{{ $claim->item->name }}</h4>
                            <p>{{ $claim->item->displayCategory() }}</p>
                            <p>Claimed by: <strong>{{ $claim->claimer_full_name }}</strong></p>
                            <p style="font-size: 0.9em;">
                                {{ $claim->claimedBy->course->course_code ?? 'N/A' }} |
                                {{ \Carbon\Carbon::parse($claim->claim_date)->format('d-m-Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag tag-pending">Pending</span>
                        <button class="btn-view-details" onclick="showClaimDetails({{ $claim->claim_id }})">View Details</button>
                        <button class="btn-approve" onclick="showPickupModal({{ $claim->claim_id }})">
                            <i class="fas fa-calendar-check"></i> Set Pickup
                        </button>
                        <button class="btn-reject" onclick="processClaimAction({{ $claim->claim_id }}, 'reject')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- VERIFIED CLAIMS (Ready to Resolve) --}}
        <h3 style="margin: 30px 0 15px;">Verified Claims — Ready for Pickup</h3>
        @php $verifiedClaims = $claims->where('claim_status', 'Verified'); @endphp
        @if($verifiedClaims->isEmpty())
            <p style="color: #666;">No verified claims awaiting pickup.</p>
        @else
            @foreach($verifiedClaims as $claim)
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            <img src="{{ $claim->item->primaryImageUrl() ?? asset('img/no-image.png') }}"
                                alt="{{ $claim->item->name }}">
                        </div>
                        <div class="item-details">
                            <h4>{{ $claim->item->name }}</h4>
                            <p>{{ $claim->item->displayCategory() }}</p>
                            <p>Claimed by: <strong>{{ $claim->claimer_full_name }}</strong></p>
                            <p style="font-size: 0.9em;">
                                {{ $claim->claimedBy->course->course_code ?? 'N/A' }} |
                                {{ \Carbon\Carbon::parse($claim->claim_date)->format('d-m-Y') }}
                            </p>
                            @if($claim->pickup_schedule || $claim->pickup_location)
                                <div class="pickup-summary">
                                    <strong>Pickup:</strong>
                                    {{ $claim->pickup_schedule ? \Carbon\Carbon::parse($claim->pickup_schedule)->format('M j, Y g:i A') : 'Not scheduled' }}
                                    @if($claim->pickup_location)<br>{{ $claim->pickup_location }}@endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag tag-approved">Verified</span>
                        <button class="btn-view-details" onclick="showClaimDetails({{ $claim->claim_id }})">View Details</button>
                        <button class="btn-approve" onclick="processClaimAction({{ $claim->claim_id }}, 'resolve')"
                            style="background-color: var(--color-blue-dark);">
                            <i class="fas fa-trophy"></i> Mark Resolved
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div id="claimDetailsModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content modal-details">
            <div class="details-header">
                <h3>Claim Details</h3>
                <i class="fas fa-times" style="cursor: pointer;" onclick="closeModal('claimDetailsModal')"></i>
            </div>
            <div id="claim-details-body"></div>
        </div>
    </div>

    <div id="pickupModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content" style="max-width: 460px; padding: 30px;">
            <i class="fas fa-times" style="position: absolute; top: 15px; right: 15px; cursor: pointer;" onclick="closeModal('pickupModal')"></i>
            <h3>Set Pickup Details</h3>
            <p id="pickup-item-name" style="color: #666; margin-bottom: 15px;"></p>
            <div class="form-group">
                <label>Pickup Schedule</label>
                <input type="datetime-local" id="pickup_schedule" required>
            </div>
            <div class="form-group">
                <label>Pickup Location</label>
                <input type="text" id="pickup_location" value="CCIS Lost and Found Office" required>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea id="pickup_notes" rows="3" placeholder="Bring a valid ID or other instructions..."></textarea>
            </div>
            <div class="modal-actions" style="justify-content: center; margin-top: 20px;">
                <button class="btn-modal-ok" onclick="submitPickupDetails()">Verify Claim</button>
                <button class="btn-modal-exit" onclick="closeModal('pickupModal')">Cancel</button>
            </div>
            <input type="hidden" id="pickup_claim_id">
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const claimsData = @json($claims->values());
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function showPickupModal(id) {
        const claim = claimsData.find(c => c.claim_id === id);
        if (!claim) return;
        document.getElementById('pickup_claim_id').value = id;
        document.getElementById('pickup-item-name').textContent = `${claim.item?.name} for ${claim.claimer_full_name}`;
        document.getElementById('pickup_schedule').value = '';
        document.getElementById('pickup_location').value = 'CCIS Lost and Found Office';
        document.getElementById('pickup_notes').value = 'Bring a valid ID.';
        document.getElementById('pickupModal').style.display = 'flex';
    }

    function showClaimDetails(id) {
        const claim = claimsData.find(c => c.claim_id === id);
        if (!claim) return;
        document.getElementById('claim-details-body').innerHTML = `
            <div class="details-content" style="padding-top: 15px;">
                <h4>${claim.item?.name}</h4>
                <div class="detail-row"><i class="fas fa-user"></i><span>Claimant: ${claim.claimer_full_name}</span></div>
                <div class="detail-row"><i class="fas fa-envelope"></i><span>${claim.claimer_email ?? 'N/A'}</span></div>
                <div class="detail-row"><i class="fas fa-graduation-cap"></i><span>${claim.claimer_course_section ?? 'N/A'}</span></div>
                <div class="detail-row"><i class="fas fa-calendar-alt"></i><span>Claimed: ${claim.claim_date}</span></div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="font-weight: bold;">Proof Description:</p>
                    <span>${claim.proof_description}</span>
                </div>
                ${claim.proof_photo_url ? `<div style="margin-top: 10px;"><img src="/${claim.proof_photo_url}" style="max-width: 100%; border-radius: 8px;"></div>` : ''}
            </div>`;
        document.getElementById('claimDetailsModal').style.display = 'flex';
    }

    async function processClaimAction(id, action) {
        const labels = { set_pickup: 'verify', reject: 'reject', resolve: 'resolve' };
        if (!confirm(`Are you sure you want to ${labels[action]} this claim?`)) return;
        const response = await fetch('{{ route('api.claim.action') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id, action })
        });
        const result = await response.json();
        if (result.success) { alert(`Claim successfully ${labels[action]}d.`); window.location.reload(); }
        else alert(`Error: ${result.message}`);
    }

    async function submitPickupDetails() {
        const id = document.getElementById('pickup_claim_id').value;
        const pickup_schedule = document.getElementById('pickup_schedule').value;
        const pickup_location = document.getElementById('pickup_location').value;
        const pickup_notes = document.getElementById('pickup_notes').value;

        if (!pickup_schedule || !pickup_location) {
            alert('Please enter the pickup schedule and location.');
            return;
        }

        const response = await fetch('{{ route('api.claim.action') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id, action: 'set_pickup', pickup_schedule, pickup_location, pickup_notes })
        });
        const result = await response.json();
        if (result.success) {
            alert('Claim verified and pickup details sent.');
            window.location.reload();
        } else {
            alert(`Error: ${result.message}`);
        }
    }
</script>
@endsection         
