@extends('layouts.app')

@section('title', 'Report Found Item')

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

        <div class="form-title" style="text-align: left; margin-top: 15px;">Report Found Item</div>

        @if(session('success'))
            <p style="color: var(--color-green-button-light); font-weight: bold;">{{ session('success') }}</p>
        @endif

        <form action="{{ route('student.report.found.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="name" required value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="category-select" required>
                    <option value="" disabled selected>Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category_id }}" data-name="{{ $cat->name }}" {{ old('category_id') == $cat->category_id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" id="custom-category-group" style="display: none;">
                <label>Specify Category</label>
                <input type="text" name="custom_category" id="custom-category-input" maxlength="80" value="{{ old('custom_category') }}" placeholder="e.g. Umbrella, Calculator">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" required>{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label>Found Location</label>
                <input type="text" name="location" id="location-input" required value="{{ old('location') }}" placeholder="e.g. CCIS, Library, Canteen">
            </div>

            <div class="form-group">
                <label>Pin Location on Map</label>
                <div id="map" style="height: 300px; border-radius: 10px; border: 1px solid #ccc;"></div>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </div>

            <div class="form-group">
                <label>Upload Images (optional)</label>
                <input type="file" name="images[]" accept="image/*" multiple>
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
    const categorySelect = document.getElementById('category-select');
    const customCategoryGroup = document.getElementById('custom-category-group');
    const customCategoryInput = document.getElementById('custom-category-input');

    function toggleCustomCategory() {
        const selected = categorySelect.options[categorySelect.selectedIndex];
        const isOther = selected?.dataset.name?.toLowerCase() === 'other';
        customCategoryGroup.style.display = isOther ? 'block' : 'none';
        customCategoryInput.required = isOther;
        if (!isOther) customCategoryInput.value = '';
    }

    categorySelect.addEventListener('change', toggleCustomCategory);
    toggleCustomCategory();

    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        if (marker) marker.setLatLng(e.latlng);
        else marker = L.marker(e.latlng).addTo(map);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    });
</script>
@endsection
