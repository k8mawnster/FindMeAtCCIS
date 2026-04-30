@extends('layouts.app')

@section('title', 'Resolved Cases')

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
            <h2>RESOLVED CASES</h2>
            <p>Successfully completed lost & found cases.</p>
        </div>

        <form method="GET" action="{{ route('admin.resolved') }}"
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; gap: 15px; align-items: center;">
                <select name="period" id="period-filter"
                    style="width: 150px; padding: 8px 15px; border-radius: 20px; border: 1px solid var(--color-input-border);">
                    <option value="" {{ $filter_period == '' ? 'selected' : '' }}>All Time</option>
                    <option value="day" {{ $filter_period == 'day' ? 'selected' : '' }}>Last 24 Hours</option>
                    <option value="week" {{ $filter_period == 'week' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="month" {{ $filter_period == 'month' ? 'selected' : '' }}>Last 30 Days</option>
                </select>
                <button type="submit" class="btn-view-details" style="padding: 8px 15px;">
                    <i class="fas fa-filter"></i> Apply Filter
                </button>
            </div>
            <button type="button" class="btn-approve"
                style="padding: 8px 15px; background-color: var(--color-blue-dark);"
                onclick="triggerDownload()">
                <i class="fas fa-download"></i> Download CSV
            </button>
        </form>

        <div class="resolved-count-tag">
            {{ $cases->count() }} Resolved Cases
            {{ $filter_period ? '(Since ' . $date_from . ')' : '' }}
        </div>

        @if($cases->isEmpty())
            <p style="text-align: center; color: #666; margin-top: 20px;">No resolved cases yet.</p>
        @else
            @foreach($cases as $case)
                <div class="admin-item-card" style="align-items: flex-start;">
                    <div class="admin-item-details" style="flex-direction: column; gap: 10px;">
                        <div class="item-info" style="align-items: flex-start;">
                            <div class="item-image-box">
                                <img src="{{ $case->item->image_url ? asset($case->item->image_url) : asset('img/no-image.png') }}"
                                    alt="{{ $case->item->name }}">
                            </div>
                            <div class="item-details">
                                <h4>{{ $case->item->name }}</h4>
                                <p>{{ $case->item->category->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div style="font-size: 0.9em; padding-left: 100px;">
                            <p>Resolved by: <strong>{{ $case->claimer_full_name }}</strong></p>
                            <p>Course/Section: {{ $case->claimedBy->course->course_code ?? 'N/A' }} / {{ $case->claimedBy->section_name ?? 'N/A' }}</p>
                            <p>Resolved Date: {{ \Carbon\Carbon::parse($case->claim_date)->format('d-m-Y') }}</p>
                            <p>Pick-up Location: {{ $case->item->last_known_location }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection

@section('scripts')
<script>
    function triggerDownload() {
        const period = document.getElementById('period-filter').value;
        let url = '{{ route('admin.resolved') }}?action=download';
        if (period) url += '&period=' + period;
        window.location.href = url;
    }
</script>
@endsection