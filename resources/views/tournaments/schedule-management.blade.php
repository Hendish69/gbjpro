{{-- resources/views/tournaments/schedule-management.blade.php --}}
@extends('layouts.app')

@section('title', 'Match Schedule Management - ' . $tournament->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="row">
                    <div class="col">
                        <h4 class="page-title">Match Schedule Management</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}">Tournaments</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament->id) }}">{{ $tournament->name }}</a></li>
                            <li class="breadcrumb-item active">Schedule Management</li>
                        </ol>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('tournaments.registration-management', $tournament->id) }}" class="btn btn-info">
                            <i class="fas fa-users me-1"></i> Manage Registration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tournament Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Tournament:</strong> {{ $tournament->name }}<br>
                            <strong>Type:</strong> {{ ucfirst($tournament->type) }}<br>
                            <strong>Format:</strong> {{ ucfirst(str_replace('_', ' ', $tournament->format)) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Participants:</strong> {{ $participants->count() }}<br>
                            <strong>Tables Available:</strong> {{ $tables->count() }}<br>
                            <strong>Time Slots:</strong> {{ count($timeSlots) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Match Duration:</strong> {{ $tournament->estimated_match_duration ?? 30 }} min<br>
                            <strong>Break Time:</strong> {{ $tournament->break_between_matches ?? 5 }} min<br>
                            <strong>Daily Hours:</strong> 
                                @if($tournament->daily_start_time && $tournament->daily_end_time)
                                    {{ $tournament->daily_start_time->format('H:i') }} - {{ $tournament->daily_end_time->format('H:i') }}
                                @else
                                    08:00 - 22:00
                                @endif
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary mb-2" onclick="getOptimizationData()">
                                <i class="fas fa-chart-line me-1"></i> Get Optimization
                            </button>
                            <div id="optimizationResult"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Schedule Creation -->
        <div class="col-lg-6">
            <!-- Single Match Creation -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create Match
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.schedule-single-match', $tournament->id) }}" method="POST" id="singleMatchForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Participant 1 *</label>
                                    <select name="participant1_id" class="form-select" required id="participant1Select">
                                        <option value="">Choose Participant...</option>
                                        @foreach($participants as $participant)
                                            <option value="{{ $participant['id'] }}" data-type="{{ $participant['type'] }}">
                                                {{ $participant['name'] }}
                                                @if(isset($participant['players']))
                                                    - {{ $participant['players'] }}
                                                @endif
                                                @if(isset($participant['division_info']))
                                                    ({{ $participant['division_info'] }})
                                                @endif
                                                @if(isset($participant['club']))
                                                    - {{ $participant['club'] }}
                                                @endif
                                                @if(isset($participant['member_count']))
                                                    - {{ $participant['member_count'] }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Participant 2 *</label>
                                    <select name="participant2_id" class="form-select" required id="participant2Select">
                                        <option value="">Choose Participant...</option>
                                        @foreach($participants as $participant)
                                            <option value="{{ $participant['id'] }}" data-type="{{ $participant['type'] }}">
                                                {{ $participant['name'] }}
                                                @if(isset($participant['players']))
                                                    - {{ $participant['players'] }}
                                                @endif
                                                @if(isset($participant['division_info']))
                                                    ({{ $participant['division_info'] }})
                                                @endif
                                                @if(isset($participant['club']))
                                                    - {{ $participant['club'] }}
                                                @endif
                                                @if(isset($participant['member_count']))
                                                    - {{ $participant['member_count'] }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Availability Info --}}
                        @if($participants->count() > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>{{ $participants->count() }} available participants</strong>
                                @switch($tournament->type)
                                    @case('single')
                                        - Single players ready for matches
                                        @break
                                    @case('double')
                                    @case('duo')
                                        - Duo pairs available for scheduling
                                        @break
                                    @case('team')
                                        - Teams ready for competition
                                        @break
                                @endswitch
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>No available participants found!</strong>
                                @switch($tournament->type)
                                    @case('single')
                                        All registered players are already in matches.
                                        @break
                                    @case('double')
                                    @case('duo')
                                        All duo pairs are already scheduled in matches.
                                        @break
                                    @case('team')
                                        All teams are already participating in matches.
                                        @break
                                @endswitch
                            </div>
                        @endif
                        <input type="hidden" name="participant_type" id="participantType" value="player">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Round Number *</label>
                                    <input type="number" name="round_number" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Match Number *</label>
                                    <input type="number" name="match_number" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Table *</label>
                                    <select name="table_id" class="form-select" required>
                                        <option value="">Choose Table...</option>
                                        @foreach($tables as $table)
                                            <option value="{{ $table->id }}">{{ $table->name }} - {{ $table->location }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Match Format *</label>
                                    <select name="match_format" class="form-select" required>
                                        <option value="">Choose format...</option>
                                        @foreach($macthformat as $slot)
                                            <option value="{{ $slot['format'] }}">{{ $slot['format'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Match Date *</label>
                                    <input type="date" name="match_date" class="form-control" value="{{ date('Y-m-d',strtotime($tournament->start_date)) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Match Time *</label>
                                    <select name="match_time" class="form-select" required>
                                        <option value="">Choose Time...</option>
                                        @foreach($timeSlots as $slot)
                                            <option value="{{ $slot['start'] }}">{{ $slot['display'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-1"></i> Create Match
                        </button>
                    </form>
                </div>
            </div>

            <!-- Round Matches Generation -->
            <!-- <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-layer-group me-2"></i>
                        Generate Round Matches
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.schedule-round-matches', $tournament->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Round Number *</label>
                                    <input type="number" name="round_number" class="form-control" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Match Date *</label>
                                    <input type="date" name="match_date" class="form-control" value="{{ date('Y-m-d',strtotime($tournament->start_date)) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Start Time *</label>
                                    <select name="start_time" class="form-select" required>
                                        <option value="">Choose Start Time...</option>
                                        @foreach($timeSlots as $slot)
                                            <option value="{{ $slot['start'] }}">{{ $slot['start'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Tables *</label>
                            <div class="row">
                                @foreach($tables as $table)
                                <div class="col-md-3 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="table_ids[]" value="{{ $table->id }}" class="form-check-input" id="table_{{ $table->id }}">
                                        <label class="form-check-label" for="table_{{ $table->id }}">
                                            {{ $table->name }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This will create matches for all available participants in random pairs.
                            Estimated matches: {{ floor($participants->count() / 2) }}
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play-circle me-1"></i> Generate Round Matches
                        </button>
                    </form>
                </div>
            </div> -->

            <!-- Elimination Bracket Generation
            @if($tournament->format === \App\Models\Tournament::FORMAT_ELIMINATION)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-sitemap me-2"></i>
                        Generate Elimination Bracket
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.generate-elimination-bracket', $tournament->id) }}" method="POST">
                        @csrf
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This will generate a complete elimination bracket for all participants.
                            Total matches: {{ $participants->count() - 1 }}
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sitemap me-1"></i> Generate Bracket
                        </button>
                    </form>
                </div>
            </div>
            @endif -->
        </div>

        <!-- Right Column - Existing Matches -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        <i class="fas fa-list me-2"></i>
                        Scheduled Matches ({{ $existingMatches->count() }})
                    </h5>
                    <span class="badge bg-{{ $existingMatches->where('status', 'scheduled')->count() > 0 ? 'primary' : 'secondary' }}">
                        {{ $existingMatches->where('status', 'scheduled')->count() }} scheduled
                    </span>
                </div>
                <div class="card-body">
                    @if($existingMatches->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Match</th>
                                    <th>Participants</th>
                                    <th>Date & Time</th>
                                    <th>Table</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($existingMatches as $match)
                                <tr>
                                    <td>
                                        <strong>R{{ $match->round_number }} M{{ $match->match_number }}</strong>
                                    </td>
                                    <td>
                                        @if($match->match_type === 'team')
                                            {{ $match->team1->name ?? 'TBA' }} vs {{ $match->team2->name ?? 'TBA' }}
                                        @elseif($match->match_type === 'double' || $match->match_type === 'duo')
                                            {{ $match->player1->name ?? 'TBA' }}/{{ $match->player1Partner->name ?? 'TBA' }}<br>
                                            vs<br>
                                            {{ $match->player2->name ?? 'TBA' }}/{{ $match->player2Partner->name ?? 'TBA' }}
                                        @else
                                            {{ $match->player1->name ?? 'TBA' }} vs {{ $match->player2->name ?? 'TBA' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($match->match_date)
                                            {{ $match->match_date->format('M d') }}<br>
                                            <small>{{ $match->match_date->format('H:i') }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $match->table->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ match($match->status) {
                                            'scheduled' => 'secondary',
                                            'ongoing' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        } }}">
                                            {{ ucfirst($match->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('matches.show', $match->id) }}" class="btn btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editScheduleModal"
                                                    data-match-id="{{ $match->id }}"
                                                    data-match-date="{{ $match->match_date ? $match->match_date->format('Y-m-d') : '' }}"
                                                    data-match-time="{{ $match->match_date ? $match->match_date->format('H:i') : '' }}"
                                                    data-table-id="{{ $match->table_id }}"
                                                    title="Edit Schedule">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($match->isScheduled)
                                            <form action="{{ route('matches.start', $match->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Start Match">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>No matches scheduled yet.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <a href="{{ route('matches.create') }}?tournament_id={{ $tournament->id }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-1"></i> New Match
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('matches.index') }}?tournament_id={{ $tournament->id }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-list me-1"></i> All Matches
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('tournaments.bracket', $tournament->id) }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-sitemap me-1"></i> View Bracket
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('bracket-pdf', $tournament->id) }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-file-pdf me-1"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Match Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Match Date *</label>
                                <input type="date" name="match_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Match Time *</label>
                                <select name="match_time" class="form-select" required>
                                    <option value="">Choose Time...</option>
                                    @foreach($timeSlots as $slot)
                                        <option value="{{ $slot['start'] }}">{{ $slot['display'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Table *</label>
                        <select name="table_id" class="form-select" required>
                            <option value="">Choose Table...</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}">{{ $table->name }} - {{ $table->location }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Di schedule-management.blade.php script section
// Enhanced version dengan fitur lengkap
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Initializing enhanced participant selection...');
    
    const participant1Select = document.getElementById('participant1Select');
    const participant2Select = document.getElementById('participant2Select');
    const participantTypeInput = document.getElementById('participantType');
    const singleMatchForm = document.getElementById('singleMatchForm');
    
    // Store original options
    let originalOptions = [];
    
    if (participant1Select) {
        originalOptions = Array.from(participant1Select.options);
    }
    
    // Initialize participant selection system
    function initializeParticipantSelection() {
        if (!participant1Select || !participant2Select) {
            console.warn('⚠️ Participant select elements not found');
            return;
        }
        
        console.log('🔄 Initializing participant selection system...');
        
        // Function to update available options (HIDE approach)
        function updateAvailableOptions() {
            const participant1Value = participant1Select.value;
            const participant2Value = participant2Select.value;
            
            console.log('🔄 Updating options:', {
                p1: participant1Value,
                p2: participant2Value
            });
            
            // Update Participant 2 dropdown
            participant2Select.innerHTML = '<option value="">Choose Participant...</option>';
            originalOptions.forEach(option => {
                if (option.value && option.value !== participant1Value) {
                    participant2Select.appendChild(option.cloneNode(true));
                }
            });
            
            // Update Participant 1 dropdown  
            participant1Select.innerHTML = '<option value="">Choose Participant...</option>';
            originalOptions.forEach(option => {
                if (option.value && option.value !== participant2Value) {
                    participant1Select.appendChild(option.cloneNode(true));
                }
            });
            
            // Restore selected values jika masih valid
            if (participant1Value && participant1Value !== participant2Value) {
                participant1Select.value = participant1Value;
            }
            if (participant2Value && participant2Value !== participant1Value) {
                participant2Select.value = participant2Value;
            }
            
            // Update participant type
            updateParticipantType();
            
            // Update form validation state
            updateFormValidation();
        }
        
        // Function to update participant type
        function updateParticipantType() {
            if (participant1Select.value && participantTypeInput) {
                const selectedOption = participant1Select.options[participant1Select.selectedIndex];
                const dataType = selectedOption.getAttribute('data-type');
                participantTypeInput.value = dataType;
                console.log('📝 Participant type updated to:', dataType);
            }
        }
        
        // Function to update form validation UI
        function updateFormValidation() {
            const isValid = participant1Select.value && participant2Select.value;
            
            // Highlight borders berdasarkan validation state
            [participant1Select, participant2Select].forEach(select => {
                if (select.value) {
                    select.style.borderColor = '#198754'; // Green for valid
                    select.style.boxShadow = '0 0 0 0.2rem rgba(25, 135, 84, 0.25)';
                } else {
                    select.style.borderColor = '';
                    select.style.boxShadow = '';
                }
            });
            
            // Update submit button state
            const submitBtn = singleMatchForm ? singleMatchForm.querySelector('button[type="submit"]') : null;
            if (submitBtn) {
                if (isValid) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-secondary');
                    submitBtn.classList.add('btn-primary');
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-secondary');
                }
            }
            
            console.log('✅ Form validation updated - Valid:', isValid);
        }
        
        // Function to reset selections
        function resetSelections() {
            participant1Select.value = '';
            participant2Select.value = '';
            updateAvailableOptions();
            console.log('🔄 Selections reset');
        }
        
        // Add reset button functionality
        function addResetButton() {
            const resetBtn = document.createElement('button');
            resetBtn.type = 'button';
            resetBtn.className = 'btn btn-outline-secondary btn-sm mt-2';
            resetBtn.innerHTML = '<i class="fas fa-redo me-1"></i> Reset Selection';
            resetBtn.addEventListener('click', resetSelections);
            
            // Insert after participant 2 select
            if (participant2Select.parentNode) {
                participant2Select.parentNode.appendChild(resetBtn);
            }
        }
        
        // Add event listeners
        participant1Select.addEventListener('change', updateAvailableOptions);
        participant2Select.addEventListener('change', updateAvailableOptions);
        
        // Initialize
        updateAvailableOptions();
        addResetButton();
        
        console.log('✅ Enhanced participant selection initialized');
    }
    
    // Initialize the system
    initializeParticipantSelection();
});

</script>
@endpush
@push('styles')
<style>
/* Style untuk selected participants */
.select-valid {
    border-color: #198754 !important;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25) !important;
}

.select-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Style untuk disabled options (jika pakai visual locking) */
select option:disabled {
    color: #6c757d !important;
    background-color: #f8f9fa !important;
    font-style: italic;
}

/* Loading state */
.select-loading {
    opacity: 0.7;
    pointer-events: none;
}

/* Success state */
.select-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
}
</style>
@endpush
