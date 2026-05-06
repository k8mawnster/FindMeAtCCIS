@extends('layouts.app')

@section('title', 'Rejected Posts')

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
            <h2>REJECTED POSTS</h2>
            <p>View rejected item reports and their reasons.</p>
        </div>

        @if($posts->isEmpty())
            <p style="text-align: center; color: #666;">No rejected posts.</p>
        @else
            @foreach($posts as $post)
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            <img src="{{ $post->primaryImageUrl() ?? asset('img/no-image.png') }}" alt="{{ $post->name }}">
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
                            <div class="rejection-reason-box">
                                Rejection Reason: <strong>{{ $post->rejection_reason }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag tag-rejected">Rejected</span>
                        <button class="btn-view-details" onclick="showDetailsModal({{ $post->item_id }})">View Details</button>
                        <button class="btn-restore" onclick="restorePost({{ $post->item_id }}, @js($post->name))">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div id="postDetailsModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content modal-details">
            <div class="details-header">
                <h3>Post Details</h3>
                <i class="fas fa-times" style="cursor: pointer;" onclick="closeModal('postDetailsModal')"></i>
            </div>
            <div id="details-body"></div>
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
                <h4>${escapeHtml(post.name)}</h4>
                <div class="detail-row"><i class="fas fa-folder"></i><span>Category: ${escapeHtml(post.category?.name ?? 'N/A')}</span></div>
                <div class="detail-row"><i class="fas fa-calendar-alt"></i><span>Reported: ${escapeHtml(post.date_reported)}</span></div>
                <div style="margin-top: 10px;"><p style="font-weight:bold; color: red;">Rejection Reason: ${escapeHtml(post.rejection_reason)}</p></div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="font-weight: bold;">Reported By:</p>
                    <span>${escapeHtml(post.reporter?.full_name)} (${escapeHtml(post.reporter?.course?.course_code ?? 'N/A')})</span>
                    <div class="detail-row"><i class="fas fa-envelope"></i><span>${escapeHtml(post.reporter?.email ?? 'N/A')}</span></div>
                    <div class="detail-row"><i class="fas fa-phone"></i><span>${escapeHtml(post.reporter?.phone_number ?? 'N/A')}</span></div>
                </div>
            </div>`;
        document.getElementById('postDetailsModal').style.display = 'flex';
    }

    async function restorePost(id, name) {
        if (!confirm(`Restore post "${name}" back to Pending?`)) return;
        const response = await fetch('{{ route('api.post.action') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id, action: 'restore' })
        });
        const result = await response.json();
        if (result.success) { alert('Post restored.'); window.location.reload(); }
        else alert(`Error: ${result.message}`);
    }
</script>
@endsection
