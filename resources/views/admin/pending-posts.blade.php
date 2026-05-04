@extends('layouts.app')

@section('title', 'Pending Posts')

@section('navbar-right')
    <i class="fas fa-cog"></i>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    <div class="admin-list-container">
        <a href="{{ route('admin.dashboard') }}" class="back-link" style="margin-bottom: 10px; display: inline-flex;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="page-header-text">
            <h2>PENDING POSTS</h2>
            <p>Review and approve/reject pending item reports.</p>
        </div>

        @if($posts->isEmpty())
            <p style="text-align: center; margin-top: 20px; color: #666;">No pending posts to review.</p>
        @else
            @foreach($posts as $post)
                @php $tag = strtolower($post->item_status) == 'lost' ? 'tag-lost' : 'tag-found'; @endphp
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            <img src="{{ $post->primaryImageUrl() ?? asset('img/no-image.png') }}"
                                alt="{{ $post->name }}">
                        </div>
                        <div class="item-details">
                            <h4>{{ $post->name }}</h4>
                            <p>{{ $post->displayCategory() }}</p>
                            <p>{{ Str::limit($post->description, 60) }}</p>
                            <div class="item-meta">
                                <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($post->date_reported)->format('d-m-Y') }}</span>
                                <span><i class="fas fa-map-marker-alt"></i> {{ $post->last_known_location }}</span>
                            </div>
                            <p style="font-size: 0.9em; margin-top: 8px;">
                                Reported by: <strong>{{ $post->reporter->full_name }}</strong>
                                ({{ $post->reporter->course->course_code ?? 'N/A' }})
                            </p>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag {{ $tag }}">{{ $post->item_status }}</span>
                        <button class="btn-view-details" onclick="showDetailsModal({{ $post->item_id }})">View Details</button>
                        <button class="btn-approve" onclick="showApproveModal({{ $post->item_id }}, '{{ addslashes($post->name) }}')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn-reject" onclick="showRejectModal({{ $post->item_id }}, '{{ addslashes($post->name) }}')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Details Modal --}}
    <div id="postDetailsModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content modal-details">
            <div class="details-header">
                <h3>Post Details</h3>
                <i class="fas fa-times" style="cursor: pointer;" onclick="closeModal('postDetailsModal')"></i>
            </div>
            <div id="details-body"></div>
        </div>
    </div>

    {{-- Approve Modal --}}
    <div id="approveModal" class="modal-backdrop modal-confirmation" style="display:none;">
        <div class="modal-content" style="max-width: 350px;">
            <i class="fas fa-times" style="position: absolute; top: 15px; right: 15px; cursor: pointer;" onclick="closeModal('approveModal')"></i>
            <h3>Confirmation</h3>
            <p id="approve-text"></p>
            <div class="modal-actions" style="justify-content: center;">
                <button class="btn-modal-ok" onclick="processAction('approve')">OK</button>
                <button class="btn-modal-exit" onclick="closeModal('approveModal')">Exit</button>
            </div>
            <input type="hidden" id="approve-id">
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="modal-backdrop modal-confirmation" style="display:none;">
        <div class="modal-content" style="max-width: 400px;">
            <i class="fas fa-times" style="position: absolute; top: 15px; right: 15px; cursor: pointer;" onclick="closeModal('rejectModal')"></i>
            <h3>Confirmation</h3>
            <p id="reject-text"></p>
            <label style="font-weight: bold;">Rejection Reason</label>
            <input type="text" id="rejection-reason" placeholder="Enter reason here" required>
            <div class="modal-actions" style="justify-content: center;">
                <button class="btn-modal-ok" onclick="processAction('reject')">OK</button>
                <button class="btn-modal-exit" onclick="closeModal('rejectModal')">Exit</button>
            </div>
            <input type="hidden" id="reject-id">
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const postsData = @json($posts);

    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function showDetailsModal(id) {
        const post = postsData.find(p => p.item_id === id);
        if (!post) return;
        document.getElementById('details-body').innerHTML = `
            <div class="details-image" style="height: auto;">
                <img src="${post.photos?.[0]?.image_url ?? post.image_url ?? '{{ asset('img/no-image.png') }}'}" style="max-height: 250px;">
            </div>
            <div class="details-content" style="padding-top: 15px;">
                <h4>${post.name}</h4>
                <div class="detail-row"><i class="fas fa-folder"></i><span>Category: ${post.category?.name ?? 'N/A'}</span></div>
                <div class="detail-row"><i class="fas fa-calendar-alt"></i><span>Reported: ${post.date_reported}</span></div>
                <div class="detail-row"><i class="fas fa-map-marker-alt"></i><span>Location: ${post.last_known_location}</span></div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="font-weight: bold;">Description:</p>
                    <span>${post.description}</span>
                </div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="font-weight: bold;">Reported By:</p>
                    <span>${post.reporter?.full_name} (${post.reporter?.course?.course_code ?? 'N/A'})</span>
                    <div class="detail-row"><i class="fas fa-envelope"></i><span>${post.reporter?.email ?? 'N/A'}</span></div>
                    <div class="detail-row"><i class="fas fa-phone"></i><span>${post.reporter?.phone_number ?? 'N/A'}</span></div>
                </div>
            </div>`;
        document.getElementById('postDetailsModal').style.display = 'flex';
    }

    function showApproveModal(id, name) {
        document.getElementById('approve-id').value = id;
        document.getElementById('approve-text').innerHTML = `Approve post: <strong>${name}</strong>?`;
        document.getElementById('approveModal').style.display = 'flex';
    }

    function showRejectModal(id, name) {
        document.getElementById('reject-id').value = id;
        document.getElementById('reject-text').innerHTML = `Reject post: <strong>${name}</strong>?`;
        document.getElementById('rejection-reason').value = '';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    async function processAction(type) {
        const id     = document.getElementById(type === 'approve' ? 'approve-id' : 'reject-id').value;
        const reason = type === 'reject' ? document.getElementById('rejection-reason').value : '';

        if (type === 'reject' && !reason) { alert('Please enter a rejection reason.'); return; }

        const response = await fetch('{{ route('api.post.action') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id, action: type, reason })
        });
        const result = await response.json();
        if (result.success) { alert(`Post successfully ${type}d.`); window.location.reload(); }
        else alert(`Error: ${result.message}`);
    }
</script>
@endsection
