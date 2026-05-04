@extends('layouts.app')

@section('title', 'Edit Report')

@section('navbar-right')
    <a href="{{ route('student.settings') }}" style="color: white; margin-right: 15px;">
        <i class="fas fa-cog" style="font-size: 1.3em;"></i>
    </a>
    <i class="fas fa-user-circle"></i>
    <span>{{ session('user_name') }}</span>
@endsection

@section('content')
    <div class="admin-list-container" style="max-width: 600px;">
        <a href="{{ route('student.activity') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Activity
        </a>

        <div class="form-title" style="text-align: left; margin-top: 15px;">Edit {{ $item->item_status }} Report</div>

        <form action="{{ route('student.reports.update', $item->item_id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="name" required value="{{ old('name', $item->name) }}">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="category-select" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->category_id }}" data-name="{{ $cat->name }}" {{ old('category_id', $item->category_id) == $cat->category_id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" id="custom-category-group" style="display: none;">
                <label>Specify Category</label>
                <input type="text" name="custom_category" id="custom-category-input" maxlength="80" value="{{ old('custom_category', $item->custom_category) }}" placeholder="e.g. Umbrella, Calculator">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" required>{{ old('description', $item->description) }}</textarea>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" required value="{{ old('location', $item->last_known_location) }}">
            </div>
            <input type="hidden" name="latitude" value="{{ old('latitude', $item->latitude) }}">
            <input type="hidden" name="longitude" value="{{ old('longitude', $item->longitude) }}">

            @if($item->photos->isNotEmpty() || $item->image_url)
                <div class="form-group">
                    <label>Current Photos</label>
                    <div class="photo-strip">
                        @foreach($item->photos as $photo)
                            <img src="{{ $photo->image_url }}" alt="{{ $item->name }} photo">
                        @endforeach
                        @if($item->photos->isEmpty() && $item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }} photo">
                        @endif
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label>Add More Images (optional)</label>
                <input type="file" name="images[]" accept="image/*" multiple>
            </div>

            <button type="submit" class="btn btn-login" style="color: white;">SAVE CHANGES</button>
        </form>
    </div>
@endsection

@section('scripts')
<script>
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
</script>
@endsection
