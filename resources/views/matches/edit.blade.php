@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Match</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matches.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Matches
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('matches.update', $match) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tournament_id" class="form-label">Tournament *</label>
                        <select class="form-control @error('tournament_id') is-invalid @enderror" 
                                id="tournament_id" name="tournament_id" required>
                            <option value="">Select Tournament</option>
                            @foreach($tournaments as $tournament)
                                <option value="{{ $tournament->id }}" 
                                    {{ old('tournament_id', $match->tournament_id) == $tournament->id ? 'selected' : '' }}>
                                    {{ $tournament->name }}
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
                                <option value="{{ $player->id }}" 
                                    {{ old('player1_id', $match->player1_id) == $player->id ? 'selected' : '' }}>
                                    {{ $player->name }} (Rating: {{ $player->rating }})
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
                                <option value="{{ $player->id }}" 
                                    {{ old('player2_id', $match->player2_id) == $player->id ? 'selected' : '' }}>
                                    {{ $player->name }} (Rating: {{ $player->rating }})
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
                               id="match_date" name="match_date" 
                               value="{{ old('match_date', $match->match_date->format('Y-m-d\TH:i')) }}" required>
                        @error('match_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="round_number" class="form-label">Round Number *</label>
                        <input type="number" class="form-control @error('round_number') is-invalid @enderror" 
                               id="round_number" name="round_number" 
                               value="{{ old('round_number', $match->round_number) }}" 
                               min="1" max="10" required>
                        @error('round_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-control @error('status') is-invalid @enderror" 
                                id="status" name="status" required>
                            <option value="scheduled" {{ old('status', $match->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="live" {{ old('status', $match->status) == 'live' ? 'selected' : '' }}>Live</option>
                            <option value="completed" {{ old('status', $match->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="table_id" class="form-label">Table</label>
                        <select class="form-control @error('table_id') is-invalid @enderror" 
                                id="table_id" name="table_id">
                            <option value="">No Table Assigned</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" 
                                    {{ old('table_id', $match->table_id) == $table->id ? 'selected' : '' }}>
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

            @if($match->status == 'completed')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Player 1 Score</label>
                        <input type="number" class="form-control" value="{{ $match->player1_score }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Player 2 Score</label>
                        <input type="number" class="form-control" value="{{ $match->player2_score }}" readonly>
                    </div>
                </div>
            </div>
            @endif

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Match
                </button>
                <a href="{{ route('matches.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
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
    });
</script>
@endsection