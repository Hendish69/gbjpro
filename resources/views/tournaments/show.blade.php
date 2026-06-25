@extends('layouts.app')

@section('title', $tournament->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Tournament Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $tournament->name }}</h4>
                        <div>
                            <span class="badge bg-light text-dark">{{ ucfirst($tournament->type) }}</span>
                            <span class="badge bg-info">{{ ucfirst($tournament->format) }}</span>
                            <span class="badge bg-{{ $tournament->status_color }}">{{ ucfirst($tournament->status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="card-text">{{ $tournament->description }}</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Start Date:</strong><br>
                                    {{ $tournament->start_date->format('d M Y') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>End Date:</strong><br>
                                    {{ $tournament->end_date->format('d M Y') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Registration Deadline:</strong><br>
                                    {{ $tournament->registration_deadline->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="mb-3">
                                    <h5>Progress</h5>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" 
                                             style="width: {{ $tournament->progress }}%;">
                                            {{ $tournament->progress }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h6>Players</h6>
                                        <h4>{{ $tournament->players->count() }}/{{ $tournament->max_players }}</h4>
                                    </div>
                                    <div class="col-6">
                                        <h6>Matches</h6>
                                        <h4>{{ $tournament->total_matches }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('tournaments.edit', $tournament) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit Tournament
                            </a>
                            <a href="{{ route('tournaments.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            {{-- 🔥 TAMBAHKAN 2 BUTTON BARU INI --}}
                            <a href="{{ route('tournaments.registration-management', $tournament->id) }}" 
                            class="btn btn-info btn-sm">
                                <i class="fas fa-users me-1"></i> Manage Registration
                            </a>
                            
                            <a href="{{ route('tournaments.schedule-management', $tournament->id) }}" 
                            class="btn btn-success btn-sm">
                                <i class="fas fa-calendar-alt me-1"></i> Schedule Matches
                            </a>
                        </div>
                        <div>
                            @if($tournament->is_registration_open)
                                <span class="badge bg-success">Registration Open</span>
                            @else
                                <span class="badge bg-danger">Registration Closed</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $tournament->completed_matches }}</h4>
                                    <p>Completed Matches</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $tournament->players->count() }}</h4>
                                    <p>Registered Players</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $tournament->remaining_spots }}</h4>
                                    <p>Remaining Spots</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-plus fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $tournament->available_tables }}</h4>
                                    <p>Available Tables</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-table-tennis fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Estimation & Table Statistics Sections -->
            <!-- ... (keep your existing schedule and table statistics sections) ... -->

            <!-- Matches Section -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Matches</h5>
                        <span class="badge bg-light text-dark">{{ $tournament->matches->count() }} matches</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($tournament->matches->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Match ID</th>
                                    <th>Players</th>
                                    <th>Round</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tournament->matches as $match)
                                <tr>
                                    <td>#{{ $match->id }}</td>
                                    <td>
                                        @if($match->player1 && $match->player2)
                                            {{ $match->player1->name }} vs {{ $match->player2->name }}
                                        @else
                                            TBD vs TBD
                                        @endif
                                    </td>
                                    <td>{{ $match->round ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $match->status == 'completed' ? 'success' : ($match->status == 'ongoing' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($match->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($match->match_date)
                                            {{ $match->match_date->format('M d, Y H:i') }}
                                        @else
                                            Not scheduled
                                        @endif
                                    </td>
                                    <td>{{ $match->duration_minutes ?? 0 }} min</td>
                                    <td>
                                        <button class="btn btn-sm btn-info">View</button>
                                        <button class="btn btn-sm btn-warning">Edit</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-table-tennis fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Matches Yet</h5>
                        <p class="text-muted">Matches will be generated after the tournament draw.</p>
                        @if($tournament->status === 'registration_open' || $tournament->status === 'ongoing')
                       
                            <button type="submit" onclick="generatepdf()" class="btn btn-primary">
                                <i class="fas fa-magic"></i> Generate Draw
                            </button>
                      
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Registered Players -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Registered Players ({{ $tournament->players->count() }})</h5>
                        @if($tournament->is_registration_open)
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                            <i class="fas fa-user-plus"></i> Add Player
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($tournament->players->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Player Name</th>
                                        <th>Division</th>
                                        <th>Club</th>
                                        <th>Representing Club</th>
                                        <th>Pair Status</th>
                                        <th>Seed</th>
                                        <th>Group</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tournament->tournamentPlayers as $index => $player)
                                    @php
                                        //dd($player);
                                        $player = $player->player;
                                        $tournamentPlayer = $tournament->tournamentPlayers->where('player_id', $player->id)->first();
                                        $playerPair = $tournament->getPlayerPair($player->id);
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $player->nickname }}</td>
                                         <td>{{ $player->division_ranking }}</td>
                                        <td>{{ $player->ptmClub->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($tournamentPlayer && $tournamentPlayer->representingClub)
                                                {{ $tournamentPlayer->representingClub->name }}
                                                @if($tournamentPlayer->is_representing_different_club)
                                                    <span class="badge bg-warning">Different</span>
                                                @endif
                                            @else
                                                Same as Club
                                            @endif
                                        </td>
                                        <td>
                                            @if($tournament->isDuoType())
                                                @if($playerPair)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-user-friends me-1"></i>
                                                        Paired with {{ $playerPair->player1_id == $player->id ? $playerPair->player2->nickname.' '.$playerPair->player2->division_ranking : $playerPair->player1->nickname.' '.$playerPair->player1->division_ranking }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-user me-1"></i>
                                                        Unpaired
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-info">Single</span>
                                            @endif
                                        </td>
                                        <td>{{ $tournamentPlayer->seed ?? 'N/A' }}</td>
                                        <td>{{ $tournamentPlayer->group ?? 'N/A' }}</td>
                                        <td>
                                            @if($tournament->is_registration_open && !$playerPair)
                                            <div class="btn-group" role="group">
                                                <!-- Change Player Button -->
                                                <button type="button" class="btn btn-sm btn-info me-2" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#changePlayerModal"
                                                    data-player-id="{{ $player->id }}"
                                                    data-player-name="{{ $player->nickname }}"
                                                    onclick="setChangeFormData(this)">
                                                    <i class="fas fa-exchange-alt"></i> Change
                                                </button>
                                                <!-- <div class="vr mx-2"></div> garis pemisah -->

                                                <!-- Edit Player Button -->
                                                <button type="button" class="btn btn-sm btn-warning me-2" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editPlayerModal"
                                                        data-player-id="{{ $player->id }}"
                                                        data-player-name="{{ $player->nickname }}"
                                                        data-seed="{{ $tournamentPlayer->seed ?? '' }}"
                                                        data-group="{{ $tournamentPlayer->group ?? '' }}"
                                                        data-representing-club="{{ $tournamentPlayer->representing_ptm_club_id ?? '' }}"
                                                        data-is-representing-different="{{ $tournamentPlayer->is_representing_different_club ?? false }}"
                                                        onclick="setEditFormData(this)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                
                                                <div class="vr mx-2"></div>
                                                <form action="{{ route('tournaments.remove-player', [$tournament, $player]) }}" 
                                                    method="POST" class="d-inline" 
                                                    onsubmit="return confirm('Remove this player from tournament?')">
                                                    @csrf
                                                    
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Remove
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Players Registered Yet</h5>
                            <p class="text-muted">Add players to start the tournament registration.</p>
                            @if($tournament->is_registration_open)
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                                <i class="fas fa-user-plus"></i> Add First Player
                            </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="btn-group" role="group">
                        <a href="{{ route('tournaments.edit', $tournament) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit Tournament
                        </a>
                        {{-- 🔥 TAMBAHKAN 2 BUTTON BARU INI --}}
                        <a href="{{ route('tournaments.registration-management', $tournament->id) }}" 
                        class="btn btn-info me-2">
                            <i class="fas fa-users me-1"></i> Registration
                        </a>
                        
                        <a href="{{ route('tournaments.schedule-management', $tournament->id) }}" 
                        class="btn btn-success me-2">
                            <i class="fas fa-calendar-alt me-1"></i> Schedule
                        </a>
                        
                        @if($tournament->status === 'registration_open' && $tournament->players->count() >= 2)
                        <form action="{{ route('tournaments.pairing', $tournament) }}" method="GET" class="d-inline me-2">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-handshake"></i> Doubles Pairing
                            </button>
                        </form>

                            <button type="submit" onclick="generatepdf()" class="btn btn-primary">
                                <i class="fas fa-magic"></i> Generate Draw
                            </button>
                    
        
                        <!-- <a href="{{ route('tournaments.pairing',$tournament) }}" class="btn btn-success me-2">
                            <i class="fas fa-handshake"></i> Pairing
                        </a> -->
                        @endif
                        
                        <!-- Status Management Buttons -->
                        @if($tournament->status === 'pending')
                        <form action="{{ route('tournaments.update-status', $tournament) }}" method="POST" class="d-inline me-2">
                            @csrf
                            <input type="hidden" name="status" value="registration_open">
                            <button type="submit" class="btn btn-info" 
                                    onclick="return confirm('Open registration for this tournament?')">
                                <i class="fas fa-door-open"></i> Open Registration
                            </button>
                        </form>
                        @endif

                        @if($tournament->status === 'registration_open')
                        <form action="{{ route('tournaments.update-status', $tournament) }}" method="POST" class="d-inline me-2">
                            @csrf
                            <input type="hidden" name="status" value="ongoing">
                            <button type="submit" class="btn btn-success" 
                                    onclick="return confirm('Start the tournament? This will close registration.')">
                                <i class="fas fa-play"></i> Start Tournament
                            </button>
                        </form>
                        @endif

                        @if($tournament->status === 'ongoing')
                        <form action="{{ route('tournaments.update-status', $tournament) }}" method="POST" class="d-inline me-2">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success" 
                                    onclick="return confirm('Mark tournament as completed?')">
                                <i class="fas fa-flag-checkered"></i> Complete Tournament
                            </button>
                        </form>
                        @endif

                        @if(in_array($tournament->status, ['pending', 'registration_open']))
                        <form action="{{ route('tournaments.update-status', $tournament) }}" method="POST" class="d-inline me-2">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Cancel this tournament? This action cannot be undone.')">
                                <i class="fas fa-times"></i> Cancel Tournament
                            </button>
                        </form>
                        @endif

                        @if(in_array($tournament->status, ['completed', 'cancelled']))
                        <form action="{{ route('tournaments.update-status', $tournament) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="btn btn-secondary" 
                                    onclick="return confirm('Reset tournament to pending?')">
                                <i class="fas fa-undo"></i> Reset to Pending
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Edit Player Modal -->
<div class="modal fade" id="editPlayerModal" tabindex="-1" aria-labelledby="editPlayerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlayerModalLabel">Edit Player Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPlayerForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="player_id" id="edit_player_id">
                    
                    <div class="mb-3">
                        <label for="edit_seed" class="form-label">Seed</label>
                        <input type="number" class="form-control" id="edit_seed" name="seed" min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_group" class="form-label">Group</label>
                        <input type="text" class="form-control" id="edit_group" name="group" placeholder="Enter group name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_representing_club" class="form-label">Representing Club</label>
                        <select class="form-select" id="edit_representing_club" name="representing_club_id">
                            <option value="">Same as Player's Club</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}">{{ $club->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="edit_is_representing_different" name="is_representing_different_club">
                        <label class="form-check-label" for="edit_is_representing_different">
                            Representing different club
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Player</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Change Player Modal -->
<div class="modal fade" id="changePlayerModal" tabindex="-1" aria-labelledby="changePlayerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePlayerModalLabel">Change Player</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePlayerForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="current_player_id" id="change_current_player_id">

                    <div class="mb-3">
                        <label class="form-label">Current Player</label>
                        <input type="text" class="form-control" id="change_current_player_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="new_player_id" class="form-label">Select New Player</label>
                        <select class="form-select" id="new_player_id" name="new_player_id" required>
                            <option value="">-- Choose New Player --</option>
                            @foreach($availablePlayers as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->nickname }} 
                                    @if($p->ptmClub)
                                        - {{ $p->ptmClub->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="change_seed" class="form-label">Seed</label>
                        <input type="number" class="form-control" id="change_seed" name="seed" min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="change_group" class="form-label">Group</label>
                        <input type="text" class="form-control" id="change_group" name="group" placeholder="Enter group name">
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This will replace the current player with the new selected player while keeping all tournament settings (seed, group, pairing, etc.).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Player</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add Player Modal -->
<!-- Add Player Modal - Support untuk Duo Tournaments -->
<!-- Create Pair Modal (Manual Pairing) -->
<!-- Debug: Check modal conditions -->
<div style="display: none;">
    <p>Debug Modal Conditions:</p>
    <p>is_registration_open: {{ $tournament->is_registration_open ? 'true' : 'false' }}</p>
    <p>isDuoType: {{ $tournament->isDuoType() ? 'true' : 'false' }}</p>
    <p>availableForPairing count: {{ $availableForPairing->count() ?? 0 }}</p>
    <p>has success session: {{ session('success') ? 'true' : 'false' }}</p>
</div>

<!-- Add Player Modal -->
@if($tournament->is_registration_open)
<!-- DEBUG: Add Player Modal Rendered -->
<div class="modal fade" id="addPlayerModal" tabindex="-1" aria-labelledby="addPlayerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlayerModalLabel">
                    @if($tournament->isDuoType())
                        <i class="fas fa-user-friends me-2"></i>Add Duo Players
                    @else
                        <i class="fas fa-user-plus me-2"></i>Add Player
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($tournament->isDuoType())
                    <!-- Duo tournament form -->
                    <ul class="nav nav-tabs mb-4" id="addPlayerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-tab-pane" type="button" role="tab">
                                <i class="fas fa-user me-1"></i> Add Single Player
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pair-tab" data-bs-toggle="tab" data-bs-target="#pair-tab-pane" type="button" role="tab">
                                <i class="fas fa-user-friends me-1"></i> Add Pair
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="addPlayerTabsContent">
                        <!-- Single Player Tab -->
                        <!-- Di dalam modal add player - bagian single tab -->
                        <div class="tab-pane fade show active" id="single-tab-pane" role="tabpanel" tabindex="0">
                            <form action="{{ route('tournaments.add-player', $tournament) }}" method="POST" id="addSinglePlayerForm">
                                @csrf
                                <input type="hidden" name="registration_type" value="single">
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Player will be registered individually and can be paired later.
                                </div>

                                <div class="mb-3">
                                    <label for="player_id" class="form-label">Select Player *</label>
                                    <select class="form-control" id="player_id" name="player_id" required>
                                        <option value="">Choose a player...</option>
                                        @foreach($availablePlayers as $player)
                                            <option value="{{ $player->id }}" data-club="{{ $player->ptm_club_id }}">
                                                {{ $player->nickname }} - {{ $player->division_ranking }} 
                                                @if($player->ptmClub)
                                                    - {{ $player->ptmClub->name }} ( {{ $player->name }} )
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="seed" class="form-label">Seed</label>
                                            <input type="number" class="form-control" id="seed" name="seed" placeholder="Optional">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="group" class="form-label">Group</label>
                                            <input type="text" class="form-control" id="group" name="group" placeholder="A, B, C, etc.">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Club Representation Section -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Club Representation (Optional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="representing_ptm_club_id" class="form-label">Representing Club</label>
                                            <select class="form-control" id="representing_ptm_club_id" name="representing_ptm_club_id">
                                                <option value="">Same as player's club</option>
                                                @foreach($clubs as $club)
                                                    <option value="{{ $club->id }}">{{ $club->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="representation_notes" class="form-label">Representation Notes</label>
                                            <textarea class="form-control" id="representation_notes" name="representation_notes" rows="2" placeholder="Optional notes about club representation..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Pair Tab -->
                        <!-- Di dalam modal add player - bagian pair tab -->
                        <div class="tab-pane fade" id="pair-tab-pane" role="tabpanel" tabindex="0">
                            <form action="{{ route('tournaments.add-pair', $tournament) }}" method="POST" id="addPairForm">
                                @csrf
                                
                                <div class="alert alert-success">
                                    <i class="fas fa-user-friends me-2"></i>
                                    Register two players as a pair/team.
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="pair_name" class="form-label">Pair/Team Name (Optional)</label>
                                            <input type="text" class="form-control" id="pair_name" name="pair_name" placeholder="e.g., Team A, Dynamic Duo">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="team_name" class="form-label">Team Name (Optional)</label>
                                            <input type="text" class="form-control" id="team_name" name="team_name" placeholder="e.g., Club Name">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Player 1 *</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="player1_id" class="form-label">Select Player</label>
                                                    <select class="form-control" id="player1_id" name="player1_id" required>
                                                        <option value="">Choose player 1...</option>
                                                        @foreach($availablePlayers as $player)
                                                            <option value="{{ $player->id }}">
                                                                {{ $player->nickname }} - {{ $player->division_ranking }} 
                                                                @if($player->ptmClub)
                                                                    - {{ $player->ptmClub->name }} ( {{ $player->name }} )
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="player1_club_id" class="form-label">Representing Club</label>
                                                    <select class="form-control" id="player1_club_id" name="player1_club_id">
                                                        <option value="">Same as player's club</option>
                                                        @foreach($clubs as $club)
                                                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Player 2 *</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="player2_id" class="form-label">Select Player</label>
                                                    <select class="form-control" id="player2_id" name="player2_id" required>
                                                        <option value="">Choose player 2...</option>
                                                        @foreach($availablePlayers as $player)
                                                            <option value="{{ $player->id }}">
                                                                {{ $player->nickname }} - {{ $player->division_ranking }} 
                                                                @if($player->ptmClub)
                                                                    - {{ $player->ptmClub->name }} ( {{ $player->name }} )
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="player2_club_id" class="form-label">Representing Club</label>
                                                    <select class="form-control" id="player2_club_id" name="player2_club_id">
                                                        <option value="">Same as player's club</option>
                                                        @foreach($clubs as $club)
                                                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="pair_notes" class="form-label">Pair Notes</label>
                                    <textarea class="form-control" id="pair_notes" name="notes" rows="2" placeholder="Optional notes about this pair..."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Single tournament form -->
                    <form action="{{ route('tournaments.add-player', $tournament) }}" method="POST" id="addPlayerForm">
                        @csrf
                        <input type="hidden" name="registration_type" value="single">
                        
                        <div class="mb-3">
                            <label for="player_id" class="form-label">Select Player *</label>
                            <select class="form-control" id="player_id" name="player_id" required>
                                <option value="">Choose a player...</option>
                                @foreach($availablePlayers as $player)
                                    <option value="{{ $player->id }}">
                                        {{ $player->nickname }} 
                                        @if($player->ptmClub)
                                            - {{ $player->ptmClub->name }} ( {{ $player->name }} )
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                
                @if($tournament->isDuoType())
                    <button type="submit" form="addSinglePlayerForm" class="btn btn-outline-primary">
                        <i class="fas fa-user me-1"></i> Add Single
                    </button>
                    <button type="submit" form="addPairForm" class="btn btn-primary">
                        <i class="fas fa-user-friends me-1"></i> Add Pair
                    </button>
                @else
                    <button type="submit" form="addPlayerForm" class="btn btn-primary">Add Player</button>
                @endif
            </div>
        </div>
    </div>
</div>
@else
<!-- DEBUG: Add Player Modal NOT Rendered - registration not open -->
@endif

<!-- Create Pair Modal -->
@if($tournament->isDuoType() && isset($availableForPairing) && $availableForPairing->count() >= 2)
<!-- DEBUG: Create Pair Modal Rendered -->
<div class="modal fade" id="createPairModal" tabindex="-1" aria-labelledby="createPairModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPairModalLabel">
                    <i class="fas fa-link me-2"></i>Create New Pair
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tournaments.create-pair', $tournament) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="manual_player1_id" class="form-label">Player 1 *</label>
                        <select class="form-control" id="manual_player1_id" name="player1_id" required>
                            <option value="">Select player 1...</option>
                            @foreach($availableForPairing as $player)
                                <option value="{{ $player->id }}">
                                    {{ $player->name }}
                                    @if($player->ptmClub)
                                        - {{ $player->ptmClub->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="manual_player2_id" class="form-label">Player 2 *</label>
                        <select class="form-control" id="manual_player2_id" name="player2_id" required>
                            <option value="">Select player 2...</option>
                            @foreach($availableForPairing as $player)
                                <option value="{{ $player->id }}">
                                    {{ $player->name }}
                                    @if($player->ptmClub)
                                        - {{ $player->ptmClub->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-link me-1"></i> Create Pair
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
<!-- DEBUG: Create Pair Modal NOT Rendered -->
@endif


@endsection

@push('scripts')
<script>
    function generatepdf() {
      
        const pdfWindow = window.open("", "pdfWindow", "width=1200,height=800");
        pdfWindow.location.href = "{{ route('bracket-pdf', $tournament) }}";
    }
    function setEditFormData(element) {
        const playerId = element.getAttribute('data-player-id');
        const playerName = element.getAttribute('data-player-name');
        const seed = element.getAttribute('data-seed');
        const group = element.getAttribute('data-group');
        const representingClub = element.getAttribute('data-representing-club');
        const isRepresentingDifferent = element.getAttribute('data-is-representing-different');
        
        document.getElementById('editPlayerModalLabel').textContent = `Edit Player: ${playerName}`;
        
        // Gunakan route yang sudah ada: tournaments.updatePlayer
        document.getElementById('editPlayerForm').action = `{{ route('tournaments.updatePlayer', ['tournament' => $tournament->id, 'player' => ':playerId']) }}`.replace(':playerId', playerId);
        
        document.getElementById('edit_player_id').value = playerId;
        document.getElementById('edit_seed').value = seed;
        document.getElementById('edit_group').value = group;
        document.getElementById('edit_representing_club').value = representingClub;
        document.getElementById('edit_is_representing_different').checked = isRepresentingDifferent === 'true';
    }

   
    function setChangeFormData(element) {
        const playerId = element.getAttribute('data-player-id');
        const playerName = element.getAttribute('data-player-name');
        
        console.log('setChangeFormData called with:', playerId, playerName);
        
        // Dapatkan form element dengan benar
        const form = document.getElementById('changePlayerForm');
        
        if (!form) {
            console.error('Form dengan ID changePlayerForm tidak ditemukan!');
            return;
        }
        
        // Set form action
        form.action = `/tournaments/{{ $tournament->id }}/change-player/${playerId}`;
        
        console.log('Final form action:', form.action);
        
        // Set modal title dan field values
        document.getElementById('changePlayerModalLabel').textContent = `Change Player: ${playerName}`;
        document.getElementById('change_current_player_id').value = playerId;
        document.getElementById('change_current_player_name').value = playerName;
        document.getElementById('change_seed').value = seed;
        document.getElementById('change_group').value = group;
        
        // Reset select dropdown
        const newPlayerSelect = document.getElementById('new_player_id');
        if (newPlayerSelect) {
            newPlayerSelect.value = '';
        }
    }



// Safe JavaScript dengan comprehensive error handling
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing tournament page...');
    
    // Safe element selector dengan null check
    const $ = (selector) => {
        const element = document.querySelector(selector);
        if (!element) {
            console.warn('⚠️ Element not found:', selector);
            return null;
        }
        return element;
    };

    // Safe element list selector
    const $$ = (selector) => {
        const elements = document.querySelectorAll(selector);
        if (elements.length === 0) {
            console.warn('⚠️ No elements found:', selector);
        }
        return elements;
    };

    // Safe class manipulation
    const safeAddClass = (element, className) => {
        if (element && element.classList) {
            element.classList.add(className);
        }
    };

    const safeRemoveClass = (element, className) => {
        if (element && element.classList) {
            element.classList.remove(className);
        }
    };

    // Initialize modals safely
    function initializeModals() {
        console.log('🔧 Initializing modals...');
        
        const modals = [
            '#addPlayerModal',
            '#createPairModal', 
            '#successModal'
        ];

        modals.forEach(modalId => {
            const modalElement = $(modalId);
            if (modalElement && typeof bootstrap !== 'undefined') {
                try {
                    const modal = new bootstrap.Modal(modalElement);
                    console.log('✅ Modal initialized:', modalId);
                    
                    // Safe event listeners
                    modalElement.addEventListener('show.bs.modal', function() {
                        console.log('📱 Modal opening:', modalId);
                    });
                    
                    modalElement.addEventListener('shown.bs.modal', function() {
                        console.log('✅ Modal fully opened:', modalId);
                    });
                    
                } catch (error) {
                    console.error('❌ Error initializing modal', modalId, error);
                }
            }
        });

        // Auto-show success modal jika ada
        @if(session('success'))
        const successModalElement = $('#successModal');
        if (successModalElement && typeof bootstrap !== 'undefined') {
            setTimeout(() => {
                try {
                    const successModal = new bootstrap.Modal(successModalElement);
                    successModal.show();
                    console.log('✅ Success modal shown');
                } catch (error) {
                    console.error('❌ Error showing success modal:', error);
                }
            }, 500);
        }
        @endif
    }

    // Initialize tabs untuk duo tournaments
    function initializeTabs() {
        console.log('🔧 Initializing tabs...');
        
        const tabContainer = $('#addPlayerTabs');
        if (!tabContainer) {
            console.log('ℹ️ No tabs container found (might not be duo tournament)');
            return;
        }

        const tabButtons = $$('#addPlayerTabs [data-bs-toggle="tab"]');
        const tabPanes = $$('.tab-pane');

        console.log('📑 Found tabs:', tabButtons.length, 'buttons,', tabPanes.length, 'panes');

        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('data-bs-target');
                const targetPane = $(targetId);
                
                if (!targetPane) {
                    console.error('❌ Tab target not found:', targetId);
                    return;
                }

                console.log('🔄 Switching to tab:', targetId);

                // Hide all panes safely
                tabPanes.forEach(pane => {
                    safeRemoveClass(pane, 'show');
                    safeRemoveClass(pane, 'active');
                });
                
                // Remove active from all buttons safely
                tabButtons.forEach(btn => {
                    safeRemoveClass(btn, 'active');
                });
                
                // Show target pane safely
                safeAddClass(targetPane, 'show');
                safeAddClass(targetPane, 'active');
                safeAddClass(this, 'active');
            });
        });
    }

    // Initialize form validations
    function initializeForms() {
        console.log('🔧 Initializing form validations...');

        // Pair form validation
        const addPairForm = $('#addPairForm');
        if (addPairForm) {
            addPairForm.addEventListener('submit', function(e) {
                const player1Select = $('#player1_id');
                const player2Select = $('#player2_id');
                
                if (player1Select && player2Select && player1Select.value === player2Select.value) {
                    e.preventDefault();
                    alert('⚠️ Please select two different players for the pair.');
                    return false;
                }
            });
        }

        // Manual pairing form - sync player selection
        const manualPlayer1Select = $('#manual_player1_id');
        const manualPlayer2Select = $('#manual_player2_id');
        
        if (manualPlayer1Select && manualPlayer2Select) {
            const updateAvailablePlayers = (changedSelect, otherSelect) => {
                const selectedValue = changedSelect.value;
                if (selectedValue) {
                    // Disable selected player in other select
                    Array.from(otherSelect.options).forEach(option => {
                        if (option.value && option.value === selectedValue) {
                            option.disabled = true;
                        } else {
                            option.disabled = false;
                        }
                    });
                } else {
                    // Enable all options if no selection
                    Array.from(otherSelect.options).forEach(option => {
                        option.disabled = false;
                    });
                }
            };

            manualPlayer1Select.addEventListener('change', function() {
                updateAvailablePlayers(this, manualPlayer2Select);
            });

            manualPlayer2Select.addEventListener('change', function() {
                updateAvailablePlayers(this, manualPlayer1Select);
            });
        }
    }

    // Reset forms ketika modal ditutup
    function initializeModalReset() {
        const addPlayerModal = $('#addPlayerModal');
        if (addPlayerModal) {
            addPlayerModal.addEventListener('hidden.bs.modal', function() {
                console.log('🔄 Resetting modal forms...');
                
                // Reset semua forms dalam modal
                const forms = $$('#addPlayerModal form');
                forms.forEach(form => {
                    if (form) form.reset();
                });
                
                // Reset select disabled states
                const manualPlayer1Select = $('#manual_player1_id');
                const manualPlayer2Select = $('#manual_player2_id');
                
                if (manualPlayer1Select) {
                    Array.from(manualPlayer1Select.options).forEach(option => {
                        option.disabled = false;
                    });
                }
                if (manualPlayer2Select) {
                    Array.from(manualPlayer2Select.options).forEach(option => {
                        option.disabled = false;
                    });
                }
            });
        }
    }

    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('🚨 Global error caught:', {
            message: e.message,
            file: e.filename,
            line: e.lineno,
            column: e.colno,
            error: e.error
        });
    });

    // Unhandled promise rejection handler
    window.addEventListener('unhandledrejection', function(e) {
        console.error('🚨 Unhandled promise rejection:', e.reason);
    });

    // Initialize semua components
    try {
        initializeModals();
        initializeTabs();
        initializeForms();
        initializeModalReset();
        
        console.log('✅ Tournament page initialized successfully!');
        
        // Debug info
        console.log('📊 Tournament Info:', {
            type: '{{ $tournament->type }}',
            isDuo: {{ $tournament->isDuoType() ? 'true' : 'false' }},
            players: {{ $tournament->players->count() }},
            availablePlayers: {{ $availablePlayers->count() ?? 0 }}
        });
        
    } catch (error) {
        console.error('❌ Error during initialization:', error);
    }
});

// Safe utility functions
function safeShowModal(modalId) {
    const modalElement = document.querySelector(modalId);
    if (modalElement && typeof bootstrap !== 'undefined') {
        try {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('✅ Modal shown safely:', modalId);
        } catch (error) {
            console.error('❌ Error showing modal:', modalId, error);
        }
    } else {
        console.error('❌ Cannot show modal - element or bootstrap not found:', modalId);
    }
}

function safeHideModal(modalId) {
    const modalElement = document.querySelector(modalId);
    if (modalElement && typeof bootstrap !== 'undefined') {
        try {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
                console.log('✅ Modal hidden safely:', modalId);
            }
        } catch (error) {
            console.error('❌ Error hiding modal:', modalId, error);
        }
    }
}
</script>

<style>
/* Safe CSS styles */
.modal {
    display: none;
    opacity: 0;
    transition: opacity 0.15s linear;
}

.modal.show {
    display: block;
    opacity: 1;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: #000;
}

.modal-backdrop.show {
    opacity: 0.5;
}

/* Tab styles */
.nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.tab-pane {
    display: none;
}

.tab-pane.show {
    display: block;
    opacity: 1;
}

/* Form styles */
.form-control:disabled {
    background-color: #e9ecef;
    opacity: 1;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>
@endpush