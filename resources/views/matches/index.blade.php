@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Matches</h1>
    
    <div class="mb-3">
        <a href="{{ route('matches.create') }}" class="btn btn-primary">Create New Match</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Filter Matches</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('matches.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <select name="tournament_id" class="form-select">
                            <option value="">All Tournaments</option>
                            @foreach($tournaments as $tournament)
                                <option value="{{ $tournament->id }}" {{ request('tournament_id') == $tournament->id ? 'selected' : '' }}>
                                    {{ $tournament->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tournament</th>
                    <th>Match</th>
                    <th>Participants</th>
                    <th>Round</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($matches as $match)
                <tr>
                    <td>{{ $match->id }}</td>
                    <td>{{ $match->tournament->name }}</td>
                    <td>#{{ $match->match_number }}</td>
                    <td>
                        @if($match->match_type === 'team')
                            {{ $match->team1->name ?? 'TBA' }} vs {{ $match->team2->name ?? 'TBA' }}
                        @else
                            {{ $match->player1->name ?? 'TBA' }} vs {{ $match->player2->name ?? 'TBA' }}
                        @endif
                    </td>
                    <td>Round {{ $match->round_number }}</td>
                    <td>{{ $match->match_date->format('M d, Y H:i') }}</td>
                    <td>
                        <span class="badge bg-{{ $match->status_color }}">
                            {{ ucfirst($match->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('matches.show', $match->id) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('matches.edit', $match->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        @if($match->isScheduled)
                            <form action="{{ route('matches.start', $match->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Start</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $matches->links() }}
</div>
@endsection