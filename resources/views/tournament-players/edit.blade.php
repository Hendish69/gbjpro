@extends('layouts.app')

@section('title', 'Edit Player - ' . $tournament->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Player: {{ $tournamentPlayer->player->display_name }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.players.update', [$tournament, $tournamentPlayer]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Player Information</label>
                                    <div class="form-control bg-light">
                                        <strong>{{ $tournamentPlayer->player->display_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $tournamentPlayer->player->ptmClub->name ?? 'No Club' }}
                                            @if($tournamentPlayer->player->ptm_number)
                                                • {{ $tournamentPlayer->player->ptm_number }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Seed Number</label>
                                    <input type="number" name="seed" class="form-control" 
                                           value="{{ $tournamentPlayer->seed }}" min="1" 
                                           placeholder="Optional seed number">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Group</label>
                            <input type="text" name="group" class="form-control" 
                                   value="{{ $tournamentPlayer->group }}" 
                                   placeholder="e.g., Group A, Pool 1">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Representing Club</label>
                            <select name="representing_ptm_club_id" class="form-select">
                                <option value="">Same as Original Club</option>
                                @foreach($ptmClubs as $club)
                                <option value="{{ $club->id }}" 
                                    {{ $tournamentPlayer->representing_ptm_club_id == $club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Jika player mewakili klub yang berbeda dari klub aslinya
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Representation Notes</label>
                            <textarea name="representation_notes" class="form-control" rows="3"
                                      placeholder="Optional notes about club representation...">{{ $tournamentPlayer->representation_notes }}</textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tournaments.draw', $tournament) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Draw
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Player
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection