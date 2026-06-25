@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Match Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matches.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Matches
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Match Info -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Match Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Tournament:</strong></td>
                        <td>{{ $match->tournament->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Round:</strong></td>
                        <td>Round {{ $match->round_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Date & Time:</strong></td>
                        <td>{{ $match->match_date->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $match->status == 'completed' ? 'success' : ($match->status == 'live' ? 'warning badge-live' : 'secondary') }}">
                                {{ ucfirst($match->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($match->table)
                    <tr>
                        <td><strong>Table:</strong></td>
                        <td>
                            {{ $match->table->name }}
                            @if($match->table->location)
                                ({{ $match->table->location }})
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($match->duration_minutes)
                    <tr>
                        <td><strong>Duration:</strong></td>
                        <td>{{ $match->duration_formatted }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Players Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Players</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="player-info {{ $match->winner_id == $match->player1_id ? 'winner' : '' }}">
                            @if($match->player1->photo)
                                <img src="{{ asset('storage/' . $match->player1->photo) }}" 
                                     class="player-avatar mb-2" style="width: 80px; height: 80px;">
                            @else
                                <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-2" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <h5 class="mb-1">{{ $match->player1->name }}</h5>
                            <div class="divisi">Divisi: {{ $match->player1->divisi }}</div>
                            @if($match->status == 'completed')
                                <div class="score h4 {{ $match->winner_id == $match->player1_id ? 'text-success' : 'text-danger' }}">
                                    {{ $match->player1_score }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="player-info {{ $match->winner_id == $match->player2_id ? 'winner' : '' }}">
                            @if($match->player2->photo)
                                <img src="{{ asset('storage/' . $match->player2->photo) }}" 
                                     class="player-avatar mb-2" style="width: 80px; height: 80px;">
                            @else
                                <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-2" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <h5 class="mb-1">{{ $match->player2->name }}</h5>
                            <div class="divisi">Divisi: {{ $match->player2->divisi }}</div>
                            @if($match->status == 'completed')
                                <div class="score h4 {{ $match->winner_id == $match->player2_id ? 'text-success' : 'text-danger' }}">
                                    {{ $match->player2_score }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($match->status == 'completed' && $match->winner)
                <div class="text-center mt-3 p-3 bg-light rounded">
                    <h5 class="text-success mb-0">
                        <i class="fas fa-trophy"></i> 
                        Winner: {{ $match->winner->name }}
                    </h5>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sets & Actions -->
    <div class="col-md-6">
        <!-- Sets -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Sets</h6>
                <span class="badge bg-primary">{{ $match->sets->count() }} sets</span>
            </div>
            <div class="card-body">
                @if($match->sets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Set</th>
                                <th>{{ $match->player1->name }}</th>
                                <th>{{ $match->player2->name }}</th>
                                <th>Winner</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($match->sets as $set)
                            <tr>
                                <td>Set {{ $set->set_number }}</td>
                                <td class="{{ $set->player1_score > $set->player2_score ? 'fw-bold text-success' : '' }}">
                                    {{ $set->player1_score }}
                                </td>
                                <td class="{{ $set->player2_score > $set->player1_score ? 'fw-bold text-success' : '' }}">
                                    {{ $set->player2_score }}
                                </td>
                                <td>
                                    @if($set->player1_score > $set->player2_score)
                                        {{ $match->player1->name }}
                                    @elseif($set->player2_score > $set->player1_score)
                                        {{ $match->player2->name }}
                                    @else
                                        Draw
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">No sets recorded yet.</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Match Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($match->status == 'scheduled')
                    <form action="{{ route('matches.start', $match) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="fas fa-play"></i> Start Match
                        </button>
                    </form>
                    @endif

                    @if($match->status == 'live')
                    <a href="{{ route('matches.record-score', $match) }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-pencil-alt"></i> Record Score
                    </a>
                    <form action="{{ route('matches.complete', $match) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg mt-2">
                            <i class="fas fa-flag"></i> Complete Match
                        </button>
                    </form>
                    @endif

                    @if($match->status == 'completed')
                    <div class="alert alert-info text-center">
                        <i class="fas fa-check-circle"></i> Match Completed
                    </div>
                    @endif

                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('matches.edit', $match) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('matches.destroy', $match) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" 
                                    onclick="return confirm('Are you sure you want to delete this match?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.player-info.winner {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    padding: 15px;
    border-radius: 10px;
    border: 2px solid #28a745;
}
.player-info {
    padding: 15px;
    border-radius: 10px;
    transition: all 0.3s;
}
.player-info:hover {
    background-color: #f8f9fa;
}
.divisi {
    color: #6c757d;
    font-size: 0.9rem;
}
.score {
    font-weight: bold;
    margin-top: 10px;
}
</style>
@endsection