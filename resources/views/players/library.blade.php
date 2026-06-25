@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Player Library</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('players.library.create') }}" class="btn btn-primary me-2">
            <i class="fas fa-plus"></i> Add Player
        </a>
        <a href="{{ route('players.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> All Players
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="h4">{{ $totalPlayers }}</div>
                <div>Total Players</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="h4">{{ $activePlayers }}</div>
                <div>Active Players</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="h4">{{ $categories->count() }}</div>
                <div>Categories</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="h4">{{ $players->avg('rating') ? round($players->avg('rating')) : 0 }}</div>
                <div>Average Rating</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('players.library') }}">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search players..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="rating_min" class="form-control" placeholder="Min Rating" 
                           value="{{ request('rating_min') }}">
                </div>
                <div class="col-md-2">
                    <input type="number" name="rating_max" class="form-control" placeholder="Max Rating" 
                           value="{{ request('rating_max') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Players Grid -->
<div class="card">
    <div class="card-body">
        @if($players->count() > 0)
        <div class="row">
            @foreach($players as $player)
            <div class="col-md-4 mb-4">
                <div class="card player-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            @if($player->photo)
                                <img src="{{ asset('storage/' . $player->photo) }}" 
                                     class="player-avatar me-3">
                            @else
                                <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">{{ $player->display_name }}</h5>
                                <div class="text-muted small">
                                    {{ $player->name }}
                                    @if($player->nickname)
                                    <br><small>aka {{ $player->nickname }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="rating-badge">
                                <span class="badge bg-primary">{{ $player->rating }}</span>
                            </div>
                        </div>

                        <div class="player-stats mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h6 mb-0">{{ $player->total_matches }}</div>
                                    <small class="text-muted">Matches</small>
                                </div>
                                <div class="col-4">
                                    <div class="h6 mb-0 {{ $player->win_rate >= 50 ? 'text-success' : 'text-warning' }}">
                                        {{ $player->win_rate }}%
                                    </div>
                                    <small class="text-muted">Win Rate</small>
                                </div>
                                <div class="col-4">
                                    <div class="h6 mb-0">{{ $player->total_tournaments }}</div>
                                    <small class="text-muted">Tournaments</small>
                                </div>
                            </div>
                        </div>

                        @if($player->categories->count() > 0)
                        <div class="player-categories mb-3">
                            @foreach($player->categories as $category)
                            <span class="badge me-1" style="background-color: {{ $category->color }}">
                                {{ $category->name }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                        @if($player->playing_style)
                        <div class="player-style mb-2">
                            <small class="text-muted">
                                <i class="fas fa-table-tennis-paddle-ball"></i>
                                {{ ucfirst($player->playing_style) }} 
                                @if($player->grip_style)
                                ({{ $player->grip_style }})
                                @endif
                            </small>
                        </div>
                        @endif

                        @if($player->last_played_at)
                        <div class="last-played">
                            <small class="text-muted">
                                Last played: {{ $player->last_played_at->diffForHumans() }}
                            </small>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('players.show-library', $player) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('players.edit-library', $player) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('players.destroy-library', $player) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Remove from library?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $players->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No players found in library</h5>
            <p class="text-muted">Add some players to get started.</p>
            <a href="{{ route('players.library.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add First Player
            </a>
        </div>
        @endif
    </div>
</div>

<style>
.player-card {
    transition: transform 0.2s;
}
.player-card:hover {
    transform: translateY(-5px);
}
.rating-badge {
    position: absolute;
    top: 15px;
    right: 15px;
}
.player-stats {
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
    padding: 10px 0;
}
</style>
@endsection