{{-- resources/views/tournaments/participants-lists/duo.blade.php --}}
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-user-friends me-2"></i>Duo Pairs ({{ $stats['duo_pairs'] }})
        </h5>
    </div>
    <div class="card-body">
        @if($stats['duo_pairs'] > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Pair Name</th>
                        <th>Players</th>
                        <th>Divisions</th>
                        <th>Clubs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tournament->duoPairs->where('status', 'confirmed') as $pair)
                    <tr>
                        <td>
                            <strong>{{ $pair->pair_display_name }}</strong>
                            @if($pair->team_name)
                                <br><small class="text-muted">Team: {{ $pair->team_name }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <div>{{ $pair->player1->display_name }}</div>
                                <div>{{ $pair->player2->display_name }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div>Div {{ $pair->player1->division_ranking }}</div>
                                <div>Div {{ $pair->player2->division_ranking }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div>{{ $pair->player1->ptmClub->name ?? '-' }}</div>
                                <div>{{ $pair->player2->ptmClub->name ?? '-' }}</div>
                            </div>
                        </td>
                        <td>
                            <form action="{{ route('tournaments.remove-participant', $tournament->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="type" value="pair">
                                <input type="hidden" name="id" value="{{ $pair->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Remove this pair?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4">
            <i class="fas fa-user-friends fa-3x mb-3"></i>
            <p>No duo pairs created yet.</p>
        </div>
        @endif

        <!-- Unpaired Players Section -->
        @if($tournament->getUnpairedPlayers()->count() > 0)
        <div class="mt-4">
            <h6 class="text-muted mb-3">
                <i class="fas fa-user me-1"></i> Unpaired Players ({{ $tournament->getUnpairedPlayers()->count() }})
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-borderless">
                    <tbody>
                        @foreach($tournament->getUnpairedPlayers() as $player)
                        <tr>
                            <td>{{ $player->display_name }}</td>
                            <td>Div {{ $player->division_ranking }}</td>
                            <td>{{ $player->ptmClub->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-warning">Unpaired</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>