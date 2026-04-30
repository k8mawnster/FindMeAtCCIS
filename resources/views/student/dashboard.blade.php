@extends('layouts.app')

@section('title', 'Student Menu')

@section('navbar-right')
    <div id="notificationBellContainer" class="notification-bell-container" onclick="toggleNotifications()">
        <i class="fas fa-bell"></i>
        <div id="notificationDropdown" class="notification-dropdown">
            <div class="notification-header">Notifications</div>
            <div id="notification-list">
                <div class="notification-item" style="text-align: center;">Loading...</div>
            </div>
        </div>
    </div>
    <a href="{{ route('student.settings') }}" style="color: white; margin-right: 15px;">
        <i class="fas fa-cog" style="font-size: 1.3em;"></i>
    </a>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }} ({{ $user->course->course_code ?? 'N/A' }})</span>
@endsection

@section('content')
    <div class="menu-container">

        <a href="{{ route('student.report.lost') }}" class="menu-card card-lost">
            <div class="icon-text">
                <i class="fas fa-exclamation-triangle icon-lost"></i>
                <div>
                    <h3>REPORT LOST ITEM</h3>
                    <p>Report a lost item</p>
                </div>
            </div>
        </a>

        <a href="{{ route('student.report.found') }}" class="menu-card card-found">
            <div class="icon-text">
                <i class="fas fa-search-plus icon-found"></i>
                <div>
                    <h3>REPORT FOUND ITEM</h3>
                    <p>Report a found item</p>
                </div>
            </div>
        </a>

        <a href="{{ route('student.search') }}" class="menu-card card-search">
            <div class="icon-text">
                <i class="fas fa-eye icon-search"></i>
                <div>
                    <h3>SEARCH & ITEM LISTS</h3>
                    <p>Browse all items</p>
                </div>
            </div>
        </a>

        <a href="{{ route('student.activity') }}" class="menu-card card-claims">
            <div class="icon-text">
                <i class="fas fa-file-alt icon-claims"></i>
                <div>
                    <h3>MY POSTS & CLAIMS</h3>
                    <p>View your activity</p>
                </div>
            </div>
        </a>

    </div>
@endsection

@section('scripts')
<script>
    let notificationsLoaded = false;

    function renderNotifications(data) {
        const list = document.getElementById('notification-list');
        list.innerHTML = '';

        if (data.length === 0) {
            list.innerHTML = '<div class="notification-item" style="text-align: center;">No new notifications.</div>';
            return;
        }

        data.forEach(notif => {
            const status = notif.status.toLowerCase();
            const isRejected = status === 'rejected';
            const isApproved = status === 'approved' || status === 'verified' || status === 'resolved';

            let title = '';
            let description = '';
            let statusColor = isRejected ? 'var(--color-red)' : isApproved ? 'var(--color-green-button-dark)' : 'var(--color-text-dark)';
            let dateDisplay = '';

            if (notif.type === 'POST_STATUS') {
                title = `Post ${status === 'approved' ? 'Approved' : 'Rejected'}`;
                description = `Report for '${notif.item_name}' has been ${status}.`;
            } else {
                title = `Claim ${status === 'resolved' ? 'Resolved' : status === 'verified' ? 'Approved' : 'Rejected'}`;
                description = `Claim on '${notif.item_name}' has been processed as ${status}.`;
            }

            if (notif.date) {
                const d = new Date(notif.date.replace(' ', 'T'));
                dateDisplay = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
            }

            list.innerHTML += `
                <div class="notification-item ${isRejected ? 'rejected' : ''}"
                    onclick="window.location.href='{{ route('student.activity') }}?view=${notif.type === 'POST_STATUS' ? 'posts' : 'claims'}';">
                    <strong style="color: ${statusColor};">${title}</strong>
                    <p>${description}</p>
                    <span style="font-size: 0.7em; color: #999; display: block; margin-top: 3px;">${dateDisplay}</span>
                </div>
            `;
        });
        notificationsLoaded = true;
    }

    async function loadNotifications() {
        try {
            const response = await fetch('{{ route('api.notifications') }}');
            const result = await response.json();
            if (result.success) {
                renderNotifications(result.data);
            } else {
                document.getElementById('notification-list').innerHTML = '<div class="notification-item" style="text-align: center; color: red;">Error loading.</div>';
            }
        } catch (error) {
            document.getElementById('notification-list').innerHTML = '<div class="notification-item" style="text-align: center; color: red;">Network Error.</div>';
        }
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
            if (!notificationsLoaded) loadNotifications();
        }
    }

    document.addEventListener('click', function(event) {
        const container = document.getElementById('notificationBellContainer');
        const dropdown  = document.getElementById('notificationDropdown');
        if (container && dropdown && !container.contains(event.target) && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    });
</script>
@endsection