@extends('layouts.app')

@section('title', 'Tournament Bracket - ' . $tournament->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Tournament Bracket - {{ $tournament->name }}
                    </h4>
                    <div>
                        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Tournament
                        </a>
                        <a href="{{ route('tournaments.draw', $tournament) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-users me-1"></i> Manage Players
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tournament Info -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $tournament->status === 'completed' ? 'success' : ($tournament->status === 'ongoing' ? 'warning' : 'info') }}">
                                {{ ucfirst($tournament->status) }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <strong>Players:</strong> 
                            <span class="badge bg-primary">{{ $tournament->players->count() }}</span>
                        </div>
                        <div class="col-md-2">
                            <strong>Matches:</strong> 
                            <span class="badge bg-info">{{ $tournament->matches->count() }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Progress:</strong>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $tournament->progress }}%;" 
                                     aria-valuenow="{{ $tournament->progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ $tournament->progress }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @if($tournament->status === 'ongoing' && $tournament->matches->where('status', 'scheduled')->count() > 0)
                            <div class="alert alert-info mb-0 py-2">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Ready to start!</strong> {{ $tournament->matches->where('status', 'scheduled')->count() }} matches scheduled
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bracket Container -->
                    <div class="bracket-container">
                        @php
                            // Group matches by round_number dan urutkan
                            $matchesByRound = $tournament->matches->sortBy('round_number')->groupBy('round_number');
                            $maxRounds = $matchesByRound->keys()->max() ?? 1;
                        @endphp

                        <div class="bracket-grid">
                            @foreach($matchesByRound as $roundNumber => $roundMatches)
                                @php
                                    $roundTitle = \App\Helpers\BracketHelper::getRoundTitle($roundNumber, $maxRounds);
                                    $sortedMatches = $roundMatches->sortBy('match_number');
                                @endphp
                                
                                <div class="bracket-round">
                                    <h5 class="round-title">{{ $roundTitle }}</h5>
                                    <div class="matches-container">
                                        @foreach($sortedMatches as $match)
                                            <div class="match-card 
                                                {{ $match->winner_id ? 'match-completed' : '' }} 
                                                {{ $match->status === 'in_progress' ? 'match-in-progress' : '' }}
                                                {{ $match->status === 'pending' ? 'match-pending' : '' }}">
                                                
                                                <div class="match-header">
                                                    <small class="text-muted">
                                                        <i class="fas fa-hashtag me-1"></i>Match {{ $match->match_number }}
                                                    </small>
                                                    @if($match->table)
                                                        <small class="text-muted">
                                                            <i class="fas fa-table-tennis me-1"></i>Table {{ $match->table->name }}
                                                        </small>
                                                    @endif
                                                </div>
                                                
                                                <!-- Player 1 -->
                                                <div class="player-row {{ $match->winner_id == $match->player1_id ? 'winner' : '' }}">
                                                    <div class="player-info">
                                                        <span class="player-name">
                                                            @if($match->player1)
                                                                {{ $match->player1->display_name }}
                                                                @if($match->player1->division_ranking)
                                                                    <span class="division-badge division-{{ $match->player1->division_ranking }}">
                                                                        Div {{ $match->player1->division_ranking }}
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">
                                                                    @if($match->status === 'pending')
                                                                        TBD
                                                                    @else
                                                                        Waiting...
                                                                    @endif
                                                                </span>
                                                            @endif
                                                        </span>
                                                        @if($match->player1 && $match->player1->ptmClub)
                                                            <small class="club-name">
                                                                <i class="fas fa-users me-1"></i>
                                                                {{ $match->player1->ptmClub->name }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    <div class="player-scores">
                                                        @if($match->player1_score !== null)
                                                            <span class="score {{ $match->player1_score > $match->player2_score ? 'winning-score' : '' }}">
                                                                {{ $match->player1_score }}
                                                            </span>
                                                        @elseif($match->player1)
                                                            <span class="score">-</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Player 2 -->
                                                <div class="player-row {{ $match->winner_id == $match->player2_id ? 'winner' : '' }}">
                                                    <div class="player-info">
                                                        <span class="player-name">
                                                            @if($match->player2)
                                                                {{ $match->player2->display_name }}
                                                                @if($match->player2->division_ranking)
                                                                    <span class="division-badge division-{{ $match->player2->division_ranking }}">
                                                                        Div {{ $match->player2->division_ranking }}
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">
                                                                    @if($match->status === 'pending')
                                                                        TBD
                                                                    @else
                                                                        Waiting...
                                                                    @endif
                                                                </span>
                                                            @endif
                                                        </span>
                                                        @if($match->player2 && $match->player2->ptmClub)
                                                            <small class="club-name">
                                                                <i class="fas fa-users me-1"></i>
                                                                {{ $match->player2->ptmClub->name }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                    <div class="player-scores">
                                                        @if($match->player2_score !== null)
                                                            <span class="score {{ $match->player2_score > $match->player1_score ? 'winning-score' : '' }}">
                                                                {{ $match->player2_score }}
                                                            </span>
                                                        @elseif($match->player2)
                                                            <span class="score">-</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Match Info -->
                                                <div class="match-footer">
                                                    <div class="match-status">
                                                        <small class="status-badge status-{{ $match->status }}">
                                                            <i class="fas fa-{{ \App\Helpers\BracketHelper::getStatusIcon($match->status) }} me-1"></i>
                                                            {{ ucfirst(str_replace('_', ' ', $match->status)) }}
                                                        </small>
                                                    </div>
                                                    <div class="match-meta">
                                                        @if($match->match_date)
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar me-1"></i>{{ $match->match_date->format('M j') }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                @if(in_array($match->status, ['scheduled', 'in_progress']) && $match->player1 && $match->player2)
                                                    <div class="match-actions">
                                                        <a href="{{ route('matches.record-score-form', $match) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit me-1"></i>Record Score
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tournament Statistics -->
                    @if($tournament->matches->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Scheduled</h6>
                                    <h3 class="text-primary">{{ $tournament->matches->where('status', 'scheduled')->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">In Progress</h6>
                                    <h3 class="text-warning">{{ $tournament->matches->where('status', 'in_progress')->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Completed</h6>
                                    <h3 class="text-success">{{ $tournament->matches->where('status', 'completed')->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Pending</h6>
                                    <h3 class="text-secondary">{{ $tournament->matches->where('status', 'pending')->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bracket-container {
    overflow-x: auto;
    padding: 20px 0;
}

.bracket-grid {
    display: flex;
    gap: 30px;
    min-width: max-content;
}

.bracket-round {
    min-width: 300px;
}

.round-title {
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 0.9rem;
}

.matches-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.match-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    background: white;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.match-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.match-card.match-completed {
    border-color: #28a745;
    background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
}

.match-card.match-in-progress {
    border-color: #ffc107;
    background: linear-gradient(135deg, #fffbf0 0%, #fff3cd 100%);
    animation: pulse 2s infinite;
}

.match-card.match-pending {
    border-color: #6c757d;
    background: #f8f9fa;
    opacity: 0.7;
}

.match-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f1f1;
}

.player-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-radius: 6px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}

.player-row.winner {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    font-weight: bold;
    border-left: 4px solid #28a745;
}

.player-info {
    flex: 1;
}

.player-name {
    font-weight: 600;
    display: block;
    color: #333;
}

.division-badge {
    font-size: 0.7em;
    padding: 1px 6px;
    border-radius: 10px;
    margin-left: 5px;
    font-weight: normal;
}

/* Division color coding */
.division-1, .division-2, .division-3 { background: #dc3545; color: white; }
.division-4, .division-5, .division-6 { background: #fd7e14; color: white; }
.division-7, .division-8, .division-9 { background: #0dcaf0; color: white; }
.division-10, .division-11 { background: #6c757d; color: white; }

.club-name {
    font-size: 0.75em;
    color: #6c757d;
    display: block;
}

.player-scores {
    margin-left: 10px;
}

.score {
    font-weight: 600;
    min-width: 25px;
    text-align: center;
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    background: #f8f9fa;
}

.winning-score {
    background: #dc3545;
    color: white;
}

.match-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid #f1f1f1;
}

.match-status, .match-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75em;
    font-weight: 500;
}

.status-scheduled { background: #e9ecef; color: #495057; }
.status-in_progress { background: #fff3cd; color: #856404; }
.status-completed { background: #d1ecf1; color: #0c5460; }
.status-pending { background: #f8f9fa; color: #6c757d; border: 1px dashed #dee2e6; }

.match-actions {
    margin-top: 10px;
    text-align: center;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}

/* Responsive */
@media (max-width: 768px) {
    .bracket-grid {
        flex-direction: column;
        gap: 20px;
    }
    
    .bracket-round {
        min-width: auto;
    }
    
    .match-card {
        padding: 12px;
    }
    
    .player-name {
        font-size: 0.9rem;
    }
}
</style>
@endsection
