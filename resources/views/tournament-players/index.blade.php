@extends('layouts.app')

@section('title', 'Tournament Players - ' . $tournament->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Tournament Players - {{ $tournament->name }}
                    </h4>
                    <div>
                        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <a href="{{ route('tournaments.players.create', $tournament) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Add Player
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($tournamentPlayers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Seed</th>
                                        <th>Player</th>
                                        <th>Club</th>
                                        <th>Representing</th>
                                        <th>Group</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tournamentPlayers as $tp)
                                    <tr>
                                        <td>
                                            @if($tp->seed)
                                                <span class="badge bg-primary">#{{ $tp->seed }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $tp->player->display_name }}</strong>
                                            @if($tp->player->ptm_number)
                                                <br><small class="text-muted">{{ $tp->player->ptm_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $tp->player->ptmClub->name ?? 'No Club' }}
                                        </td>
                                        <td>
                                            @if($tp->is_representing_different_club)
                                                <span class="badge bg-warning">
                                                    {{ $tp->representingClub->name ?? 'Unknown Club' }}
                                                </span>
                                                @if($tp->representation_notes)
                                                    <br><small class="text-muted">{{ Str::limit($tp->representation_notes, 30) }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-success">Original Club</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tp->group)
                                                <span class="badge bg-info">{{ $tp->group }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('tournaments.players.edit', [$tournament, $tp]) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('tournaments.players.destroy', [$tournament, $tp]) }}" 
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Players Registered</h4>
                            <p class="text-muted">Add players to this tournament to get started.</p>
                            <a href="{{ route('tournaments.players.create', $tournament) }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i> Add First Player
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection