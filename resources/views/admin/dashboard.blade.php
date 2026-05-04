@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('navbar-right')
    <i class="fas fa-cog" style="cursor: pointer;" onclick="showAdminProfileModal()"></i>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    <main class="dashboard-container">
        <div class="dashboard-header">
            <h2>Dashboard</h2>
        </div>

        <div class="dashboard-grid">

            <a href="{{ route('admin.pending') }}" class="card-link">
                <div class="card card-yellow">
                    <div class="icon-box"><i class="fas fa-clock"></i></div>
                    <div class="text-box">Pending Posts ({{ $counts['pending_posts'] }})</div>
                </div>
            </a>

            <a href="{{ route('admin.approved') }}" class="card-link">
                <div class="card card-green">
                    <div class="icon-box"><i class="fas fa-check"></i></div>
                    <div class="text-box">Approved Posts ({{ $counts['approved_posts'] }})</div>
                </div>
            </a>

            <a href="{{ route('admin.rejected') }}" class="card-link">
                <div class="card card-red">
                    <div class="icon-box"><i class="fas fa-times"></i></div>
                    <div class="text-box">Rejected Posts ({{ $counts['rejected_posts'] }})</div>
                </div>
            </a>

            <a href="{{ route('admin.claims') }}" class="card-link">
                <div class="card card-blue-dark">
                    <div class="icon-box"><i class="fas fa-file-alt"></i></div>
                    <div class="text-box">Claim Forms ({{ $counts['claim_forms'] }})</div>
                </div>
            </a>

            <a href="{{ route('admin.tobeclaimed') }}" class="card-link">
                <div class="card card-yellow-dark">
                    <div class="icon-box"><i class="fas fa-box-open"></i></div>
                    <div class="text-box">To be Claimed ({{ $counts['to_be_claimed'] }})</div>
                </div>
            </a>

            <a href="{{ route('admin.resolved') }}" class="card-link">
                <div class="card card-blue-light">
                    <div class="icon-box"><i class="fas fa-trophy"></i></div>
                    <div class="text-box">Resolved Cases ({{ $counts['resolved_cases'] }})</div>
                </div>
            </a>

            <a href="{{ route('admin.users') }}" class="card-link">
                <div class="card card-grey">
                    <div class="icon-box"><i class="fas fa-users"></i></div>
                    <div class="text-box">User Management ({{ $counts['user_management'] }})</div>
                </div>
            </a>

        </div>
    </main>

    <div id="adminProfileModal" class="modal-backdrop" style="display: none;">
        <div class="modal-content modal-details" style="max-width: 400px; padding: 25px;">
            <div class="details-header">
                <h3>Admin Profile</h3>
                <i class="fas fa-times" style="cursor: pointer;" onclick="closeModal('adminProfileModal')"></i>
            </div>
            <div class="user-card" style="margin-top: 15px; box-shadow: none; border: none; padding: 0;">
                <div class="user-info" style="align-items: flex-start; gap: 20px;">
                    <div class="user-icon-box">
                        <i class="fas fa-user-shield" style="font-size: 2.5em;"></i>
                    </div>
                    <div class="user-details-text">
                        <h4>{{ session('user_name') }}</h4>
                        <p style="margin-top: 10px;">Role: <strong>Administrator</strong></p>
                    </div>
                </div>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-logout"
                        style="width: 120px; font-weight: bold; background-color: var(--color-red); color: white;">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function showAdminProfileModal() { document.getElementById('adminProfileModal').style.display = 'flex'; }
</script>
@endsection
