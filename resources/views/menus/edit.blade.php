@extends('layouts.app')

@section('title', 'Edit Menu')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Menu: {{ $menu->name }}</h1>
    <a href="{{ route('menus.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('menus.update', $menu->id) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Menu Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $menu->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="route" class="form-label">Route Name</label>
                        <input type="text" class="form-control" id="route" name="route" value="{{ old('route', $menu->route) }}"
                               placeholder="e.g., users.index, dashboard">
                        @error('route')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon Class</label>
                        <input type="text" class="form-control" id="icon" name="icon" value="{{ old('icon', $menu->icon) }}"
                               placeholder="e.g., fas fa-home, bi bi-house">
                        <div class="form-text">
                            Use Font Awesome or Bootstrap Icons class names
                        </div>
                        @error('icon')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Parent Menu</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">No Parent (Main Menu)</option>
                            @foreach($parentMenus as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $menu->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" 
                               value="{{ old('sort_order', $menu->sort_order) }}" min="0" required>
                        @error('sort_order')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3 form-check pt-4">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active Menu</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Icon Preview</label>
                <div id="iconPreview" class="p-3 border rounded bg-light text-center">
                    <i id="previewIcon" class="{{ $menu->icon }} fs-1"></i>
                    <div id="previewText" class="text-muted mt-2" style="display: none;">Icon preview will appear here</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Menu</button>
            <a href="{{ route('menus.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon');
    const previewIcon = document.getElementById('previewIcon');
    const previewText = document.getElementById('previewText');
    
    function updateIconPreview() {
        const iconClass = iconInput.value.trim();
        
        if (iconClass) {
            previewIcon.className = iconClass + ' fs-1';
            previewText.style.display = 'none';
            previewIcon.style.display = 'inline-block';
        } else {
            previewIcon.style.display = 'none';
            previewText.style.display = 'block';
        }
    }
    
    iconInput.addEventListener('input', updateIconPreview);
    iconInput.addEventListener('change', updateIconPreview);
});
</script>
@endpush
@endsection