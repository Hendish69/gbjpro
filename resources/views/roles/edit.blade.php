@extends('layouts.app')

@section('title', 'Edit Role Permissions')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Role: {{ $role->name }}</h1>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('roles.update', $role->id) }}">
            @csrf
            @method('PUT')
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $role->name) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $role->description) }}">
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Menu Permissions</h5>
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Menu</th>
                            <th>View</th>
                            <th>Create</th>
                            <th>Edit</th>
                            <th>Delete</th>
                            <th>Select All</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                        @php
                            $permission = $permissions[$menu->id] ?? null;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $menu->name }}</strong>
                                @if($menu->icon)
                                <br><small class="text-muted"><i class="{{ $menu->icon }}"></i> {{ $menu->route }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="permissions[{{ $menu->id }}][]" value="view" 
                                    {{ $permission && $permission->can_view ? 'checked' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="permissions[{{ $menu->id }}][]" value="create" 
                                    {{ $permission && $permission->can_create ? 'checked' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="permissions[{{ $menu->id }}][]" value="edit" 
                                    {{ $permission && $permission->can_edit ? 'checked' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" name="permissions[{{ $menu->id }}][]" value="delete" 
                                    {{ $permission && $permission->can_delete ? 'checked' : '' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="select-all" data-menu="{{ $menu->id }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Update Role & Permissions</button>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllCheckboxes = document.querySelectorAll('.select-all');
    
    selectAllCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const menuId = this.getAttribute('data-menu');
            const checkboxes = document.querySelectorAll(`input[name="permissions[${menuId}][]"]`);
            
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    });
});
</script>
@endpush
@endsection