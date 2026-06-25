@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Player Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Players
        </a>
    </div>
</div>

<div class="row">
    <!-- Player Info -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                @if($player->photo)
                  
                    <img src="{{ asset('images/' . $player->photo) }}" 
                         class="player-avatar mb-3" style="width: 150px; height: 150px;">
                @else
                    <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 150px; height: 150px; font-size: 3rem;">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                
                <h3 class="card-title">{{ $player->name }}</h3>
                <p class="text-muted">{{ $player->nickname }}</p>
                
                @if($player->phone)
                <p class="card-text">
                    <i class="fas fa-phone me-2"></i>{{ $player->phone }}
                </p>
                @endif
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="badge bg-primary fs-6">Divisi: {{ $player->division_ranking }}</span>
                </div>

                @if($player->bio)
                <div class="card-text">
                    <h6>About:</h6>
                    <p class="text-muted">{{ $player->bio }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Stats Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Player Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="h4 text-primary mb-0">{{ $player->total_matches }}</div>
                        <small class="text-muted">Total Matches</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-success mb-0">{{ $player->wins_count }}</div>
                        <small class="text-muted">Wins</small>
                    </div>
                    <div class="col-4">
                        <div class="h4 text-danger mb-0">{{ $player->losses_count }}</div>
                        <small class="text-muted">Losses</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <div class="h4 mb-0 {{ $player->win_rate >= 50 ? 'text-success' : 'text-warning' }}">
                        {{ $player->win_rate }}%
                    </div>
                    <small class="text-muted">Win Rate</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Match History -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Match History</h6>
                <span class="badge bg-primary">{{ $player->all_matches->count() }} matches</span>
            </div>
            <div class="card-body">
                @if($player->all_matches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tournament</th>
                                <th>Opponent</th>
                                <th>Score</th>
                                <th>Date</th>
                                <th>Result</th>
                                <th>Round</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($player->all_matches as $match)
                            @php
                                $isPlayer1 = $match->player1_id == $player->id;
                                $opponent = $isPlayer1 ? $match->player2 : $match->player1;
                                $playerScore = $isPlayer1 ? $match->player1_score : $match->player2_score;
                                $opponentScore = $isPlayer1 ? $match->player2_score : $match->player1_score;
                                $isWinner = $match->winner_id == $player->id;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $match->tournament->name }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($opponent->photo)
                                            <img src="{{ asset('storage/' . $opponent->photo) }}" 
                                                 class="player-avatar me-2" style="width: 30px; height: 30px;">
                                        @else
                                            <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                                 style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                        {{ $opponent->name }}
                                    </div>
                                </td>
                                <td>
                                    @if($match->status == 'completed')
                                        <span class="fw-bold {{ $isWinner ? 'text-success' : 'text-danger' }}">
                                            {{ $playerScore }}
                                        </span>
                                        -
                                        <span class="fw-bold {{ !$isWinner ? 'text-success' : 'text-danger' }}">
                                            {{ $opponentScore }}
                                        </span>
                                    @else
                                        <span class="text-muted">VS</span>
                                    @endif
                                </td>
                                <td>{{ $match->match_date->format('M d, Y') }}</td>
                                <td>
                                    @if($match->status == 'completed')
                                        <span class="badge bg-{{ $isWinner ? 'success' : 'danger' }}">
                                            {{ $isWinner ? 'WON' : 'LOST' }}
                                        </span>
                                    @else
                                        <span class="badge bg-{{ $match->status == 'live' ? 'warning' : 'secondary' }}">
                                            {{ strtoupper($match->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>Round {{ $match->round_number }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-table-tennis-paddle-ball fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No matches played yet.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Performance -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Recent Performance</h6>
            </div>
            <div class="card-body">
                @php
                    $recentMatches = $player->all_matches->where('status', 'completed')->take(10);
                    $wins = $recentMatches->filter(function($match) use ($player) {
                        return $match->winner_id == $player->id;
                    })->count();
                    $losses = $recentMatches->count() - $wins;
                @endphp
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-success mb-0">{{ $wins }}</div>
                        <small class="text-muted">Wins (Last 10)</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-danger mb-0">{{ $losses }}</div>
                        <small class="text-muted">Losses (Last 10)</small>
                    </div>
                </div>
                
                @if($recentMatches->count() > 0)
                <div class="mt-3">
                    @foreach($recentMatches as $match)
                    @php
                        $isWinner = $match->winner_id == $player->id;
                    @endphp
                    <span class="badge bg-{{ $isWinner ? 'success' : 'danger' }} me-1 mb-1" 
                          title="{{ $match->tournament->name }} - {{ $match->match_date->format('M d') }}">
                        {{ $isWinner ? 'W' : 'L' }}
                    </span>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center mt-3">No completed matches yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('players.edit', $player) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Player
            </a>
            <form action="{{ route('players.destroy', $player) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this player?')">
                    <i class="fas fa-trash"></i> Delete Player
                </button>
            </form>
        </div>
    </div>
</div>
@endsection