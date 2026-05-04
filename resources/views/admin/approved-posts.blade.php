@extends('layouts.app')

@section('title', 'Approved Posts')

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
            <h2>APPROVED POSTS</h2>
            <p>View all approved item reports.</p>
        </div>

        @if($posts->isEmpty())
            <p style="text-align: center; color: #666;">No approved posts.</p>
        @else
            @foreach($posts as $post)
                @php
                    $tag = strtolower($post->item_status) == 'lost' ? 'tag-lost' : 'tag-found';
                @endphp
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
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag {{ $tag }}">{{ $post->item_status }}</span>
                        <button class="btn-view-details" onclick="showDetailsModal({{ $post->item_id }})">View Details</button>
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
                <h4>${post.name}</h4>
                <div class="detail-row"><i class="fas fa-folder"></i><span>Category: ${post.category?.name ?? 'N/A'}</span></div>
                <div class="detail-row"><i class="fas fa-calendar-alt"></i><span>Reported: ${post.date_reported}</span></div>
                <div class="detail-row"><i class="fas fa-map-marker-alt"></i><span>Location: ${post.last_known_location}</span></div>
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p style="font-weight: bold;">Description:</p><span>${post.description}</span>
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
</script>
@endsection
