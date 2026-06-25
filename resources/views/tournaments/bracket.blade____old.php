@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tournament Bracket: {{ $tournament->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tournament
        </a>
    </div>
</div>

@if($tournament->matches->count() === 0)
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No matches generated yet. 
    <a href="{{ route('tournaments.draw', $tournament) }}" class="alert-link">
        Generate tournament draw first.
    </a>
</div>
@else
<div class="bracket-container">
    @foreach($bracketData as $round)
    <div class="bracket-round">
        <div class="round-header">
            <h5>{{ $round['title'] }}</h5>
            <small class="text-muted">{{ $round['matches']->count() }} matches</small>
        </div>
        
        <div class="matches-container">
            @foreach($round['matches'] as $match)
            <div class="match-card {{ $match->status == 'completed' ? 'completed' : '' }} 
                         {{ $match->status == 'live' ? 'live' : '' }}">
                <div class="match-header">
                    <small class="text-muted">Match #{{ $match->match_number }}</small>
                    @if($match->table)
                    <small class="text-muted">Table: {{ $match->table->name }}</small>
                    @endif
                </div>
                
                <div class="players-container">
                    <!-- Player 1 -->
                    <div class="player-slot {{ $match->winner_id == $match->player1_id ? 'winner' : '' }}">
                        <div class="player-info">
                            @if($match->player1)
                            <div class="player-name">
                                {{ $match->player1->name }}
                                @if($match->player1->division_ranking)
                                <small class="rating">({{ $match->player1->division_ranking }})</small>
                                @endif
                            </div>
                            @if($match->status == 'completed')
                            <div class="player-score {{ $match->winner_id == $match->player1_id ? 'winning-score' : '' }}">
                                {{ $match->player1_score }}
                            </div>
                            @endif
                            @else
                            <div class="player-name text-muted">TBD</div>
                            @endif
                        </div>
                    </div>

                    <!-- VS -->
                    <div class="vs-divider">VS</div>

                    <!-- Player 2 -->
                    <div class="player-slot {{ $match->winner_id == $match->player2_id ? 'winner' : '' }}">
                        <div class="player-info">
                            @if($match->player2)
                            <div class="player-name">
                                {{ $match->player2->name }}
                                @if($match->player2->division_ranking)
                                <small class="rating">({{ $match->player2->division_ranking }})</small>
                                @endif
                            </div>
                            @if($match->status == 'completed')
                            <div class="player-score {{ $match->winner_id == $match->player2_id ? 'winning-score' : '' }}">
                                {{ $match->player2_score }}
                            </div>
                            @endif
                            @else
                            <div class="player-name text-muted">TBD</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="match-footer">
                    <div class="match-status">
                        <span class="badge bg-{{ $match->status == 'completed' ? 'success' : ($match->status == 'live' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($match->status) }}
                        </span>
                    </div>
                    <div class="match-actions">
                        <a href="{{ route('matches.show', $match) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endif

<style>
.bracket-container {
    display: flex;
    overflow-x: auto;
    gap: 20px;
    padding: 20px 0;
}

.bracket-round {
    min-width: 300px;
}

.round-header {
    text-align: center;
    margin-bottom: 20px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}

.matches-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.match-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background: white;
    transition: all 0.3s ease;
}

.match-card.live {
    border-color: #ffc107;
    background: #fffdf6;
    animation: pulse 2s infinite;
}

.match-card.completed {
    border-color: #28a745;
    background: #f8fff9;
}

.match-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.8rem;
}

.players-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.player-slot {
    padding: 8px 12px;
    border-radius: 5px;
    border: 1px solid #e9ecef;
}

.player-slot.winner {
    background: #d4edda;
    border-color: #c3e6cb;
}

.player-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.player-name {
    font-weight: 500;
}

.rating {
    color: #6c757d;
    font-size: 0.8rem;
}

.player-score {
    font-weight: bold;
    font-size: 1.1rem;
}

.winning-score {
    color: #28a745;
}

.vs-divider {
    text-align: center;
    font-weight: bold;
    color: #6c757d;
    margin: 5px 0;
}

.match-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* Responsive design */
@media (max-width: 768px) {
    .bracket-container {
        flex-direction: column;
    }
    
    .bracket-round {
        min-width: 100%;
    }
}
</style>
@endsection