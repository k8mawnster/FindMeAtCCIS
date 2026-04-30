@extends('layouts.app')

@section('title', 'Report Lost Item')

@section('navbar-right')
    <a href="{{ route('student.settings') }}" style="color: white; margin-right: 15px;">
        <i class="fas fa-cog" style="font-size: 1.3em;"></i>
    </a>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endsection

@section('content')
    <div class="admin-list-container" style="max-width: 600px;">
        <a href="{{ route('student.dashboard') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="form-title" style="text-align: left; margin-top: 15px;">Report Lost Item</div>

        @if(session('success'))
            <p style="color: var(--color-green-button-light); font-weight: bold;">{{ session('success') }}</p>
        @endif

        <form action="{{ route('student.report.lost.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="name" required value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="" disabled selected>Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category_id }}" {{ old('category_id') == $cat->category_id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" required>{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label>Last Known Location</label>
                <input type="text" name="location" id="location-input" required value="{{ old('location') }}" placeholder="Click the map or type here">
            </div>

            <div class="form-group">
                <label>Pin Location on Map</label>
                <div id="map" style="height: 300px; border-radius: 10px; border: 1px solid #ccc;"></div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </div>

            <div class="form-group">
                <label>Upload Image (optional)</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <button type="submit" class="btn btn-login" style="color: white;">SUBMIT REPORT</button>
        </form>
    </div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([18.1980, 120.5936], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        if (marker) marker.setLatLng(e.latlng);
        else marker = L.marker(e.latlng).addTo(map);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        document.getElementById('location-input').value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    });
</script>
@endsection