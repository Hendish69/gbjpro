@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tournament Draw: {{ $tournament->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tournament
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Player Management -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Tournament Players</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('tournaments.add-players', $tournament) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Select Players for Tournament</label>
                        <select name="player_ids[]" class="form-select" multiple size="8">
                            @foreach($players as $player)
                            <option value="{{ $player->id }}" 
                                {{ $tournament->players->contains($player->id) ? 'selected' : '' }}>
                                {{ $player->name }} (Division: {{ $player->division_ranking }})
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl to select multiple players</div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-users"></i> Update Players
                    </button>
                </form>

                <hr>

                <h6>Current Players ({{ $tournament->players->count() }})</h6>
                @if($tournament->players->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tournament->players as $player)
                            <tr>
                                <td>{{ $player->name }}</td>
                                <td>{{ $player->division_ranking }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">No players added yet.</p>
                @endif
            </div>
        </div>
    </div>
    <!-- Tambahkan di bagian Player Management -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Quick Add Players</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('tournaments.createquick-add-players', $tournament) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Add Multiple Players (one per line)</label>
                    <textarea name="player_names" class="form-control" rows="4" 
                            placeholder="Enter player names, one per line&#10;Example:&#10;John Doe&#10;Jane Smith&#10;Mike Johnson"></textarea>
                    <div class="form-text">Each line will be added as a new player to the library and tournament.</div>
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-bolt"></i> Quick Add Players
                </button>
            </form>
        </div>
    </div>
    <!-- Di dalam form add players -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">Add Players with Club Representation</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('tournaments.add-players', $tournament) }}" method="POST" id="addPlayersForm">
                @csrf
                
                <div class="table-responsive">
                    <table class="table table-hover" id="playersTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">Player</th>
                                <th width="15%">Division</th>
                                <th width="25%">PTM Club</th>
                                <th width="25%">Representing PTM Club</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($players as $index => $player)
                            <tr>
                                <td>
                                    <input type="checkbox" name="player_ids[]" value="{{ $player->id }}" 
                                        class="form-check-input player-checkbox">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($player->photo)
                                            <img src="{{ asset('storage/' . $player->photo) }}" 
                                                class="player-avatar me-2">
                                        @else
                                            <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $player->display_name }}</strong>
                                            @if($player->ptm_number)
                                            <br><small class="text-muted">No: {{ $player->ptm_number }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $player->division_color }}">
                                        {{ $player->division_name }}
                                    </span>
                                </td>
                                <td>
                                    @if($player->ptmClub)
                                        <span class="badge bg-primary">
                                            {{ $player->ptmClub->code ? $player->ptmClub->code . ' - ' : '' }}{{ $player->ptmClub->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">Tidak ada klub</span>
                                    @endif
                                </td>
                                <td>
                                    <select name="representing_club_ids[]" class="form-control form-control-sm">
                                        <option value="">Klub Sendiri</option>
                                        @foreach($ptmClubs as $club)
                                        @if(!$player->ptmClub || $club->id != $player->ptmClub->id)
                                        <option value="{{ $club->id }}">
                                            {{ $club->display_name }}
                                        </option>
                                        @endif
                                        @endforeach
                                    </select>
                                    <input type="text" name="representation_notes[]" class="form-control form-control-sm mt-1" 
                                        placeholder="Catatan (opsional)">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" id="selectAllBtn" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-check-square"></i> Select All
                    </button>
                    <button type="button" id="deselectAllBtn" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-square"></i> Deselect All
                    </button>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add Selected Players to Tournament
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Draw Generation -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Generate Draw</h6>
            </div>
            <div class="card-body">
                @if($tournament->players->count() < 2)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Need at least 2 players to generate tournament draw.
                </div>
                @else
                <form action="{{ route('tournaments.generate-draw', $tournament) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="draw_method" class="form-label">Draw Method</label>
                        <select class="form-control" id="draw_method" name="draw_method" required>
                            <option value="random">Random Draw</option>
                            <option value="rating">By Rating (Highest vs Lowest)</option>
                            <option value="seeded">Seeded Draw (Fair Distribution)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Draw Information:</h6>
                                <ul class="mb-0">
                                    <li><strong>Players:</strong> {{ $tournament->players->count() }}</li>
                                    <li><strong>Type:</strong> {{ ucfirst($tournament->type) }} Elimination</li>
                                    <li><strong>Rounds:</strong> {{ ceil(log($tournament->players->count(), 2)) }}</li>
                                    <li><strong>Matches:</strong> {{ $tournament->players->count() - 1 }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-magic"></i> Generate Tournament Draw
                    </button>
                </form>

                <div class="mt-3">
                    <a href="{{ route('tournaments.bracket', $tournament) }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-project-diagram"></i> View Bracket
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Draw Methods Explanation -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Draw Methods Explained</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Random Draw:</strong>
                    <p class="mb-1 text-muted">Complete random pairing of players.</p>
                </div>
                <div class="mb-3">
                    <strong>By Rating:</strong>
                    <p class="mb-1 text-muted">Highest rated players paired with lowest rated players in first round.</p>
                </div>
                <div class="mb-3">
                    <strong>Seeded Draw:</strong>
                    <p class="mb-1 text-muted">Top players distributed evenly across the bracket for fair competition.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal untuk Add New Player -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Player</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPlayerForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nickname</label>
                        <input type="text" name="nickname" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating *</label>
                        <input type="number" name="rating" class="form-control" value="1000" min="0" max="3000" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Player</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// AJAX untuk add new player
document.getElementById('addPlayerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("tournaments.add-new-player", $tournament) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page untuk update player list
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding player.');
    });
});

// Auto-complete untuk player search
const playerSearch = document.getElementById('playerSearch');
if (playerSearch) {
    playerSearch.addEventListener('input', function() {
        // Implement auto-complete logic here
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Select/Deselect All functionality
    document.getElementById('selectAllBtn').addEventListener('click', function() {
        document.querySelectorAll('.player-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    document.getElementById('deselectAllBtn').addEventListener('click', function() {
        document.querySelectorAll('.player-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Form validation
    document.getElementById('addPlayersForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.player-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one player.');
        }
    });
});
</script>
@endsection