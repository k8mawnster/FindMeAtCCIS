@extends('layouts.app')

@section('title', 'Search Items')

@section('navbar-right')
    <a href="{{ route('student.settings') }}" style="color: white; margin-right: 15px;">
        <i class="fas fa-cog" style="font-size: 1.3em;"></i>
    </a>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    <div class="admin-list-container">
        <a href="{{ route('student.dashboard') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="page-header-text">
            <h2>SEARCH & ITEM LISTS</h2>
            <p>Browse all approved lost and found items.</p>
        </div>

        <form method="GET" action="{{ route('student.search') }}" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search item name..." value="{{ request('search') }}"
                style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc; flex: 1;">
            <select name="category" style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->category_id }}" {{ request('category') == $cat->category_id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" style="padding: 8px 15px; border-radius: 20px; border: 1px solid #ccc;">
                <option value="">All Status</option>
                <option value="Lost" {{ request('status') == 'Lost' ? 'selected' : '' }}>Lost</option>
                <option value="Found" {{ request('status') == 'Found' ? 'selected' : '' }}>Found</option>
            </select>
            <button type="submit" class="btn-view-details" style="padding: 8px 15px;">
                <i class="fas fa-search"></i> Search
            </button>
        </form>

        @if($items->isEmpty())
            <p style="text-align: center; color: #666; margin-top: 20px;">No items found.</p>
        @else
            @foreach($items as $item)
                @php $tag = strtolower($item->item_status) == 'lost' ? 'tag-lost' : 'tag-found'; @endphp
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            @if($item->primaryImageUrl())
                                <img src="{{ $item->primaryImageUrl() }}" alt="{{ $item->name }}">
                            @else
                                <img src="{{ asset('img/no-image.png') }}" alt="No Image">
                            @endif
                        </div>
                        <div class="item-details">
                            <h4>{{ $item->name }}</h4>
                            <p>{{ $item->displayCategory() }}</p>
                            <p>{{ Str::limit($item->description, 60) }}</p>
                            <div class="item-meta">
                                <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($item->date_reported)->format('d-m-Y') }}</span>
                                <span><i class="fas fa-map-marker-alt"></i> {{ $item->last_known_location }}</span>
                            </div>
                            <p style="font-size: 0.9em; margin-top: 8px;">
                                Reported by: <strong>{{ $item->reporter->full_name }}</strong>
                                ({{ $item->reporter->course->course_code ?? 'N/A' }})
                            </p>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag {{ $tag }}">{{ $item->item_status }}</span>
                        <a class="btn-view-details" href="{{ route('student.items.show', $item->item_id) }}" style="text-decoration: none;">View Details</a>
                        @if($item->item_status === 'Found')
                            <button class="btn-approve" onclick="showClaimModal({{ $item->item_id }}, '{{ addslashes($item->name) }}')">
                                <i class="fas fa-hand-paper"></i> Claim
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Claim Modal --}}
    <div id="claimModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content" style="max-width: 450px; padding: 30px;">
            <i class="fas fa-times" style="position: absolute; top: 15px; right: 15px; cursor: pointer;" onclick="closeModal('claimModal')"></i>
            <h3>Claim Item</h3>
            <p id="claim-item-name" style="color: #666; margin-bottom: 15px;"></p>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="claim_name" value="{{ session('user_name') }}" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="claim_email" required>
            </div>
            <div class="form-group">
                <label>Course & Section</label>
                <input type="text" id="claim_course" placeholder="e.g. BSIT 3-A" required>
            </div>
            <div class="form-group">
                <label>Proof Description</label>
                <textarea id="claim_proof_desc" rows="3" placeholder="Describe proof of ownership..." required></textarea>
            </div>
            <div class="form-group">
                <label>Proof Photo (optional)</label>
                <input type="file" id="claim_file" accept="image/*">
            </div>

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
        formData.append('item_id',          document.getElementById('claim_item_id').value);
        formData.append('claim_name',       document.getElementById('claim_name').value);
        formData.append('claim_email',      document.getElementById('claim_email').value);
        formData.append('claim_course',     document.getElementById('claim_course').value);
        formData.append('claim_proof_desc', document.getElementById('claim_proof_desc').value);

        const fileInput = document.getElementById('claim_file');
        if (fileInput.files[0]) {
            formData.append('claim_file', fileInput.files[0]);
        }

        const response = await fetch('{{ route('api.claim.submit') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            alert('Claim submitted successfully!');
            closeModal('claimModal');
            window.location.reload();
        } else {
            alert(`Error: ${result.message}`);
        }
    }
</script>
@endsection
