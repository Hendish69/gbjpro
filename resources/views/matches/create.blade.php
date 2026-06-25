@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Schedule New Match</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matches.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Matches
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('matches.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tournament_id" class="form-label">Tournament *</label>
                        <select class="form-control @error('tournament_id') is-invalid @enderror" 
                                id="tournament_id" name="tournament_id" required>
                            <option value="">Select Tournament</option>
                            @foreach($tournaments as $tournament)
                                <option value="{{ $tournament->id }}" 
                                    {{ old('tournament_id', request('tournament')) == $tournament->id ? 'selected' : '' }}>
                                    {{ $tournament->name }} ({{ $tournament->start_date->format('M d, Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('tournament_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="player1_id" class="form-label">Player 1 *</label>
                        <select class="form-control @error('player1_id') is-invalid @enderror" 
                                id="player1_id" name="player1_id" required>
                            <option value="">Select Player 1</option>
                            @foreach($players as $player)
                                <option value="{{ $player->id }}" {{ old('player1_id') == $player->id ? 'selected' : '' }}>
                                    {{ $player->name }} (Divisi: {{ $player->division_ranking }})
                                </option>
                            @endforeach
                        </select>
                        @error('player1_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="player2_id" class="form-label">Player 2 *</label>
                        <select class="form-control @error('player2_id') is-invalid @enderror" 
                                id="player2_id" name="player2_id" required>
                            <option value="">Select Player 2</option>
                            @foreach($players as $player)
                                <option value="{{ $player->id }}" {{ old('player2_id') == $player->id ? 'selected' : '' }}>
                                    {{ $player->name }} (Divisi: {{ $player->division_ranking }})
                                </option>
                            @endforeach
                        </select>
                        @error('player2_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="match_date" class="form-label">Match Date & Time *</label>
                        <input type="datetime-local" class="form-control @error('match_date') is-invalid @enderror" 
                               id="match_date" name="match_date" value="{{ old('match_date') }}" required>
                        @error('match_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="round_number" class="form-label">Round Number *</label>
                        <input type="number" class="form-control @error('round_number') is-invalid @enderror" 
                               id="round_number" name="round_number" value="{{ old('round_number', 1) }}" 
                               min="1" max="10" required>
                        @error('round_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="table_id" class="form-label">Table (Optional)</label>
                        <select class="form-control @error('table_id') is-invalid @enderror" 
                                id="table_id" name="table_id">
                            <option value="">No Table Assigned</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" {{ old('table_id') == $table->id ? 'selected' : '' }}>
                                    {{ $table->name }} 
                                    @if($table->location) - {{ $table->location }} @endif
                                    ({{ ucfirst($table->status) }})
                                </option>
                            @endforeach
                        </select>
                        @error('table_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calendar-plus"></i> Schedule Match
                </button>
                <a href="{{ route('matches.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Validasi: Player 1 dan Player 2 tidak boleh sama
    document.addEventListener('DOMContentLoaded', function() {
        const player1Select = document.getElementById('player1_id');
        const player2Select = document.getElementById('player2_id');
        
        function validatePlayers() {
            if (player1Select.value && player2Select.value && player1Select.value === player2Select.value) {
                player2Select.setCustomValidity('Player 1 and Player 2 cannot be the same person.');
            } else {
                player2Select.setCustomValidity('');
            }
        }
        
        player1Select.addEventListener('change', validatePlayers);
        player2Select.addEventListener('change', validatePlayers);
        
        // Set minimum datetime untuk match date (saat ini)
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('match_date').min = now.toISOString().slice(0, 16);
    });
</script>
@endsection