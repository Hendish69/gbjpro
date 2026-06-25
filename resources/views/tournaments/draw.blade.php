@extends('layouts.app')

@section('title', 'Tournament Draw - ' . $tournament->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-random me-2"></i>
                        Tournament Draw Management - {{ $tournament->name }}
                    </h4>
                </div>
                @if(in_array($tournament->type, ['double', 'doubleduo']) && $tournament->players->count() >= 2)
                <div class="text-center mt-3">
                    <a href="{{ route('tournaments.pairing', $tournament) }}" class="btn btn-warning btn-lg">
                        <i class="fas fa-handshake me-2"></i>Go to Player Pairing
                    </a>
                    <small class="d-block text-muted mt-2">
                        Required for {{ ucfirst($tournament->type) }} tournaments
                    </small>
                </div>
                @endif
                <div class="card-body">
                    <div class="row">
                        <!-- Kolom 1: Add Players -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-plus me-2"></i>
                                        Add Players to Tournament
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('tournaments.players.store', $tournament) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Select Player</label>
                                            <select name="player_id" class="form-select" required>
                                                <option value="">Choose Player...</option>
                                                @foreach($players as $player)
                                                <option value="{{ $player->id }}">
                                                    {{ $player->display_name }} 
                                                   - ({{ $player->division_ranking }}) -
                                                    {{ $player->ptmClub->name ?? 'No Club' }}
                                                    
                                                </option>
                                                @endforeach
                                            </select>
                                            @if($players->count() == 0)
                                                <div class="alert alert-warning mt-2 mb-0">
                                                    <i class="fas fa-info-circle"></i> All available players have been added to this tournament.
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Representing Club (Optional)</label>
                                            <select name="representing_ptm_club_id" class="form-select">
                                                <option value="">Same as Original Club</option>
                                                @foreach($ptmClubs as $club)
                                                <option value="{{ $club->id }}">{{ $club->name }}</option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                Jika player mewakili klub yang berbeda
                                            </small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Seed Number</label>
                                                    <input type="number" name="seed" class="form-control" min="1" 
                                                           placeholder="Optional">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Group</label>
                                                    <input type="text" name="group" class="form-control" 
                                                           placeholder="e.g., Group A">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Representation Notes</label>
                                            <textarea name="representation_notes" class="form-control" 
                                                      rows="2" placeholder="Optional notes..."></textarea>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Add to Tournament
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <!-- Di bagian atas card header atau di actions -->

                            <!-- Quick Add Section -->
                            <div class="card mt-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt me-2"></i>
                                        Quick Add Multiple Players
                                        <small>({{ $players->count() }} available)</small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('tournaments.add-players', $tournament) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                           <label class="form-label">Select Multiple Players <small class="text-muted">(Hold Ctrl/Cmd to select multiple)</small></label>
                                            @if($players->count() > 0)
                                                <select name="player_ids[]" class="form-select" multiple size="8">
                                                    @foreach($players as $player)
                                                    <option value="{{ $player->id }}">
                                                        {{ $player->display_name }} 
                                                        - ({{ $player->division_ranking }}) - {{ $player->ptmClub->name ?? 'No Club' }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">
                                                    Available players: {{ $players->count() }} | 
                                                    Hold Ctrl/Cmd to select multiple
                                                </small>
                                                @else
                                                <div class="alert alert-info">
                                                    <i class="fas fa-users"></i> 
                                                    No available players to add. All players are already in the tournament.
                                                </div>
                                            @endif
                                        </div>
                                        @if($players->count() > 0)
                                            <button type="submit" class="btn btn-info">
                                                <i class="fas fa-users me-2"></i>Add Selected Players
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom 2: Manage Existing Players -->
                        <!-- Kolom 2: Manage Existing Players -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-list me-2"></i>
                                        Tournament Players ({{ $tournament->players->count() }})
                                    </h5>
                                    <div>
                                        <span class="badge bg-light text-dark">
                                            Div Range: 
                                            @if($tournament->players->count() > 0)
                                                {{ $tournament->players->min('division_ranking') }} - {{ $tournament->players->max('division_ranking') }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($tournament->players->count() >= 2)
                                        <div class="text-center mt-4">
                                            <form action="{{ route('tournaments.generate-bracket', $tournament) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-lg">
                                                    <i class="fas fa-trophy me-2"></i>
                                                    Generate Tournament Bracket
                                                </button>
                                                <small class="d-block text-muted mt-2">
                                                    Ready to create bracket with {{ $tournament->players->count() }} players
                                                </small>
                                            </form>
                                        </div>
                                        @endif
                                        <div class="row text-center mt-3">
                                            <div class="col-3">
                                                <small class="text-muted">Div 1-3</small>
                                                <div class="fw-bold text-danger">
                                                    {{ $tournament->players->where('division_ranking', '<=', 3)->count() }}
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted">Div 4-6</small>
                                                <div class="fw-bold text-warning">
                                                    {{ $tournament->players->whereBetween('division_ranking', [4, 6])->count() }}
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted">Div 7-9</small>
                                                <div class="fw-bold text-info">
                                                    {{ $tournament->players->whereBetween('division_ranking', [7, 9])->count() }}
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <small class="text-muted">Div 10-11</small>
                                                <div class="fw-bold text-secondary">
                                                    {{ $tournament->players->where('division_ranking', '>=', 10)->count() }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Generate Bracket Button -->
                                    
                                    @if($tournament->players->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Player</th>
                                                        <th>Division</th>
                                                        <th>Club</th>
                                                        <th>Seed</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($tournament->tournamentPlayers as $tp)
                                                        @if($tp && $tp->player)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>
                                                                <strong>{{ $tp->player->display_name }}</strong>
                                                                @if($tp->is_representing_different_club && $tp->representingClub)
                                                                    <br>
                                                                    <small class="text-warning">
                                                                        <i class="fas fa-exchange-alt"></i>
                                                                        {{ $tp->representingClub->name }}
                                                                    </small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge 
                                                                    @if($tp->player->division_ranking <= 3) bg-danger
                                                                    @elseif($tp->player->division_ranking <= 6) bg-warning
                                                                    @else bg-info
                                                                    @endif">
                                                                    Div {{ $tp->player->division_ranking }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $tp->player->ptmClub->name ?? '-' }}</td>
                                                            <td>
                                                                @if($tp->seed)
                                                                    <span class="badge bg-primary">#{{ $tp->seed }}</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="{{ route('tournaments.players.edit', [$tournament, $tp]) }}" 
                                                                    class="btn btn-outline-primary" title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <form action="{{ route('tournaments.players.destroy', [$tournament, $tp]) }}" 
                                                                        method="POST" class="d-inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-outline-danger" 
                                                                                onclick="return confirm('Remove {{ $tp->player->display_name }} from tournament?')"
                                                                                title="Remove">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                    
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No players added yet. Add players to start the tournament.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection