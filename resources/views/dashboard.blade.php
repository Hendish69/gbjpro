@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Players</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_players'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Tournaments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_tournaments'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-trophy fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Ongoing Tournaments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['ongoing_tournaments'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-play-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Completed Matches</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_matches'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Tournaments -->
    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Recent Tournaments</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTournaments as $tournament)
                            <tr>
                                <td>{{ $tournament->name }}</td>
                                <td>{{ $tournament->start_date->format('M d') }} - {{ $tournament->end_date->format('M d') }}</td>
                                <td>
                                    <span class="badge bg-{{ $tournament->status == 'completed' ? 'success' : ($tournament->status == 'ongoing' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($tournament->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $tournament->status == 'completed' ? 'success' : ($tournament->status == 'ongoing' ? 'warning' : 'secondary') }}" 
                                             style="width: {{ $tournament->progress }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $tournament->progress }}%</small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Players -->
    <div class="col-xl-6 col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Registered Players</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Division</th>
                                <th>PTM</th>
                                <th>Tournament</th>
                                <!-- <th>Win Rate</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            @if (!empty($tournament->tournamentPlayers))
                                @foreach($tournament->tournamentPlayers as $player)
                                @php //dd( $player,$tournament); @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($player->player->photo)
                                                <img src="{{ asset('images/' . $player->player->photo) }}" class="player-avatar me-2">
                                            @else
                                                <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $player->player->nickname }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-primary">{{ $player->player->division_ranking }}</span></td>
                                    <td><span class="badge bg-primary">{{ $player->representingClub->name }}</span></td>
                                    <td>{{ $tournament->name }}</td>
                                    <!-- <td>
                                        <span class="badge bg-{{ $player->win_rate >= 50 ? 'success' : 'warning' }}">
                                            {{ $player->win_rate }}%
                                        </span>
                                    </td> -->
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Matches -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Matches</h6>
            </div>
            <div class="card-body">
                @if($upcomingMatches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tournament</th>
                                <th>Players</th>
                                <th>Date & Time</th>
                                <th>Round</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingMatches as $match)
                            <tr>
                                <td>{{ $match->tournament->name }}</td>
                                <td>
                                    <strong>{{ $match->player1->name }}</strong> vs <strong>{{ $match->player2->name }}</strong>
                                </td>
                                <td>{{ $match->match_date->format('M d, Y H:i') }}</td>
                                <td>Round {{ $match->round_number }}</td>
                                <td>
                                    <span class="badge bg-{{ $match->status == 'live' ? 'warning badge-live' : 'secondary' }}">
                                        {{ ucfirst($match->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">No upcoming matches scheduled.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection