{{-- resources/views/tournaments/registration-forms/single.blade.php --}}
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-user-plus me-2"></i>Register Single Player
        </h5>
    </div>
    <div class="card-body">
        <form action="{{ route('tournaments.register-single-player', $tournament->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Select Player *</label>
                <select name="player_id" class="form-select" required>
                    <option value="">Choose Player...</option>
                    @foreach($availablePlayers as $player)
                        <option value="{{ $player->id }}">
                            {{ $player->display_name }} 
                            @if($player->ptmClub)
                                - {{ $player->ptmClub->name }}
                            @endif
                            (Div {{ $player->division_ranking }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Representing Club</label>
                        <select name="representing_club_id" class="form-select">
                            <option value="">Same as Player's Club</option>
                            @foreach($ptmClubs as $club)
                                <option value="{{ $club->id }}">{{ $club->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Seed</label>
                        <input type="number" name="seed" class="form-control" min="1" placeholder="Optional">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Group</label>
                        <input type="text" name="group" class="form-control" placeholder="e.g., A, B, C">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-user-plus me-1"></i> Register Player
            </button>
        </form>
    </div>
</div>