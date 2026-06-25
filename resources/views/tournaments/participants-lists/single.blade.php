{{-- resources/views/tournaments/participants-lists/single.blade.php --}}
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-users me-2"></i>Registered Players ({{ $stats['single_players'] }})
        </h5>
    </div>
    <div class="card-body">
        @if($stats['single_players'] > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Division</th>
                        <th>Club</th>
                        <th>Seed</th>
                        <th>Group</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tournament->tournamentPlayers as $tp)
                    <tr>
                        <td>
                            <strong>{{ $tp->player->display_name }}</strong>
                            @if($tp->is_representing_different_club)
                                <br><small class="text-muted">Representing: {{ $tp->representingClub->name ?? 'N/A' }}</small>
                            @endif
                        </td>
                        <td>Div {{ $tp->player->division_ranking }}</td>
                        <td>{{ $tp->player->ptmClub->name ?? '-' }}</td>
                        <td>{{ $tp->seed ?? '-' }}</td>
                        <td>{{ $tp->group ?? '-' }}</td>
                        <td>
                            <form action="{{ route('tournaments.remove-participant', $tournament->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="type" value="player">
                                <input type="hidden" name="id" value="{{ $tp->player_id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Remove {{ $tp->player->display_name }} from tournament?')">
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
            <i class="fas fa-users fa-3x mb-3"></i>
            <p>No players registered yet.</p>
        </div>
        @endif
    </div>
</div>