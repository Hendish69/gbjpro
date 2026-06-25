@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tournament Players: {{ $tournament->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tournaments.draw', $tournament) }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Back to Draw
        </a>
        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-outline-primary">
            <i class="fas fa-eye"></i> View Tournament
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Player Management</h6>
    </div>
    <div class="card-body">
        @if($players->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Division</th>
                        <th>PTM Club</th>
                        <th>PTM Representation</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($players as $player)
                    @php
                        $isRepresentingDifferent = $player->pivot->is_representing_different_club;
                        $representingClub = $player->pivot->representing_ptm_club_id ? 
                            $ptmClubs->find($player->pivot->representing_ptm_club_id) : null;
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($player->photo)
                                    <img src="{{ asset('storage/' . $player->photo) }}" 
                                         class="player-avatar me-2">
                                @else
                                    <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $player->display_name }}</strong>
                                    @if($player->ptm_number)
                                    <br><small class="text-muted">No: {{ $player->ptm_number }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $player->division_color }}">
                                {{ $player->division_name }}
                            </span>
                        </td>
                        <td>
                            @if($player->ptmClub)
                                <span class="badge bg-primary">
                                    {{ $player->ptmClub->code ? $player->ptmClub->code . ' - ' : '' }}{{ $player->ptmClub->name }}
                                </span>
                            @else
                                <span class="text-muted">Nothing PTM Club</span>
                            @endif
                        </td>
                        <td>
                            @if($isRepresentingDifferent && $representingClub)
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-exchange-alt me-1"></i>
                                    {{ $representingClub->code ? $representingClub->code . ' - ' : '' }}{{ $representingClub->name }}
                                </span>
                                @if($player->pivot->representation_notes)
                                <br><small class="text-muted">{{ $player->pivot->representation_notes }}</small>
                                @endif
                            @else
                                <span class="badge bg-success">
                                    <i class="fas fa-home me-1"></i>
                                    Home Club
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($isRepresentingDifferent)
                            <span class="badge bg-warning text-dark">Representing Another Club</span>
                            @else
                            <span class="badge bg-success">Home Club</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                    data-bs-toggle="modal" data-bs-target="#representationModal{{ $player->id }}">
                                <i class="fas fa-exchange-alt"></i> Change Club Representation
                            </button>
                            
                            <form action="{{ route('tournaments.remove-player', [$tournament, $player]) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Remove player from tournament?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Representation Modal -->
                    <div class="modal fade" id="representationModal{{ $player->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Change Club Representation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('tournaments.update-representation', [$tournament, $player]) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Player</label>
                                            <input type="text" class="form-control" value="{{ $player->display_name }}" readonly>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Home Club</label>
                                            <input type="text" class="form-control" 
                                                   value="{{ $player->ptmClub ? $player->ptmClub->display_name : 'No club assigned' }}" readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label for="representing_ptm_club_id{{ $player->id }}" class="form-label">
                                                Representing Club
                                            </label>
                                            <select class="form-control" id="representing_ptm_club_id{{ $player->id }}" 
                                                    name="representing_ptm_club_id">
                                                <option value="">Home Club ({{ $player->ptmClub ? $player->ptmClub->display_name : 'No club assigned' }})</option>
                                                @foreach($ptmClubs as $club)
                                                @if(!$player->ptmClub || $club->id != $player->ptmClub->id)
                                                <option value="{{ $club->id }}" 
                                                    {{ $player->pivot->representing_ptm_club_id == $club->id ? 'selected' : '' }}>
                                                    {{ $club->display_name }}
                                                </option>
                                                @endif
                                                @endforeach
                                            </select>
                                            <div class="form-text">
                                                Select the club the player will represent. Leave blank to represent their home club.
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="representation_notes{{ $player->id }}" class="form-label">Catatan</label>
                                            <textarea class="form-control" id="representation_notes{{ $player->id }}" 
                                                      name="representation_notes" rows="3" 
                                                      placeholder="Alasan mewakili klub lain...">{{ $player->pivot->representation_notes }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Statistics -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <div class="h4">{{ $players->count() }}</div>
                        <small>Total Pemain</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <div class="h4">{{ $players->where('pivot.is_representing_different_club', false)->count() }}</div>
                        <small>Klub Sendiri</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <div class="h4">{{ $players->where('pivot.is_representing_different_club', true)->count() }}</div>
                        <small>Klub Lain</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <div class="h4">{{ $ptmClubs->count() }}</div>
                        <small>Klub Tersedia</small>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No players in tournament</h5>
            <p class="text-muted">Add players to get started.</p>
            <a href="{{ route('tournaments.draw', $tournament) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Players
            </a>
        </div>
        @endif
    </div>
</div>
@endsection