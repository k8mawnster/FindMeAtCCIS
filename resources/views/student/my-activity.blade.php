@extends('layouts.app')

@section('title', 'My Posts & Claims')

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
            <h2>MY POSTS & CLAIMS</h2>
        </div>

        {{-- POSTS --}}
        <h3 style="margin: 20px 0 10px;">My Reports</h3>
        @if($posts->isEmpty())
            <p style="color: #666;">No reports yet.</p>
        @else
            @foreach($posts as $post)
                @php
                    $tag = strtolower($post->item_status) == 'lost' ? 'tag-lost' : 'tag-found';
                    $vtag = strtolower($post->verification_status);
                @endphp
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            @if($post->primaryImageUrl())
                                <img src="{{ $post->primaryImageUrl() }}" alt="{{ $post->name }}">
                            @else
                                <img src="{{ asset('img/no-image.png') }}" alt="No Image">
                            @endif
                        </div>
                        <div class="item-details">
                            <h4>{{ $post->name }}</h4>
                            <p>{{ $post->displayCategory() }}</p>
                            <p>{{ Str::limit($post->description, 60) }}</p>
                            <div class="item-meta">
                                <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($post->date_reported)->format('d-m-Y') }}</span>
                                <span><i class="fas fa-map-marker-alt"></i> {{ $post->last_known_location }}</span>
                            </div>
                            @if($post->rejection_reason)
                                <div class="rejection-reason-box">
                                    Rejection Reason: <strong>{{ $post->rejection_reason }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="admin-actions">
                        <span class="status-tag {{ $tag }}">{{ $post->item_status }}</span>
                        <span class="status-tag {{ $vtag == 'approved' ? 'tag-approved' : ($vtag == 'rejected' ? 'tag-rejected' : 'tag-pending') }}">
                            {{ $post->verification_status }}
                        </span>
                        @if($post->verification_status === 'Pending')
                            <a class="btn-view-details" href="{{ route('student.reports.edit', $post->item_id) }}" style="text-decoration: none;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn-reject"
                                onclick="cancelPost({{ $post->item_id }}, '{{ $post->name }}')">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        {{-- CLAIMS --}}
        <h3 style="margin: 30px 0 10px;">My Claims</h3>
        @if($claims->isEmpty())
            <p style="color: #666;">No claims yet.</p>
        @else
            @foreach($claims as $claim)
                <div class="admin-item-card">
                    <div class="admin-item-details">
                        <div class="item-image-box">
                            @if($claim->item->primaryImageUrl())
                                <img src="{{ $claim->item->primaryImageUrl() }}" alt="{{ $claim->item->name }}">
                            @else
                                <img src="{{ asset('img/no-image.png') }}" alt="No Image">
                            @endif
                        </div>
                        <div class="item-details">
                            <h4>{{ $claim->item->name }}</h4>
                            <p>{{ $claim->item->displayCategory() }}</p>
                            <p>Claimed on: {{ \Carbon\Carbon::parse($claim->claim_date)->format('d-m-Y') }}</p>
                            @if($claim->claim_status === 'Verified')
                                <div class="pickup-summary">
                                    <strong>Pickup:</strong>
                                    {{ $claim->pickup_schedule ? \Carbon\Carbon::parse($claim->pickup_schedule)->format('M j, Y g:i A') : 'Schedule pending' }}
                                    @if($claim->pickup_location)
                                        <br>{{ $claim->pickup_location }}
                                    @endif
                                    @if($claim->pickup_notes)
                                        <br>{{ $claim->pickup_notes }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="admin-actions">
                        @php $cs = strtolower($claim->claim_status); @endphp
                        <span class="status-tag {{ $cs == 'verified' || $cs == 'resolved' ? 'tag-approved' : ($cs == 'rejected' ? 'tag-rejected' : 'tag-pending') }}">
                            {{ $claim->claim_status }}
                        </span>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@section('scripts')
<script>
    async function cancelPost(id, name) {
        if (!confirm(`Cancel report for "${name}"?`)) return;
        const response = await fetch(`/api/posts/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });
        const result = await response.json();
        if (result.success) {
            alert('Post cancelled successfully.');
            window.location.reload();
        } else {
            alert(`Error: ${result.message}`);
        }
    }
</script>
@endsection
