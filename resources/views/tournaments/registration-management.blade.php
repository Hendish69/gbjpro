{{-- resources/views/tournaments/registration-management.blade.php --}}
@extends('layouts.app')

@section('title', 'Tournament Registration Management - ' . $tournament->name)

@section('page-title', 'Tournament Registration Management')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('tournaments.index') }}">Tournaments</a></li>
<li class="breadcrumb-item"><a href="{{ route('tournaments.show', $tournament->id) }}">{{ $tournament->name }}</a></li>
<li class="breadcrumb-item active">Registration Management</li>
@endsection

@section('page-actions')
<a href="{{ route('tournaments.schedule-management', $tournament->id) }}" class="btn btn-success">
    <i class="fas fa-calendar-alt me-1"></i> Schedule Matches
</a>
<a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-1"></i> Back to Tournament
</a>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Tournament Type Badge & Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">{{ $tournament->name }}</h4>
                            <p class="text-muted mb-2">{{ $tournament->description }}</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary fs-6">{{ strtoupper($tournament->type) }} TOURNAMENT</span>
                                <span class="badge bg-secondary">{{ ucfirst($tournament->format) }} Format</span>
                                <span class="badge bg-{{ $tournament->status_color }}">{{ ucfirst($tournament->status) }}</span>
                                @if($tournament->is_registration_open)
                                    <span class="badge bg-success">Registration Open</span>
                                @else
                                    <span class="badge bg-danger">Registration Closed</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h5 class="mb-0">{{ $stats['single_players'] }}</h5>
                                    <small class="text-muted">Single Players</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="mb-0">{{ $stats['duo_pairs'] }}</h5>
                                    <small class="text-muted">Duo Pairs</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="mb-0">{{ $stats['teams'] }}</h5>
                                    <small class="text-muted">Teams</small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Total Participants: {{ $stats['total_participants'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Registration Forms -->
        <div class="col-lg-6">
            <!-- Dynamic Registration Form berdasarkan Tournament Type -->
            @switch($tournament->type)
                @case('single')
                    @include('tournaments.registration-forms.single')
                    @break

                @case('double')
                @case('duo')
                    @include('tournaments.registration-forms.duo')
                    @break

                @case('team')
                    @include('tournaments.registration-forms.team')
                    @break

                @default
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Unknown tournament type: {{ $tournament->type }}
                    </div>
            @endswitch

            <!-- Quick Actions Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @if($tournament->isDuoType() && $tournament->getUnpairedPlayers()->count() >= 2)
                        <div class="col-12">
                            <form action="{{ route('tournaments.auto-pair-players', $tournament->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-magic me-1"></i> Auto Pair All Players
                                </button>
                                <small class="text-muted d-block mt-1">
                                    {{ $tournament->getUnpairedPlayers()->count() }} unpaired players available
                                </small>
                            </form>
                        </div>
                        @endif

                        <div class="col-6">
                            <a href="{{ route('tournaments.players.index', $tournament->id) }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-list me-1"></i> Manage Players
                            </a>
                        </div>
                        
                        <div class="col-6">
                            <a href="{{ route('players.library.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-book me-1"></i> Player Library
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Participants List -->
        <div class="col-lg-6">
            <!-- Dynamic Participants List berdasarkan Tournament Type -->
            @switch($tournament->type)
                @case('single')
                    @include('tournaments.participants-lists.single')
                    @break

                @case('double')
                @case('duo')
                    @include('tournaments.participants-lists.duo')
                    @break

                @case('team')
                    @include('tournaments.participants-lists.team')
                    @break
            @endswitch
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-pair confirmation
    const autoPairForm = document.querySelector('form[action*="auto-pair-players"]');
    if (autoPairForm) {
        autoPairForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to auto-pair all unpaired players?')) {
                e.preventDefault();
            }
        });
    }

    // Dynamic form validation untuk duo pairing
    const player1Select = document.getElementById('player1Select');
    const player2Select = document.getElementById('player2Select');
    
    if (player1Select && player2Select) {
        const validatePlayerSelection = () => {
            if (player1Select.value && player2Select.value && player1Select.value === player2Select.value) {
                alert('Please select two different players for the pair.');
                player2Select.value = '';
            }
        };

        player1Select.addEventListener('change', validatePlayerSelection);
        player2Select.addEventListener('change', validatePlayerSelection);
    }

    // Loading states untuk forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            }
        });
    });
});
</script>
@endpush