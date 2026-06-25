@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Table Details: {{ $table->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tables.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tables
        </a>
    </div>
</div>

<div class="row">
    <!-- Table Info -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Table Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ $table->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Location:</strong></td>
                        <td>{{ $table->location ?: 'Not specified' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $table->status == 'available' ? 'success' : ($table->status == 'occupied' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($table->status) }}
                            </span>
                        </td>
                    </tr>
                </table>

                @if($table->description)
                <div class="mt-3">
                    <strong>Description:</strong>
                    <p class="text-muted mt-1">{{ $table->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Usage Stats -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Usage Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary mb-0">{{ $todayUsage }}m</div>
                        <small class="text-muted">Today</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-0">{{ $totalUsage }}m</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <div class="h6 mb-0">{{ $table->matches->where('status', 'completed')->count() }}</div>
                    <small class="text-muted">Completed Matches</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Match & History -->
    <div class="col-md-8">
        <!-- Current Match -->
        @if($table->currentMatch)
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h6 class="m-0 font-weight-bold text-dark">Current Match</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <h5>{{ $table->currentMatch->player1->name }}</h5>
                        <div class="rating">Rating: {{ $table->currentMatch->player1->rating }}</div>
                    </div>
                    <div class="col-md-6 text-center">
                        <h5>{{ $table->currentMatch->player2->name }}</h5>
                        <div class="rating">Rating: {{ $table->currentMatch->player2->rating }}</div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Tournament: {{ $table->currentMatch->tournament->name }} | 
                        Round: {{ $table->currentMatch->round_number }}
                    </small>
                </div>
            </div>
        </div>
        @endif

        <!-- Match History -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Recent Matches</h6>
            </div>
            <div class="card-body">
                @if($table->matches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tournament</th>
                                <th>Players</th>
                                <th>Date</th>
                                <th>Duration</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($table->matches->take(10) as $match)
                            <tr>
                                <td>{{ $match->tournament->name }}</td>
                                <td>
                                    {{ $match->player1->name }} vs {{ $match->player2->name }}
                                </td>
                                <td>{{ $match->match_date->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($match->duration_minutes)
                                        {{ $match->duration_formatted }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $match->status == 'completed' ? 'success' : ($match->status == 'live' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($match->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">No matches played on this table yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('tables.edit', $table) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Table
            </a>
            <form action="{{ route('tables.destroy', $table) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this table?')">
                    <i class="fas fa-trash"></i> Delete Table
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.rating {
    color: #6c757d;
    font-size: 0.9rem;
}
</style>
@endsection