@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Role Management</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Users Count</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $role->name }}</strong>
                        </td>
                        <td>{{ $role->description }}</td>
                        <td>
                            <span class="badge bg-info">{{ $role->users_count ?? $role->users->count() }}</span>
                        </td>
                        <td>
                            @foreach($role->permissions as $permission)
                                @if($permission->can_view)
                                <span class="badge bg-light text-dark mb-1">
                                    {{ $permission->menu->name }}
                                    @if($permission->can_create) <i class="fas fa-plus text-success"></i> @endif
                                    @if($permission->can_edit) <i class="fas fa-edit text-warning"></i> @endif
                                    @if($permission->can_delete) <i class="fas fa-trash text-danger"></i> @endif
                                </span>
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @if(auth()->user()->hasPermission('Role Management', 'edit'))
                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Edit Permissions
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection