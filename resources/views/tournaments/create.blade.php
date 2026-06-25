@extends('layouts.app')

@section('title', 'Create Tournament')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Create New Tournament</h4>
                    <a href="{{ route('tournaments.index') }}" class="btn btn-secondary btn-sm float-right">
                        <i class="fas fa-arrow-left"></i> Back to Tournaments
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.store') }}" method="POST" id="tournamentForm">
                        @csrf
                        
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
                                                   id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
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
                                                                {{ old('type') == $type ? 'selected' : '' }}>
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
                                                        <option value="elimination" {{ old('format') == 'elimination' ? 'selected' : '' }}>Elimination</option>
                                                        <option value="group" {{ old('format') == 'group' ? 'selected' : '' }}>Group Stage</option>
                                                        <option value="round_robin" {{ old('format') == 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                                                        <option value="league" {{ old('format') == 'league' ? 'selected' : '' }}>League</option>
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
                                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="registration_open" {{ old('status') == 'registration_open' ? 'selected' : '' }}>Registration Open</option>
                                                <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                                                   id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="end_date" class="form-label">End Date *</label>
                                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="registration_deadline" class="form-label">Registration Deadline *</label>
                                            <input type="datetime-local" class="form-control @error('registration_deadline') is-invalid @enderror" 
                                                   id="registration_deadline" name="registration_deadline" value="{{ old('registration_deadline') }}" required>
                                            @error('registration_deadline')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="max_players" class="form-label">Max Players *</label>
                                                    <input type="number" class="form-control @error('max_players') is-invalid @enderror" 
                                                           id="max_players" name="max_players" value="{{ old('max_players', 48) }}" min="2" required>
                                                    @error('max_players')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="max_teams" class="form-label">Max Teams</label>
                                                    <input type="number" class="form-control @error('max_teams') is-invalid @enderror" 
                                                           id="max_teams" name="max_teams" value="{{ old('max_teams') }}" min="1">
                                                    @error('max_teams')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
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
                                                   id="available_tables" name="available_tables" value="{{ old('available_tables', 2) }}" min="1" required>
                                            @error('available_tables')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="matches_per_table" class="form-label">Matches Per Table *</label>
                                            <input type="number" class="form-control @error('matches_per_table') is-invalid @enderror" 
                                                   id="matches_per_table" name="matches_per_table" value="{{ old('matches_per_table', 1) }}" min="1" required>
                                            @error('matches_per_table')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="estimated_match_duration" class="form-label">Match Duration (minutes) *</label>
                                            <input type="number" class="form-control @error('estimated_match_duration') is-invalid @enderror" 
                                                   id="estimated_match_duration" name="estimated_match_duration" value="{{ old('estimated_match_duration', 10) }}" min="1" required>
                                            @error('estimated_match_duration')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="break_between_matches" class="form-label">Break Between Matches (minutes) *</label>
                                            <input type="number" class="form-control @error('break_between_matches') is-invalid @enderror" 
                                                   id="break_between_matches" name="break_between_matches" value="{{ old('break_between_matches', 5) }}" min="0" required>
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
                                                   id="warmup_time" name="warmup_time" value="{{ old('warmup_time', 5) }}" min="0" required>
                                            @error('warmup_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="daily_start_time" class="form-label">Daily Start Time *</label>
                                            <input type="time" class="form-control @error('daily_start_time') is-invalid @enderror" 
                                                   id="daily_start_time" name="daily_start_time" value="{{ old('daily_start_time', '08:00') }}" required>
                                            @error('daily_start_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="daily_end_time" class="form-label">Daily End Time *</label>
                                            <input type="time" class="form-control @error('daily_end_time') is-invalid @enderror" 
                                                   id="daily_end_time" name="daily_end_time" value="{{ old('daily_end_time', '18:00') }}" required>
                                            @error('daily_end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="max_daily_playing_hours" class="form-label">Max Daily Playing Hours *</label>
                                            <input type="number" class="form-control @error('max_daily_playing_hours') is-invalid @enderror" 
                                                   id="max_daily_playing_hours" name="max_daily_playing_hours" value="{{ old('max_daily_playing_hours', 12) }}" min="1" max="24" required>
                                            @error('max_daily_playing_hours')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estimation Preview -->
                        <div class="card mb-4">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0">Schedule Estimation Preview</h5>
                            </div>
                            <div class="card-body">
                                <div id="estimationPreview" class="row text-center">
                                    <div class="col-md-12">
                                        <p class="text-muted">Fill in the form to see schedule estimation</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus"></i> Create Tournament
                            </button>
                            <a href="{{ route('tournaments.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
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
    const estimationPreview = document.getElementById('estimationPreview');
   
    let hoursPerDay=24;
    
    
    // Function to calculate estimation
    function calculateEstimation() {
        const availableTables = parseInt(document.getElementById('available_tables').value) || 0;
        const matchDuration = parseInt(document.getElementById('estimated_match_duration').value) || 0;
        const maxPlayers = parseInt(document.getElementById('max_players').value) || 0;
        const tournamentType = document.getElementById('type').value;
        const tournamentformat = document.getElementById('format').value;
        const group = document.getElementById('max_teams');
        const istirahat=parseInt(document.getElementById('break_between_matches').value) || 0;
        const pemanasan=parseInt(document.getElementById('warmup_time').value) || 0;
        let hperday =parseInt(document.getElementById('max_daily_playing_hours')) || 12;
        
        if (availableTables > 0 && matchDuration > 0 && maxPlayers >= 2) {
            
           
            const totalMatchTime = matchDuration+istirahat+pemanasan;
            const dailyStart = document.getElementById('daily_start_time').value;
            let dailyEnd = document.getElementById('daily_end_time');
            group.value=1;
            console.log(totalMatchTime,'totalMatchTime');
            
            // Calculate daily playing minutes
            const startTime = new Date(`2000-01-01T${dailyStart}`);
          
            // Estimate total matches based on tournament type
            let totalMatches = 0; courts = 1;doubleProbability=0.7;
            let totalMinutes=0;
            let groupAdvancers = 1; // jumlah yang lolos per grup
            // const calcRoundRobin = (n) => (n * (n - 1)) / 2;
            const calcRoundRobin = (n) => (n * (n - 1)) / 2;
            const toHours = (minutes) => minutes / 60;
            const toDays = (hours) => hours / hoursPerDay / courts;
            if (tournamentformat === "league") {
                const matches = calcRoundRobin(maxPlayers);
                totalMinutes = matches * totalMatchTime;
                totalMatches = matches;
            }else if (tournamentformat === "group") {
                group.value=3;
                hoursPerDay=12;
                const groups=group.value;
                let jmlpeserta=maxPlayers;
                if (tournamentType=='duo' || tournamentType=='double'){
                    jmlpeserta=maxPlayers/2
                }

                const perGroup = jmlpeserta / groups;
                let groupMatches = calcRoundRobin(groups);
                if (tournamentType=='duo'){
                    // matches = (maxPlayers/2) - 1;
                    const avgGamesPerMatch = 2 + (1 * doubleProbability);
                    groupMatches = Math.round(perGroup * avgGamesPerMatch);
                    console.log(jmlpeserta,perGroup,groupMatches,'groupMatches',avgGamesPerMatch)
                    
                }
                const totalGroupMatches = groupMatches * perGroup;
                // console.log('perGroup',perGroup,'groupMatches',groupMatches,'totalGroupMatches',totalGroupMatches)
                const advancers = groups * groupAdvancers;
                const knockoutMatches = advancers - 1;
                totalMatches = totalGroupMatches + knockoutMatches;
                totalMinutes = totalMatches * totalMatchTime;
                // console.log(perGroup,groupMatches,totalGroupMatches,advancers,totalMatches);
            }else if (tournamentformat === "round_robin") {
            }else if (tournamentformat === "elimination") {
                let matches = maxPlayers - 1;
                totalMinutes = matches * totalMatchTime;

                if (tournamentType=='duo'){
                    matches = (maxPlayers/2) - 1;
                    const avgGamesPerMatch = 2 + (1 * doubleProbability);
                    matches = Math.round(matches * avgGamesPerMatch);
                    // console.log(matches,'totalGamestotalGamestotalGames')
                    totalMinutes = matches * totalMatchTime;
                }else if (tournamentType=='double'){
                    matches = (maxPlayers/2) - 1;
                }
                totalMatches = matches;

            }
            const totalParallel = courts * availableTables;
            const effectiveMinutes = totalMinutes / totalParallel;
            const totalHours = toHours(effectiveMinutes);
            const totalDays = toDays(toHours(effectiveMinutes));
            console.log(dailyStart,'startTime')


            const [startH, startM] = dailyStart.split(":").map(Number);
            const startMinutes = startH * 60 + startM;
            const endMinutes = startMinutes + effectiveMinutes;
            const endH = Math.floor((endMinutes % (24 * 60)) / 60);
            const endM = Math.round(endMinutes % 60);
            const endTime = `${String(endH).padStart(2, "0")}:${String(endM).padStart(2, "0")}`;

            const hours=toHours(totalMinutes);
            // const days = toDays(toHours(totalMinutes))
            console.log(totalMatches,totalDays,'totalMatchestotalMatchestotalMatches',totalMinutes,endTime,effectiveMinutes,totalHours)
            dailyEnd.value=endTime;
            hperday.value=hoursPerDay?hoursPerDay:24;
            // switch(tournamentType) {
            //     case 'single':
            //         totalMatches = maxPlayers - 1;
            //         break;
            //     case 'double':
            //         totalMatches = maxPlayers - 1;// (maxPlayers * 2) - 2;
            //         break;
            //     case 'duo':
            //         totalMatches = (maxPlayers * 2) -2;
            //         break;
            //     case 'team':
            //         totalMatches = Math.ceil(maxPlayers / 2) - 1;
            //         break;
            //     default:
            //         totalMatches = maxPlayers - 1;
            // }
            let totalDailyMatches=Math.floor(totalMatches/availableTables);dailyMinutes=0;
            const daysNeeded = Math.ceil(totalDays);
            const totalDurationMinutes = effectiveMinutes;
            
            // Update preview
            estimationPreview.innerHTML = `
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Total Matches</h6>
                            <h3 class="text-primary">${totalMatches}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Matches Per Table</h6>
                            <h3 class="text-info">${totalDailyMatches}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Days Needed</h6>
                            <h3 class="text-warning">${daysNeeded}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Total Duration</h6>
                            <h3 class="text-success">${Math.round(totalDurationMinutes / 60)} hours</h3>
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    // Add event listeners to relevant fields
    const calculationFields = [
        'available_tables', 'matches_per_table', 'estimated_match_duration',
        'break_between_matches', 'warmup_time', 'max_players', 'type','format',
        'daily_start_time', 'daily_end_time', 'max_daily_playing_hours'
    ];

    // const calculationFields = [
    //     'available_tables', 'estimated_match_duration','max_players', 'type','format',
    //     'daily_start_time'
    // ];
    
    calculationFields.forEach(field => {
        document.getElementById(field).addEventListener('input', calculateEstimation);
        document.getElementById(field).addEventListener('change', calculateEstimation);
    });
    
    // Initial calculation
    calculateEstimation();
    
    // Date validation
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    const registrationDeadline = document.getElementById('registration_deadline');
    
    startDate.addEventListener('change', function() {
        const start = new Date(this.value);
        const now = new Date();
        
        if (start < now) {
            alert('Start date must be in the future');
            this.value = '';
        }
        
        // Set min for end date
        endDate.min = this.value;
        
        // Set max for registration deadline
        registrationDeadline.max = this.value;
    });
    
    endDate.addEventListener('change', function() {
        const start = new Date(startDate.value);
        const end = new Date(this.value);
        
        if (end < start) {
            alert('End date must be after start date');
            this.value = '';
        }
    });
    
    registrationDeadline.addEventListener('change', function() {
        const start = new Date(startDate.value);
        const deadline = new Date(this.value);
        
        if (deadline >= start) {
            alert('Registration deadline must be before start date');
            this.value = '';
        }
    });
//     function tournamentSimulator({
//         type = "group_knockout", // "league", "group_knockout", "single_knockout", "duo_knockout"
//         participants = 48,
//         groups = 0,              // untuk group stage
//         groupAdvancers = 1,      // jumlah yang lolos per grup
//         minutesPerGame = 20,
//         doubleProbability = 0.7, // untuk duo_knockout
//         courts = 1,
//         hoursPerDay = 12
//         }) {
//         let totalMatches = 0;
//         let details = [];

//         const calcRoundRobin = (n) => (n * (n - 1)) / 2;
//         const toHours = (minutes) => minutes / 60;
//         const toDays = (hours) => hours / hoursPerDay / courts;

//         // ================================
//         // 1️⃣ League / Round Robin penuh
//         // ================================
//         if (type === "league") {
//             const matches = calcRoundRobin(participants);
//             const totalMinutes = matches * minutesPerGame;
//             totalMatches = matches;

//             details.push({
//             phase: "League",
//             matches,
//             minutes: totalMinutes,
//             hours: toHours(totalMinutes),
//             days: toDays(toHours(totalMinutes))
//             });
//         }

//         // ================================
//         // 2️⃣ Group Stage + Knockout
//         // ================================
//         else if (type === "group_knockout") {
//             if (groups <= 0) throw new Error("Harus menentukan jumlah grup untuk group_knockout!");

//             // --- Group Stage ---
//             const perGroup = participants / groups;
//             const groupMatches = calcRoundRobin(perGroup);
//             const totalGroupMatches = groupMatches * groups;

//             const advancers = groups * groupAdvancers;
//             const knockoutMatches = advancers - 1;
//             console.log('perGroup',perGroup,'groupMatches : ',groupMatches,'totalGroupMatches',totalGroupMatches,'knockoutMatches',knockoutMatches)
//             totalMatches = totalGroupMatches + knockoutMatches;

//             const totalGroupMinutes = totalGroupMatches * minutesPerGame;
//             const totalKnockoutMinutes = knockoutMatches * minutesPerGame;
//             const totalMinutes = totalGroupMinutes + totalKnockoutMinutes;

//             details.push({
//             phase: "Group Stage",
//             matches: totalGroupMatches,
//             minutes: totalGroupMinutes,
//             hours: toHours(totalGroupMinutes),
//             days: toDays(toHours(totalGroupMinutes))
//             });

//             details.push({
//             phase: "Knockout Stage",
//             matches: knockoutMatches,
//             minutes: totalKnockoutMinutes,
//             hours: toHours(totalKnockoutMinutes),
//             days: toDays(toHours(totalKnockoutMinutes))
//             });
//         }

//         // ================================
//         // 3️⃣ Single Knockout
//         // ================================
//         else if (type === "single_knockout") {
//             const matches = participants - 1;
//             const totalMinutes = matches * minutesPerGame;
//             totalMatches = matches;

//             details.push({
//             phase: "Knockout",
//             matches,
//             minutes: totalMinutes,
//             hours: toHours(totalMinutes),
//             days: toDays(toHours(totalMinutes))
//             });
//         }

//         // ================================
//         // 4️⃣ Duo Knockout (2 single + 1 double)
//         // ================================
//         else if (type === "duo_knockout") {
//             const matches = participants - 1;
//             const avgGamesPerMatch = 2 + (1 * doubleProbability);
//             const totalGames = matches * avgGamesPerMatch;
//             const totalMinutes = totalGames * minutesPerGame;
//             totalMatches = matches;

//             details.push({
//             phase: "Duo Knockout",
//             matches,
//             avgGamesPerMatch,
//             totalGames,
//             minutes: totalMinutes,
//             hours: toHours(totalMinutes),
//             days: toDays(toHours(totalMinutes))
//             });
//         }

//         // ================================
//         // Rekap Akhir
//         // ================================
//         const totalMinutes = details.reduce((sum, d) => sum + d.minutes, 0);
//         const totalHours = toHours(totalMinutes);
//         const totalDays = toDays(totalHours);

//         return {
//             type,
//             participants,
//             courts,
//             hoursPerDay,
//             totalMatches: Math.round(totalMatches),
//             totalMinutes: Math.round(totalMinutes),
//             totalHours: totalHours.toFixed(2),
//             totalDays: totalDays.toFixed(2),
//             formatted: `${Math.floor(totalHours)} jam ${Math.round(totalMinutes % 60)} menit`,
//             details
//         };
//     }

// // ================================
// // 🔍 Contoh Pemakaian
// // ================================

// // 1️⃣ Liga penuh (semua bertemu)
// console.log(tournamentSimulator({
//   type: "league",
//   participants: 10,
//   minutesPerGame: 20,
//   courts: 2
// }));

// // 2️⃣ Group stage → knockout
// console.log(tournamentSimulator({
//   type: "group_knockout",
//   participants: 150,
//   groups: 50,
//   groupAdvancers: 1,
//   minutesPerGame: 20,
//   courts: 3
// }));

// // 3️⃣ Duo knockout
// console.log(tournamentSimulator({
//   type: "duo_knockout",
//   participants: 48,
//   doubleProbability: 0.7,
//   minutesPerGame: 20,
//   courts: 4
// }));


});
</script>
@endpush