@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>Tournaments</h4>
                        <a href="{{ route('tournaments.create') }}" class="btn btn-primary">Create Tournament</a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter form -->
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="registration_open" {{ request('status') == 'registration_open' ? 'selected' : '' }}>Registration Open</option>
                                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Tournaments table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tournaments as $tournament)
                                <tr>
                                    <td>{{ $tournament->name }}</td>
                                    <td>{{ ucfirst($tournament->type) }}</td>
                                    <td>{{ $tournament->start_date->format('d M Y') }}</td>
                                    <td>{{ $tournament->end_date->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $tournament->status_color }}">{{ ucfirst($tournament->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $tournament->progress }}%;">
                                                {{ $tournament->progress }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('tournaments.show', $tournament) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('tournaments.edit', $tournament) }}" class="btn btn-sm btn-warning">Edit</a>
                                        @if(!$tournament->matches()->exists())
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $tournament->id }}">
                                                Delete
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>Delete</button>
                                        @endif

                                        <!-- Modal Konfirmasi -->
                                        <div class="modal fade" id="deleteModal{{ $tournament->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Hapus Turnamen</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Yakin ingin menghapus turnamen:</p>
                                                        <strong>"{{ $tournament->name }}"</strong>?
                                                        <br><small>Data akan hilang permanen.</small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        
                                                        <form action="{{ route('tournaments.destroy', $tournament) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{ $tournaments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection