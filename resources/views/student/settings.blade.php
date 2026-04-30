@extends('layouts.app')

@section('title', 'Profile Settings')

@section('navbar-right')
    <i class="fas fa-cog" style="color: #ffd966; margin-right: 15px;"></i>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    <div class="admin-list-container" style="max-width: 600px; margin-top: 20px;">
        <a href="{{ route('student.dashboard') }}" class="back-link" style="margin-bottom: 25px; display: inline-flex;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="profile-card">
            <div class="page-header-text" style="padding-left: 15px;">
                <span class="pending-count-tag" style="background-color: var(--color-green-button-light); color: var(--color-white); min-width: 80px;">
                    Profile
                </span>
            </div>

            <div id="profileDisplay" class="user-card" style="margin-top: 10px; box-shadow: none; justify-content: space-between;">
                <div class="user-info" style="align-items: flex-start; gap: 25px;">
                    <div class="user-icon-box" style="margin-top: 5px;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details-text">
                        <h4>{{ $user->full_name }}</h4>
                        <p style="margin-top: 10px;">Student ID: <strong>{{ $user->student_id }}</strong></p>
                        <p>Course: <strong>{{ $user->course->course_code ?? 'N/A' }}</strong>, Section: <strong>{{ $user->section_name }}</strong></p>
                        <p>Email: <strong>{{ $user->email }}</strong></p>
                        <p>Phone: <strong>{{ $user->phone_number }}</strong></p>
                        <p>Password: **********
                            <button type="button" class="btn-view-details"
                                style="width: 140px; height: 40px; margin-left: 10px;"
                                onclick="showPasswordModal()">Change Password</button>
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-view-details"
                    style="width: 80px; height: 30px; margin-top: 0;"
                    onclick="toggleEditView(true)">Edit</button>
            </div>

            <div id="profileEditForm" class="user-card" style="display: none; margin-top: 10px; box-shadow: none; flex-direction: column; width: 100%;">
                <form id="editProfileForm" onsubmit="handleProfileUpdate(event)" style="width: 100%; display: flex; flex-direction: column; gap: 15px;">
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
                    <div class="form-actions" style="justify-content: space-between; margin-top: 10px;">
                        <button type="submit" class="btn-edit-save">Save</button>
                        <button type="button" class="btn-cancel" style="width: 120px;" onclick="toggleEditView(false)">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div style="text-align: right; margin-top: 30px;">
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-logout" style="width: 120px; background-color: var(--color-red); color: white;">
                    Log out
                </button>
            </form>
        </div>
    </div>

    {{-- Password Modal --}}
    <div id="passwordModal" class="modal-backdrop modal-confirmation" style="display: none;">
        <div class="modal-content" style="max-width: 400px; padding: 30px;">
            <i class="fas fa-times" style="position: absolute; top: 15px; right: 15px; cursor: pointer;" onclick="closeModal('passwordModal')"></i>
            <h3>Change Password</h3>
            <form id="editPasswordForm" onsubmit="handlePasswordUpdate(event)">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="edit-pass" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label>Re-enter New Password</label>
                    <input type="password" id="edit-repass" placeholder="Confirm new password" required>
                </div>
                <div class="modal-actions" style="justify-content: center; margin-top: 20px;">
                    <button type="submit" class="btn-edit-save">Save</button>
                    <button type="button" class="btn-cancel" onclick="closeModal('passwordModal')">Cancel</button>
                </div>
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

    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function showPasswordModal() { document.getElementById('passwordModal').style.display = 'flex'; }

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

   async function handlePasswordUpdate(event) {
    event.preventDefault();
    const newPass = document.getElementById('edit-pass').value;
    const rePass  = document.getElementById('edit-repass').value;

    if (newPass !== rePass) { alert('Passwords do not match.'); return; }
    if (newPass.length < 6) { alert('Password must be at least 6 characters.'); return; }

    closeModal('passwordModal');

    const response = await fetch('{{ route('student.settings.password') }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ password: newPass })
    });
    const result = await response.json();
    if (result.success) {
        alert('Password updated! Please log in again.');
        window.location.href = result.redirect;
    } else {
        alert(`Failed: ${result.message}`);
    }
}
</script>
@endsection