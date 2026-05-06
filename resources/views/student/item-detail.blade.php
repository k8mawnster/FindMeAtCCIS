@extends('layouts.app')

@section('title', $item->name)

@section('navbar-right')
    <a href="{{ route('student.settings') }}" style="color: white; margin-right: 15px;">
        <i class="fas fa-cog" style="font-size: 1.3em;"></i>
    </a>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    @php
        $photos = $item->photos->pluck('image_url');
        if ($photos->isEmpty() && $item->image_url) {
            $photos = collect([$item->image_url]);
        }
        $tag = strtolower($item->item_status) === 'lost' ? 'tag-lost' : 'tag-found';
        $canClaim = $item->item_status === 'Found' && (int) $item->reported_by_user_id !== (int) session('user_id');
    @endphp

    <div class="admin-list-container">
        <a href="{{ route('student.search') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Search
        </a>

        <div class="item-detail-layout">
            <div>
                <div class="item-detail-main-image">
                    <img src="{{ $photos->first() ?? asset('img/no-image.png') }}" alt="{{ $item->name }}">
                </div>
                @if($photos->count() > 1)
                    <div class="photo-strip">
                        @foreach($photos as $photo)
                            <img src="{{ $photo }}" alt="{{ $item->name }} photo">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="item-detail-panel">
                <span class="status-tag {{ $tag }}">{{ $item->item_status }}</span>
                <h2>{{ $item->name }}</h2>
                <p>{{ $item->displayCategory() }}</p>
                <div class="detail-row"><i class="fas fa-calendar-alt"></i><span>{{ \Carbon\Carbon::parse($item->date_reported)->format('F j, Y') }}</span></div>
                <div class="detail-row"><i class="fas fa-map-marker-alt"></i><span>{{ $item->last_known_location }}</span></div>
                <div class="detail-desc">{{ $item->description }}</div>
                <div class="detail-row"><i class="fas fa-user"></i><span>{{ $item->reporter->full_name }} ({{ $item->reporter->course->course_code ?? 'N/A' }})</span></div>

                @if($canClaim)
                    <button class="btn-approve" onclick="showClaimModal({{ $item->item_id }}, @js($item->name))" style="margin-top: 18px;">
                        <i class="fas fa-hand-paper"></i> Claim Item
                    </button>
                @endif
            </div>
        </div>

        <h3 style="margin: 30px 0 15px;">Possible Matches</h3>
        @if($similarItems->isEmpty())
            <p style="color: #666;">No possible matches yet.</p>
        @else
            @foreach($similarItems as $match)
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            <img src="{{ $match->primaryImageUrl() ?? asset('img/no-image.png') }}" alt="{{ $match->name }}">
                        </div>
                        <div class="item-details">
                            <h4>{{ $match->name }}</h4>
                            <p>{{ $match->displayCategory() }}</p>
                            <p>{{ Str::limit($match->description, 70) }}</p>
                            <div class="item-meta">
                                <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($match->date_reported)->format('d-m-Y') }}</span>
                                <span><i class="fas fa-map-marker-alt"></i> {{ $match->last_known_location }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag {{ strtolower($match->item_status) === 'lost' ? 'tag-lost' : 'tag-found' }}">{{ $match->item_status }}</span>
                        <a class="btn-view-details" href="{{ route('student.items.show', $match->item_id) }}" style="text-decoration: none;">View Details</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div id="claimModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content" style="max-width: 450px; padding: 30px;">
            <i class="fas fa-times" style="position: absolute; top: 15px; right: 15px; cursor: pointer;" onclick="closeModal('claimModal')"></i>
            <h3>Claim Item</h3>
            <p id="claim-item-name" style="color: #666; margin-bottom: 15px;"></p>
            <div class="form-group"><label>Full Name</label><input type="text" id="claim_name" value="{{ session('user_name') }}" required></div>
            <div class="form-group"><label>Email</label><input type="email" id="claim_email" required></div>
            <div class="form-group"><label>Course & Section</label><input type="text" id="claim_course" placeholder="e.g. BSIT 3-A" required></div>
            <div class="form-group"><label>Proof Description</label><textarea id="claim_proof_desc" rows="3" required></textarea></div>
            <div class="form-group"><label>Proof Photo (optional)</label><input type="file" id="claim_file" accept="image/*"></div>
            <div class="modal-actions" style="justify-content: center; margin-top: 20px;">
                <button class="btn-modal-ok" onclick="submitClaim()">Submit Claim</button>
                <button class="btn-modal-exit" onclick="closeModal('claimModal')">Cancel</button>
            </div>
            <input type="hidden" id="claim_item_id">
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function showClaimModal(id, name) {
        document.getElementById('claim_item_id').value = id;
        document.getElementById('claim-item-name').textContent = `Claiming: "${name}"`;
        document.getElementById('claimModal').style.display = 'flex';
    }
    async function submitClaim() {
        const formData = new FormData();
        formData.append('item_id', document.getElementById('claim_item_id').value);
        formData.append('claim_name', document.getElementById('claim_name').value);
        formData.append('claim_email', document.getElementById('claim_email').value);
        formData.append('claim_course', document.getElementById('claim_course').value);
        formData.append('claim_proof_desc', document.getElementById('claim_proof_desc').value);
        const fileInput = document.getElementById('claim_file');
        if (fileInput.files[0]) formData.append('claim_file', fileInput.files[0]);
        const response = await fetch('{{ route('api.claim.submit') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            alert('Claim submitted successfully!');
            window.location.href = '{{ route('student.activity') }}';
        } else {
            alert(`Error: ${result.message}`);
        }
    }
</script>
@endsection
