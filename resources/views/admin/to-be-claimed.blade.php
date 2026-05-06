@extends('layouts.app')

@section('title', 'To Be Claimed')

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
            <h2>ITEMS TO BE CLAIMED</h2>
            <p>Approved found items available for pickup.</p>
        </div>

        <div class="resolved-count-tag" style="background-color: var(--color-blue-dark);">
            {{ $posts->count() }} Available Items
        </div>

        @if($posts->isEmpty())
            <p style="text-align: center; color: #666; margin-top: 20px;">No items available to be claimed.</p>
        @else
            @foreach($posts as $post)
                <div class="admin-item-card">
                    @php $verifiedClaim = $post->claims->firstWhere('claim_status', 'Verified'); @endphp
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
                            @if($verifiedClaim)
                                <div class="pickup-summary">
                                    <strong>Pickup:</strong>
                                    {{ $verifiedClaim->pickup_schedule ? \Carbon\Carbon::parse($verifiedClaim->pickup_schedule)->format('M j, Y g:i A') : 'Schedule pending' }}
                                    @if($verifiedClaim->pickup_location)<br>{{ $verifiedClaim->pickup_location }}@endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag tag-found">Found</span>
                        <button class="btn-view-details" onclick="showDetailsModal({{ $post->item_id }})">View Details</button>
                        <button class="btn-approve" onclick="resolveItem({{ $post->item_id }}, @js($post->name))">
                            <i class="fas fa-trophy"></i> Item Claimed
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div id="postDetailsModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content modal-details">
            <div class="details-header">
                <h3>Item Details</h3>
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
                <div class="detail-row"><i class="fas fa-map-marker-alt"></i><span>Location: ${escapeHtml(post.last_known_location)}</span></div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="font-weight: bold;">Description:</p><span>${escapeHtml(post.description)}</span>
                </div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="font-weight: bold;">Reported By:</p>
                    <span>${escapeHtml(post.reporter?.full_name)} (${escapeHtml(post.reporter?.course?.course_code ?? 'N/A')})</span>
                    <div class="detail-row"><i class="fas fa-envelope"></i><span>${escapeHtml(post.reporter?.email ?? 'N/A')}</span></div>
                    <div class="detail-row"><i class="fas fa-phone"></i><span>${escapeHtml(post.reporter?.phone_number ?? 'N/A')}</span></div>
                </div>
            </div>`;
        document.getElementById('postDetailsModal').style.display = 'flex';
    }

    async function resolveItem(itemId, name) {
    if (!confirm(`Mark "${name}" as claimed and resolve?`)) return;

    // Find the verified claim for this item
    const response = await fetch('{{ route('api.claim.action') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ item_id: itemId, action: 'resolve_by_item' })
    });

    const result = await response.json();
    if (result.success) {
        alert('Item marked as claimed and resolved!');
        window.location.reload();
    } else {
        alert(`Error: ${result.message}`);
    }
}
</script>
@endsection
