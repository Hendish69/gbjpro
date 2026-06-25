@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">PTM Clubs Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('ptm-clubs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Club
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="h4">{{ $totalClubs }}</div>
                <div>Total Clubs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="h4">{{ $activeClubs }}</div>
                <div>Active Clubs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="h4">{{ $totalPlayers }}</div>
                <div>Total Players</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="h4">{{ number_format($totalPlayers / max($totalClubs, 1), 1) }}</div>
                <div>Avg Players/Club</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('ptm-clubs.index') }}">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search clubs..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="sort" class="form-control">
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Sort by Name</option>
                        <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Sort by Date</option>
                        <option value="players_count" {{ request('sort') == 'players_count' ? 'selected' : '' }}>Sort by Players</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Clubs Grid -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">PTM Clubs</h6>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog"></i> Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" id="selectAllBtn">Select All</a></li>
                <li><a class="dropdown-item" href="#" id="deselectAllBtn">Deselect All</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form id="bulkActionForm" method="POST" action="{{ route('ptm-clubs.bulk-actions') }}">
                        @csrf
                        <input type="hidden" name="action" id="bulkAction">
                        <input type="hidden" name="club_ids" id="bulkClubIds">
                        <button type="button" class="dropdown-item bulk-action-btn" data-action="activate">
                            <i class="fas fa-check text-success"></i> Activate Selected
                        </button>
                        <button type="button" class="dropdown-item bulk-action-btn" data-action="deactivate">
                            <i class="fas fa-times text-warning"></i> Deactivate Selected
                        </button>
                        <button type="button" class="dropdown-item bulk-action-btn" data-action="delete">
                            <i class="fas fa-trash text-danger"></i> Delete Selected
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        @if($clubs->count() > 0)
        <div class="row">
            @foreach($clubs as $club)
            <div class="col-md-4 mb-4">
                <div class="card club-card h-100 {{ $club->is_active ? 'border-success' : 'border-secondary' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ $club->name }}</h5>
                                @if($club->code)
                                <h6 class="card-subtitle text-muted">{{ $club->code }}</h6>
                                @endif
                            </div>
                            <div class="status-badge">
                                <span class="badge bg-{{ $club->is_active ? 'success' : 'secondary' }}">
                                    {{ $club->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <div class="club-info mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                <small class="text-muted">{{ $club->city }}, {{ $club->province }}</small>
                            </div>
                            @if($club->phone)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-phone text-muted me-2"></i>
                                <small class="text-muted">{{ $club->phone }}</small>
                            </div>
                            @endif
                            @if($club->email)
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-envelope text-muted me-2"></i>
                                <small class="text-muted">{{ $club->email }}</small>
                            </div>
                            @endif
                        </div>

                        @if($club->description)
                        <div class="club-description mb-3">
                            <p class="card-text small text-muted">
                                {{ Str::limit($club->description, 100) }}
                            </p>
                        </div>
                        @endif

                        <div class="club-stats">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h5 mb-0 text-primary">{{ $club->active_players_count }}</div>
                                    <small class="text-muted">Active Players</small>
                                </div>
                                <div class="col-6">
                                    <div class="h5 mb-0 text-success">{{ $club->players_count ?? 0 }}</div>
                                    <small class="text-muted">Total Players</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input club-checkbox" type="checkbox" 
                                       value="{{ $club->id }}" id="club{{ $club->id }}">
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('ptm-clubs.show', $club) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('ptm-clubs.edit', $club) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('ptm-clubs.destroy', $club) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Delete this club?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $clubs->links('pagination::bootstrap-5') }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-club fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No PTM Clubs Found</h5>
            <p class="text-muted">Create your first PTM club to get started.</p>
            <a href="{{ route('ptm-clubs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create First Club
            </a>
        </div>
        @endif
    </div>
</div>

<style>
.club-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.club-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.status-badge {
    position: absolute;
    top: 15px;
    right: 15px;
}
.club-stats {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select/Deselect All
    document.getElementById('selectAllBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.club-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    document.getElementById('deselectAllBtn').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.club-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Bulk Actions
    document.querySelectorAll('.bulk-action-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const selectedClubs = Array.from(document.querySelectorAll('.club-checkbox:checked'))
                .map(checkbox => checkbox.value);

            if (selectedClubs.length === 0) {
                alert('Please select at least one club.');
                return;
            }

            if (action === 'delete' && !confirm('Are you sure you want to delete selected clubs?')) {
                return;
            }

            document.getElementById('bulkAction').value = action;
            document.getElementById('bulkClubIds').value = JSON.stringify(selectedClubs);
            document.getElementById('bulkActionForm').submit();
        });
    });
});
</script>
@endsection