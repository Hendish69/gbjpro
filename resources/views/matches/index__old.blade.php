@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Matches Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matches.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Schedule Match
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tournament</th>
                        <th>Players</th>
                        <th>Score</th>
                        <th>Date & Time</th>
                        <th>Round</th>
                        <th>Status</th>
                        <th>Winner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matches as $match)
                    <tr>
                        <td>
                            <strong>{{ $match->tournament->name }}</strong>
                        </td>
                        <td>
                            <div class="d-flex align-items-center mb-1">
                                @if($match->player1->photo)
                                    <img src="{{ asset('storage/' . $match->player1->photo) }}" class="player-avatar me-2">
                                @else
                                    <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <span class="{{ $match->winner_id == $match->player1_id ? 'fw-bold text-success' : '' }}">
                                    {{ $match->player1->name }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                @if($match->player2->photo)
                                    <img src="{{ asset('storage/' . $match->player2->photo) }}" class="player-avatar me-2">
                                @else
                                    <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <span class="{{ $match->winner_id == $match->player2_id ? 'fw-bold text-success' : '' }}">
                                    {{ $match->player2->name }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($match->status == 'completed')
                                <span class="fw-bold {{ $match->winner_id == $match->player1_id ? 'text-success' : 'text-dark' }}">
                                    {{ $match->player1_score }}
                                </span>
                                -
                                <span class="fw-bold {{ $match->winner_id == $match->player2_id ? 'text-success' : 'text-dark' }}">
                                    {{ $match->player2_score }}
                                </span>
                            @else
                                <span class="text-muted">VS</span>
                            @endif
                        </td>
                        <td>{{ $match->match_date->format('M d, Y H:i') }}</td>
                        <td>Round {{ $match->round_number }}</td>
                        <td>
                            <span class="badge bg-{{ $match->status == 'completed' ? 'success' : ($match->status == 'live' ? 'warning badge-live' : 'secondary') }}">
                                {{ ucfirst($match->status) }}
                            </span>
                        </td>
                        <td>
                            @if($match->winner)
                                <span class="badge bg-success">{{ $match->winner->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('matches.show', $match) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('matches.edit', $match) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($match->status == 'scheduled')
                            <form action="{{ route('matches.start', $match) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                            @endif
                            @if($match->status == 'live')
                            <form action="{{ route('matches.complete', $match) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-flag"></i>
                                </button>
                            </form>
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