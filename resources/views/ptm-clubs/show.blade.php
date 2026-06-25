@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">{{ $ptmClub->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('ptm-clubs.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Clubs
        </a>
        <a href="{{ route('ptm-clubs.edit', $ptmClub) }}" class="btn btn-warning me-2">
            <i class="fas fa-edit"></i> Edit Club
        </a>
        <form action="{{ route('ptm-clubs.destroy', $ptmClub) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" 
                    onclick="return confirm('Delete this club? All players will be unassigned.')">
                <i class="fas fa-trash"></i> Delete Club
            </button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Club Information -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Club Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $ptmClub->is_active ? 'success' : 'secondary' }}">
                                {{ $ptmClub->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @if($ptmClub->code)
                    <tr>
                        <td><strong>Code:</strong></td>
                        <td>{{ $ptmClub->code }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Location:</strong></td>
                        <td>{{ $ptmClub->city }}, {{ $ptmClub->province }}</td>
                    </tr>
                    <tr>
                        <td><strong>Address:</strong></td>
                        <td>{{ $ptmClub->address }}</td>
                    </tr>
                    @if($ptmClub->phone)
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>{{ $ptmClub->phone }}</td>
                    </tr>
                    @endif
                    @if($ptmClub->email)
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $ptmClub->email }}</td>
                    </tr>
                    @endif
                    @if($ptmClub->website)
                    <tr>
                        <td><strong>Website:</strong></td>
                        <td>
                            <a href="{{ $ptmClub->website }}" target="_blank" class="text-decoration-none">
                                {{ $ptmClub->website }}
                                <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>

                @if($ptmClub->description)
                <div class="mt-3">
                    <strong>Description:</strong>
                    <p class="text-muted mt-1">{{ $ptmClub->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Club Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary mb-0">{{ $stats['total_players'] }}</div>
                        <small class="text-muted">Total Players</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-0">{{ $stats['active_players'] }}</div>
                        <small class="text-muted">Active Players</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-info mb-0">{{ $stats['top_division'] ? 'Divisi ' . $stats['top_division'] : '-' }}</div>
                        <small class="text-muted">Top Division</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-warning mb-0">{{ $stats['average_division'] ? number_format($stats['average_division'], 1) : '-' }}</div>
                        <small class="text-muted">Avg Division</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Division Distribution -->
        @if($divisionDistribution->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Division Distribution</h6>
            </div>
            <div class="card-body">
                @foreach($divisionDistribution as $division)
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Divisi {{ $division['division'] }}</span>
                        <span class="text-muted">{{ $division['count'] }} players ({{ $division['percentage'] }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: {{ $division['percentage'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Players List -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Club Players ({{ $ptmClub->players->count() }})</h6>
                <a href="{{route('players.create', [
    'ptm_club_id' => $ptmClub->id,
    'redirect_to' => route('ptm-clubs.show', $ptmClub->id)
]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add Player
                </a>
            </div>
            <div class="card-body">
                @if($ptmClub->players->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Divisi</th>
                                <th>Matches</th>
                                <th>Win Rate</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ptmClub->players as $player)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($player->photo)
                                            <img src="{{ asset('images/' . $player->photo) }}" 
                                                 class="player-avatar me-2">
                                        @else
                                            <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $player->nickname??$player->name }}</strong>
                                            @if($player->name)
                                            <br><small class="text-muted">{{ $player->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $player->division_color }}">
                                        {{ $player->division_name }}
                                    </span>
                                </td>
                                <td>{{ $player->total_matches }}</td>
                                <td>
                                    <span class="badge bg-{{ $player->win_rate >= 50 ? 'success' : 'warning' }}">
                                        {{ $player->win_rate }}%
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $player->is_active ? 'success' : 'secondary' }}">
                                        {{ $player->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('players.show', $player) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('players.edit', $player) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-2x text-muted mb-3"></i>
                    <h5 class="text-muted">No Players in This Club</h5>
                    <p class="text-muted">Add players to this club to get started.</p>
                    <a href="{{ route('players.create.fromClub', $ptmClub->id) }}?ptm_club_id={{ $ptmClub->id }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Player
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.player-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
</style>
@endsection