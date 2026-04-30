@extends('layouts.app')

@section('title', 'User Management')

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
        <div class="page-header-text" style="margin-bottom: 10px;">
            <h2>USER MANAGEMENT</h2>
            <p>{{ $show_archived ? 'Showing All Users' : 'Showing Active Users Only' }}</p>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div class="user-count-tag">{{ $users->count() }} Users</div>
            <div style="display: flex; gap: 15px; align-items: center;">
                @if($show_archived)
                    <a href="{{ route('admin.users') }}" class="btn-view-details" style="text-decoration: none; padding: 8px 15px;">
                        <i class="fas fa-eye-slash"></i> Hide Archived
                    </a>
                @else
                    <a href="{{ route('admin.users') }}?show=all" class="btn-view-details" style="text-decoration: none; padding: 8px 15px;">
                        <i class="fas fa-box-archive"></i> View Archived
                    </a>
                @endif
            </div>
        </div>

        @if($users->isEmpty())
            <p style="text-align: center; color: #666;">No users found.</p>
        @else
            @foreach($users as $user)
                @php $isArchived = $user->user_status === 'Archived'; @endphp
                <div class="user-card" style="{{ $isArchived ? 'opacity: 0.6; border: 1px dashed #cc0000;' : '' }}">
                    <div class="user-info">
                        <div class="user-icon-box">
                            <i class="fas fa-user-circle" style="{{ $isArchived ? 'color: var(--color-red);' : '' }}"></i>
                        </div>
                        <div class="user-details-text">
                            <h4 style="{{ $isArchived ? 'color: var(--color-red);' : '' }}">
                                {{ $user->full_name }} {{ $isArchived ? '(ARCHIVED)' : '' }}
                            </h4>
                            <p>Course: {{ $user->course->course_code ?? 'N/A' }}</p>
                            <p>Student ID: <strong>{{ $user->student_id }}</strong></p>
                        </div>
                    </div>
                    <div class="user-actions" style="flex-direction: row; gap: 10px;">
                        <button class="btn-view-details" style="width: 100px; padding: 5px 10px;"
                            onclick="showUserInfo({{ $user->user_id }})">View Info</button>
                        <button class="btn-delete-user"
                            onclick="archiveUser({{ $user->user_id }}, '{{ addslashes($user->full_name) }}', {{ $isArchived ? 'true' : 'false' }})"
                            {{ $isArchived ? 'disabled' : '' }}
                            style="{{ $isArchived ? 'opacity: 0.5; cursor: default;' : '' }}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div id="userInfoModal" class="modal-backdrop" style="display:none;">
        <div class="modal-content modal-details" style="max-width: 450px;">
            <div class="details-header">
                <h3>User Information</h3>
                <i class="fas fa-times" style="cursor: pointer;" onclick="closeModal('userInfoModal')"></i>
            </div>
            <div id="user-details-body"></div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const usersData = @json($users);
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    function showUserInfo(id) {
        const user = usersData.find(u => u.user_id === id);
        if (!user) return;
        const statusTag = user.user_status === 'Archived'
            ? '<span style="color: #cc0000; font-weight: bold;">(ARCHIVED)</span>'
            : '<span style="color: var(--color-green-button-dark); font-weight: bold;">(ACTIVE)</span>';
        document.getElementById('user-details-body').innerHTML = `
            <div class="details-content" style="padding-top: 15px;">
                <h4>${user.full_name} ${statusTag}</h4>
                <div class="detail-row"><i class="fas fa-id-card"></i><span>Student ID: <strong>${user.student_id}</strong></span></div>
                <h4 style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">Academic Details</h4>
                <div class="detail-row"><span>Course: ${user.course?.course_code ?? 'N/A'}</span></div>
                <div class="detail-row"><span>Year/Section: ${user.section_name ?? 'N/A'}</span></div>
                <h4 style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">Contact Information</h4>
                <div class="detail-row"><i class="fas fa-envelope"></i><span>${user.email ?? 'N/A'}</span></div>
                <div class="detail-row"><i class="fas fa-phone"></i><span>${user.phone_number ?? 'N/A'}</span></div>
            </div>`;
        document.getElementById('userInfoModal').style.display = 'flex';
    }

    async function archiveUser(id, name, isArchived) {
        if (isArchived) { alert(`${name} is already archived.`); return; }
        if (!confirm(`Archive (disable) user: ${name}?`)) return;
        const response = await fetch('{{ route('api.user.action') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id, action: 'delete' })
        });
        const result = await response.json();
        if (result.success) { alert('User archived successfully.'); window.location.reload(); }
        else alert(`Error: ${result.message}`);
    }
</script>
@endsection