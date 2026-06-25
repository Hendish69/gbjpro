{{-- resources/views/tournaments/registration-forms/duo.blade.php --}}
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-user-friends me-2"></i>Duo/Double Registration
        </h5>
    </div>
    <div class="card-body">
        <ul class="nav nav-pills mb-3" id="duoRegistrationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="single-tab" data-bs-toggle="pill" data-bs-target="#single-form" type="button">
                    <i class="fas fa-user me-1"></i> Single Player
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pair-tab" data-bs-toggle="pill" data-bs-target="#pair-form" type="button">
                    <i class="fas fa-user-friends me-1"></i> Create Pair
                </button>
            </li>
        </ul>

        <div class="tab-content" id="duoRegistrationContent">
            <!-- Single Player Tab -->
            <div class="tab-pane fade show active" id="single-form" role="tabpanel">
                <form action="{{ route('tournaments.register-single-player', $tournament->id) }}" method="POST">
                    @csrf
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Register individual player for later pairing
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Player *</label>
                        <select name="player_id" class="form-select" required>
                            <option value="">Choose Player...</option>
                            @foreach($availablePlayers as $player)
                                <option value="{{ $player->id }}">
                                    {{ $player->display_name }} - Div {{ $player->division_ranking }}
                                    @if($player->ptmClub)
                                        ({{ $player->ptmClub->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Seed</label>
                                <input type="number" name="seed" class="form-control" min="1" placeholder="Optional">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Group</label>
                                <input type="text" name="group" class="form-control" placeholder="e.g., A, B, C">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-user-plus me-1"></i> Add Single Player
                    </button>
                </form>
            </div>

            <!-- Create Pair Tab -->
            <div class="tab-pane fade" id="pair-form" role="tabpanel">
                <form action="{{ route('tournaments.create-duo-pair', $tournament->id) }}" method="POST">
                    @csrf
                    <div class="alert alert-success">
                        <i class="fas fa-user-friends me-2"></i>
                        Create a pair/team directly
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Player 1 *</label>
                                <select name="player1_id" class="form-select" required id="player1Select">
                                    <option value="">Choose Player 1...</option>
                                    @foreach($availablePlayers as $player)
                                        <option value="{{ $player->id }}">
                                            {{ $player->display_name }} - Div {{ $player->division_ranking }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Player 2 *</label>
                                <select name="player2_id" class="form-select" required id="player2Select">
                                    <option value="">Choose Player 2...</option>
                                    @foreach($availablePlayers as $player)
                                        <option value="{{ $player->id }}">
                                            {{ $player->display_name }} - Div {{ $player->division_ranking }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Pair Name *</label>
                                <input type="text" name="pair_name" class="form-control" required placeholder="e.g., Dynamic Duo, Team Alpha">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Team Name</label>
                                <input type="text" name="team_name" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Pair notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-save me-1"></i> Create Pair
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>