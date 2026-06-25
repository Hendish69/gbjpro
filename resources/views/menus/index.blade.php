@extends('layouts.app')

@section('title', 'Menu Management')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Menu Management</h1>
    @if(auth()->user()->hasPermission('Menu Management', 'create'))
    <div>
        <a href="{{ route('menus.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Menu
        </a>
        <button class="btn btn-info" onclick="toggleSortable()">
            <i class="fas fa-sort"></i> Reorder Menus
        </button>
    </div>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Normal View -->
<div id="normalView">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Route</th>
                            <th>Icon</th>
                            <th>Parent Menu</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <i class="{{ $menu->icon }} me-2"></i>
                                <strong>{{ $menu->name }}</strong>
                            </td>
                            <td>
                                <code>{{ $menu->route ?? '-' }}</code>
                            </td>
                            <td>
                                @if($menu->icon)
                                <i class="{{ $menu->icon }}"></i>
                                <small class="text-muted">{{ $menu->icon }}</small>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                @if($menu->parent)
                                <span class="badge bg-secondary">{{ $menu->parent->name }}</span>
                                @else
                                <span class="badge bg-light text-dark">Main Menu</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $menu->sort_order }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $menu->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('Menu Management', 'edit'))
                                <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->hasPermission('Menu Management', 'delete'))
                                <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        
                        <!-- Sub-menus -->
                        @foreach($menu->children as $child)
                        <tr>
                            <td></td>
                            <td class="ps-4">
                                <i class="{{ $child->icon }} me-2"></i>
                                {{ $child->name }}
                            </td>
                            <td>
                                <code>{{ $child->route ?? '-' }}</code>
                            </td>
                            <td>
                                @if($child->icon)
                                <i class="{{ $child->icon }}"></i>
                                <small class="text-muted">{{ $child->icon }}</small>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $menu->name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $child->sort_order }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $child->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $child->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('Menu Management', 'edit'))
                                <a href="{{ route('menus.edit', $child->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                
                                @if(auth()->user()->hasPermission('Menu Management', 'delete'))
                                <form action="{{ route('menus.destroy', $child->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sortable View -->
<div id="sortableView" style="display: none;">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Drag and drop to reorder menus</h5>
            <div>
                <button class="btn btn-success" onclick="saveMenuOrder()">
                    <i class="fas fa-save"></i> Save Order
                </button>
                <button class="btn btn-secondary" onclick="toggleSortable()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Main Menus</h6>
                    <ul id="mainMenus" class="list-group sortable-menu">
                        @foreach($parentMenus as $menu)
                        <li class="list-group-item" data-id="{{ $menu->id }}">
                            <i class="{{ $menu->icon }} me-2"></i>
                            {{ $menu->name }}
                            <small class="text-muted ms-2">(Order: <span class="order-display">{{ $menu->sort_order }}</span>)</small>
                            
                            @if($menu->children->count() > 0)
                            <ul class="list-group mt-2 sortable-submenu">
                                @foreach($menu->children as $child)
                                <li class="list-group-item" data-id="{{ $child->id }}">
                                    <i class="{{ $child->icon }} me-2"></i>
                                    {{ $child->name }}
                                    <small class="text-muted ms-2">(Order: <span class="order-display">{{ $child->sort_order }}</span>)</small>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Instructions:</h6>
                        <ul class="mb-0">
                            <li>Drag and drop to reorder menus</li>
                            <li>Main menus can contain sub-menus</li>
                            <li>Sub-menus can be moved between main menus</li>
                            <li>Click "Save Order" when finished</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.css" rel="stylesheet">
<style>
.sortable-menu .sortable-chosen, .sortable-submenu .sortable-chosen {
    background-color: #e9ecef;
    cursor: move;
}
.sortable-ghost {
    opacity: 0.5;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
let mainSortable, subSortable;

function toggleSortable() {
    const normalView = document.getElementById('normalView');
    const sortableView = document.getElementById('sortableView');
    
    if (normalView.style.display === 'none') {
        normalView.style.display = 'block';
        sortableView.style.display = 'none';
    } else {
        normalView.style.display = 'none';
        sortableView.style.display = 'block';
        initializeSortable();
    }
}

function initializeSortable() {
    // Main menus sortable
    mainSortable = new Sortable(document.getElementById('mainMenus'), {
        group: 'menus',
        animation: 150,
        onEnd: function(evt) {
            updateOrderDisplays();
        }
    });

    // Make all submenus sortable and connect them
    document.querySelectorAll('.sortable-submenu').forEach(submenu => {
        new Sortable(submenu, {
            group: 'submenus',
            animation: 150,
            onEnd: function(evt) {
                updateOrderDisplays();
            }
        });
    });
}

function updateOrderDisplays() {
    document.querySelectorAll('#mainMenus > li').forEach((item, index) => {
        const orderDisplay = item.querySelector('.order-display');
        if (orderDisplay) {
            orderDisplay.textContent = index;
        }
        
        // Update submenu orders
        const submenus = item.querySelectorAll('.sortable-submenu > li');
        submenus.forEach((subitem, subindex) => {
            const subOrderDisplay = subitem.querySelector('.order-display');
            if (subOrderDisplay) {
                subOrderDisplay.textContent = subindex;
            }
        });
    });
}

function saveMenuOrder() {
    const menus = [];
    
    document.querySelectorAll('#mainMenus > li').forEach((mainItem, mainIndex) => {
        const mainId = mainItem.getAttribute('data-id');
        menus.push({
            id: mainId,
            sort_order: mainIndex,
            parent_id: null
        });
        
        // Process submenus
        const submenus = mainItem.querySelectorAll('.sortable-submenu > li');
        submenus.forEach((subItem, subIndex) => {
            const subId = subItem.getAttribute('data-id');
            menus.push({
                id: subId,
                sort_order: subIndex,
                parent_id: mainId
            });
        });
    });

    // Send AJAX request
    fetch('{{ route("menus.update-order") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ menus: menus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Menu order saved successfully!');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving menu order.');
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateOrderDisplays();
});
</script>
@endpush