@extends('layouts.app')

@section('title', 'Edit Tournament: ' . $tournament->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Tournament: {{ $tournament->name }}</h4>
                    <div class="float-right">
                        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View Tournament
                        </a>
                        <a href="{{ route('tournaments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Tournaments
                        </a>
                    </div>
                </div>
                <div class="card-body">
                     <!-- Debug Session Messages -->
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    @endif
                     <!-- Debug Old Data -->
                    @php
                        // Debug: lihat data old yang tersimpan
                       // dd(old());
                    @endphp
                    <!-- Debug Info -->
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <h5>Validation Errors:</h5>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <form action="{{ route('tournaments.update', $tournament) }}" method="POST" id="tournamentForm">
                        @csrf
                        @method('PUT')
                       
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Basic Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Tournament Name *</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', $tournament->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="3">{{ old('description', $tournament->description) }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="type" class="form-label">Tournament Type *</label>
                                                    <select class="form-control @error('type') is-invalid @enderror" 
                                                            id="type" name="type" required>
                                                        <option value="">Select Type</option>
                                                        @foreach($types as $type)
                                                            <option value="{{ $type }}" 
                                                                {{ old('type', $tournament->type) == $type ? 'selected' : '' }}>
                                                                {{ ucfirst($type) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="format" class="form-label">Format *</label>
                                                    <select class="form-control @error('format') is-invalid @enderror" 
                                                            id="format" name="format" required>
                                                        <option value="">Select Format</option>
                                                        <option value="elimination" {{ old('format', $tournament->format) == 'elimination' ? 'selected' : '' }}>Elimination</option>
                                                        <option value="league" {{ old('format', $tournament->format) == 'league' ? 'selected' : '' }}>League</option>
                                                        <option value="group" {{ old('format', $tournament->format) == 'group' ? 'selected' : '' }}>Group Stage</option>
                                                        <option value="round_robin" {{ old('format', $tournament->format) == 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                                                    </select>
                                                    @error('format')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="status" class="form-label">Status *</label>
                                            <select class="form-control @error('status') is-invalid @enderror" 
                                                    id="status" name="status" required>
                                                <option value="pending" {{ old('status', $tournament->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="registration_open" {{ old('status', $tournament->status) == 'registration_open' ? 'selected' : '' }}>Registration Open</option>
                                                <option value="ongoing" {{ old('status', $tournament->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                                <option value="completed" {{ old('status', $tournament->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ old('status', $tournament->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dates & Capacity -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Dates & Capacity</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="start_date" class="form-label">Start Date *</label>
                                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" 
                                                   value="{{ old('start_date', $tournament->start_date->format('Y-m-d\TH:i')) }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="end_date" class="form-label">End Date *</label>
                                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" 
                                                   value="{{ old('end_date', $tournament->end_date->format('Y-m-d\TH:i')) }}" required>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="registration_deadline" class="form-label">Registration Deadline *</label>
                                            <input type="datetime-local" class="form-control @error('registration_deadline') is-invalid @enderror" 
                                                   id="registration_deadline" name="registration_deadline" 
                                                   value="{{ old('registration_deadline', $tournament->registration_deadline->format('Y-m-d\TH:i')) }}" required>
                                            @error('registration_deadline')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="max_players" class="form-label">Max Players *</label>
                                                    <input type="number" class="form-control @error('max_players') is-invalid @enderror" 
                                                           id="max_players" name="max_players" 
                                                           value="{{ old('max_players', $tournament->max_players) }}" min="2" required>
                                                    @error('max_players')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="max_teams" class="form-label">Max Teams</label>
                                                    <input type="number" class="form-control @error('max_teams') is-invalid @enderror" 
                                                           id="max_teams" name="max_teams" 
                                                           value="{{ old('max_teams', $tournament->max_teams) }}" min="1">
                                                    @error('max_teams')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted">Only for team tournaments</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Time Management -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Time & Table Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="available_tables" class="form-label">Available Tables *</label>
                                            <input type="number" class="form-control @error('available_tables') is-invalid @enderror" 
                                                   id="available_tables" name="available_tables" 
                                                   value="{{ old('available_tables', $tournament->available_tables) }}" min="1" required>
                                            @error('available_tables')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="matches_per_table" class="form-label">Matches Per Table *</label>
                                            <input type="number" class="form-control @error('matches_per_table') is-invalid @enderror" 
                                                   id="matches_per_table" name="matches_per_table" 
                                                   value="{{ old('matches_per_table', $tournament->matches_per_table) }}" min="1" required>
                                            @error('matches_per_table')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="estimated_match_duration" class="form-label">Match Duration (minutes) *</label>
                                            <input type="number" class="form-control @error('estimated_match_duration') is-invalid @enderror" 
                                                   id="estimated_match_duration" name="estimated_match_duration" 
                                                   value="{{ old('estimated_match_duration', $tournament->estimated_match_duration) }}" min="1" required>
                                            @error('estimated_match_duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="break_between_matches" class="form-label">Break Between Matches (minutes) *</label>
                                            <input type="number" class="form-control @error('break_between_matches') is-invalid @enderror" 
                                                   id="break_between_matches" name="break_between_matches" 
                                                   value="{{ old('break_between_matches', $tournament->break_between_matches) }}" min="0" required>
                                            @error('break_between_matches')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="warmup_time" class="form-label">Warmup Time (minutes) *</label>
                                            <input type="number" class="form-control @error('warmup_time') is-invalid @enderror" 
                                                   id="warmup_time" name="warmup_time" 
                                                   value="{{ old('warmup_time', $tournament->warmup_time) }}" min="0" required>
                                            @error('warmup_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="daily_start_time" class="form-label">Daily Start Time *</label>
                                            <input type="time" class="form-control @error('daily_start_time') is-invalid @enderror" 
                                                   id="daily_start_time" name="daily_start_time" 
                                                   value="{{ old('daily_start_time', $tournament->daily_start_time ? $tournament->daily_start_time->format('H:i') : '09:00') }}" required>
                                            @error('daily_start_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="daily_end_time" class="form-label">Daily End Time *</label>
                                            <input type="time" class="form-control @error('daily_end_time') is-invalid @enderror" 
                                                   id="daily_end_time" name="daily_end_time" 
                                                   value="{{ old('daily_end_time', $tournament->daily_end_time ? $tournament->daily_end_time->format('H:i') : '17:00') }}" required>
                                            @error('daily_end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="max_daily_playing_hours" class="form-label">Max Daily Playing Hours *</label>
                                            <input type="number" class="form-control @error('max_daily_playing_hours') is-invalid @enderror" 
                                                   id="max_daily_playing_hours" name="max_daily_playing_hours" 
                                                   value="{{ old('max_daily_playing_hours', $tournament->max_daily_playing_hours) }}" min="1" max="24" required>
                                            @error('max_daily_playing_hours')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Statistics -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">Current Tournament Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Registered Players</h6>
                                                <h3 class="text-primary">{{ $tournament->players->count() }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Total Matches</h6>
                                                <h3 class="text-info">{{ $tournament->total_matches }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Completed Matches</h6>
                                                <h3 class="text-success">{{ $tournament->completed_matches }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6>Progress</h6>
                                                <h3 class="text-warning">{{ $tournament->progress }}%</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($tournament->matches->count() > 0)
                                <div class="mt-3">
                                    <div class="alert alert-info">
                                        <strong>Note:</strong> Changing time settings may affect scheduled matches. 
                                        Existing matches will need to be reviewed if you change table availability or time slots.
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </form>
                        <!-- Danger Zone -->
                    @if($tournament->matches->count() == 0)
                    <div class="card border-danger mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Danger Zone</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Delete Tournament</h6>
                                    <p class="mb-0">Once you delete a tournament, there is no going back. Please be certain.</p>
                                </div>
                                <div>
                                    <form action="{{ route('tournaments.destroy', $tournament) }}" method="POST" 
                                            onsubmit="return confirm('Are you sure you want to delete this tournament? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete Tournament
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card border-warning mb-4">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Restrictions</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Tournament cannot be deleted</strong> because it already has matches. 
                                You can only cancel the tournament.
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="form-group text-center">
                        <button form="tournamentForm" type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Tournament
                        </button>
                        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-info btn-lg">
                            <i class="fas fa-eye"></i> View Tournament
                        </a>
                        <a href="{{ route('tournaments.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tournamentForm');
    const submitBtn = document.querySelector('button[form="tournamentForm"][type="submit"]');

    console.log('Form:', form);
    console.log('Submit Button:', submitBtn); // HARUS [object HTMLButtonElement]

    if (!form || !submitBtn) {
        console.error('Form atau tombol tidak ditemukan!');
        return;
    }

    // Submit handler
    form.addEventListener('submit', function(e) {
        console.log('SUBMIT EVENT FIRED!');
        console.log('Action:', form.action);

        submitBtn.innerHTML = 'Updating...';
        submitBtn.disabled = true;
    });

    // Click debug
    submitBtn.addEventListener('click', function() {
        console.log('BUTTON CLICKED!');
    });

    // ... validasi lain (tanpa alert)
});
</script>
@endpush