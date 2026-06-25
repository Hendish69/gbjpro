<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Photo</th>
            <th>Name</th>
            <th>PTM Club</th>
            <th>Divisi</th>
            <th>Matches</th>
            <th>Win Rate</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($players as $player)
            <tr>
                <td>
                    @if($player->photo)
                        <img src="{{ asset('images/' .$player->photo) }}" class="rounded-circle" width="50" height="50">
                    @else
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </td>
                <td>
                    <strong>{{ $player->nickname??$player->name }}</strong>
                    @if($player->name)
                        <br><small class="text-muted">{{ $player->name }}</small>
                    @endif
                    @if($player->phone)
                        <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $player->phone }}</small>
                    @endif
                </td>
                <td>
                    @if($player->ptmClub)
                        <span class="badge bg-primary">
                            {{ $player->ptmClub->name }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    <span class="badge" style="background-color: {{ $player->division_color ?? '#777' }}">
                        {{ $player->division_name ?? '-' }}
                    </span>
                </td>
                <td>{{ $player->total_matches ?? 0 }}</td>
                <td>
                    <span class="badge bg-{{ $player->win_rate >= 50 ? 'success' : 'warning' }}">
                        {{ $player->win_rate ?? 0 }}%
                    </span>
                </td>
                <td>
                    <a href="{{ route('players.show', $player) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('players.edit', $player) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('players.destroy', $player) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin hapus?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted">Tidak ada pemain ditemukan</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-center">
   {{ $players->links('pagination::bootstrap-5') }}
</div>
