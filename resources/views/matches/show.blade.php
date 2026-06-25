@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Match Details #{{ $match->id }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Match Info</h5>
                            <p><strong>Tournament:</strong> {{ $match->tournament->name }}</p>
                            <p><strong>Type:</strong> {{ ucfirst($match->match_type) }}</p>
                            <p><strong>Format:</strong> {{ str_replace('_', ' ', ucfirst($match->match_format)) }}</p>
                            <p><strong>Round:</strong> {{ $match->round_number }}</p>
                            <p><strong>Match #:</strong> {{ $match->match_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Status & Schedule</h5>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $match->status_color }}">{{ ucfirst($match->status) }}</span>
                            </p>
                            <p><strong>Date:</strong> {{ $match->match_date->format('M d, Y H:i') }}</p>
                            <p><strong>Duration:</strong> {{ $match->duration_formatted }}</p>
                            <p><strong>Table:</strong> {{ $match->table->name ?? 'Not assigned' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5>Participants</h5>
                    <div class="row text-center">
                        <div class="col-md-5">
                            @if($match->match_type === 'team')
                                <h6>{{ $match->team1->name ?? 'TBA' }}</h6>
                                <p class="h4 {{ $match->winning_side === 'team1' ? 'text-success fw-bold' : '' }}">
                                    {{ $match->team1_score ?? '-' }}
                                </p>
                            @else
                                <h6>{{ $match->player1->name ?? 'TBA' }}</h6>
                                @if($match->match_type === 'double')
                                    <small>Partner: {{ $match->player1Partner->name ?? 'TBA' }}</small>
                                @endif
                                <p class="h4 {{ $match->winning_side === 'player1' ? 'text-success fw-bold' : '' }}">
                                    {{ $match->player1_score ?? '-' }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <p class="h4">VS</p>
                        </div>
                        <div class="col-md-5">
                            @if($match->match_type === 'team')
                                <h6>{{ $match->team2->name ?? 'TBA' }}</h6>
                                <p class="h4 {{ $match->winning_side === 'team2' ? 'text-success fw-bold' : '' }}">
                                    {{ $match->team2_score ?? '-' }}
                                </p>
                            @else
                                <h6>{{ $match->player2->name ?? 'TBA' }}</h6>
                                @if($match->match_type === 'double')
                                    <small>Partner: {{ $match->player2Partner->name ?? 'TBA' }}</small>
                                @endif
                                <p class="h4 {{ $match->winning_side === 'player2' ? 'text-success fw-bold' : '' }}">
                                    {{ $match->player2_score ?? '-' }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($match->isCompleted && $match->winner)
                    <div class="alert alert-success mt-3">
                        <strong>Winner:</strong> 
                        @if($match->match_type === 'team')
                            {{ $match->team1->name ?? $match->team2->name }}
                        @else
                            {{ $match->player1->name ?? $match->player2->name }}
                        @endif
                    </div>
                    @endif

                    @if($match->sets->count() > 0)
                    <hr>
                    <h5>Set Scores</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Set</th>
                                    <th>{{ $match->match_type === 'team' ? $match->team1->name : $match->player1->name }}</th>
                                    <th>{{ $match->match_type === 'team' ? $match->team2->name : $match->player2->name }}</th>
                                    <th>Winner</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($match->sets as $set)
                                <tr>
                                    <td>Set {{ $set->set_number }}</td>
                                    <td>{{ $set->player1_score }}</td>
                                    <td>{{ $set->player2_score }}</td>
                                    <td>
                                        <span class="badge bg-{{ $set->winner === 'player1' ? 'success' : 'secondary' }}">
                                            {{ $set->winner === 'player1' ? ($match->match_type === 'team' ? $match->team1->name : $match->player1->name) : ($match->match_type === 'team' ? $match->team2->name : $match->player2->name) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="btn-group">
                        <a href="{{ route('matches.edit', $match->id) }}" class="btn btn-warning">Edit</a>
                        <a href="{{ route('matches.score-form', $match->id) }}" class="btn btn-primary">Record Score</a>
                        
                        @if($match->isScheduled)
                            <form action="{{ route('matches.start', $match->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">Start Match</button>
                            </form>
                        @endif

                        @if($match->isOngoing)
                            <form action="{{ route('matches.update-status', $match->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success">Complete Match</button>
                            </form>
                        @endif

                        <form action="{{ route('matches.destroy', $match->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection