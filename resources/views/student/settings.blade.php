@extends('layouts.app')

@section('title', 'Profile Settings')

@section('navbar-right')
    <i class="fas fa-cog" style="color: #ffd966; margin-right: 15px;"></i>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    <div class="settings-container">
        <a href="{{ route('student.dashboard') }}" class="back-link" style="margin-bottom: 25px; display: inline-flex;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="settings-header">
            <span class="settings-kicker">Profile</span>
            <h2>Account Settings</h2>
        </div>

        <div class="settings-card">
            <div id="profileDisplay" class="settings-profile">
                <div class="settings-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="settings-details">
                    <h3>{{ $user->full_name }}</h3>
                    <div class="settings-info-grid">
                        <span>Student ID</span>
                        <strong>{{ $user->student_id }}</strong>
                        <span>Course</span>
                        <strong>{{ $user->course->course_code ?? 'N/A' }} - {{ $user->section_name }}</strong>
                        <span>Email</span>
                        <strong>{{ $user->email }}</strong>
                        <span>Phone</span>
                        <strong>{{ $user->phone_number }}</strong>
                    </div>
                </div>
                <button type="button" class="btn-view-details settings-edit-btn" onclick="toggleEditView(true)">
                    <i class="fas fa-pen"></i> Edit
                </button>
            </div>

            <div id="profileEditForm" class="settings-edit-form" style="display: none;">
                <form id="editProfileForm" onsubmit="handleProfileUpdate(event)">
                    @csrf
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ $user->full_name }}" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone_number" value="{{ $user->phone_number }}" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label>Course</label>
                            <select name="course_id" required>
                                @foreach($courses as $course)
                                    <option value="{{ $course->course_id }}"
                                        {{ $user->course_id == $course->course_id ? 'selected' : '' }}>
                                        {{ $course->course_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Section</label>
                            <input type="text" name="section_name" value="{{ $user->section_name }}" required>
                        </div>
                    </div>
                    <div class="form-actions settings-form-actions">
                        <button type="submit" class="btn-edit-save">Save</button>
                        <button type="button" class="btn-cancel" onclick="toggleEditView(false)">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="settings-footer-actions">
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-logout">
                    Log out
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function toggleEditView(show) {
        document.getElementById('profileDisplay').style.display = show ? 'none' : 'flex';
        document.getElementById('profileEditForm').style.display = show ? 'flex' : 'none';
    }

    async function handleProfileUpdate(event) {
        event.preventDefault();
        const form = event.target;
        const data = Object.fromEntries(new FormData(form).entries());

        if (!/^\+?\d{10,15}$/.test(data.phone_number)) {
            alert('Invalid phone number format.');
            return;
        }

        const response = await fetch('{{ route('api.profile.update') }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result.success) {
            alert('Profile updated successfully!');
            window.location.reload();
        } else {
            alert(`Update failed: ${result.message}`);
        }
    }
</script>
@endsection
