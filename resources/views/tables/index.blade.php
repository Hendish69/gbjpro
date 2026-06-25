@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Table Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tables.create') }}" class="btn btn-primary me-2">
            <i class="fas fa-plus"></i> Add Table
        </a>
        <a href="{{ route('tables.usage-report') }}" class="btn btn-info">
            <i class="fas fa-chart-bar"></i> Usage Report
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    @foreach($tables as $table)
    <div class="col-md-4 mb-4">
        <div class="card table-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">{{ $table->name }}</h6>
                <span class="badge bg-{{ $table->status == 'available' ? 'success' : ($table->status == 'occupied' ? 'warning' : 'secondary') }}">
                    {{ ucfirst($table->status) }}
                </span>
            </div>
            <div class="card-body">
                @if($table->location)
                <p class="card-text">
                    <i class="fas fa-map-marker-alt me-2"></i>{{ $table->location }}
                </p>
                @endif

                @if($table->currentMatch)
                <div class="current-match">
                    <small class="text-muted">Current Match:</small>
                    <div class="fw-bold">
                        {{ $table->currentMatch->player1->name }} vs {{ $table->currentMatch->player2->name }}
                    </div>
                    <small class="text-muted">
                        {{ $table->currentMatch->tournament->name }}
                    </small>
                </div>
                @else
                <div class="text-muted">No active match</div>
                @endif

                @if($table->description)
                <p class="card-text mt-2">
                    <small class="text-muted">{{ Str::limit($table->description, 100) }}</small>
                </p>
                @endif

                <div class="table-stats mt-3">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h6 mb-0">{{ $table->today_usage_minutes }}m</div>
                            <small class="text-muted">Today</small>
                        </div>
                        <div class="col-6">
                            <div class="h6 mb-0">{{ $table->total_usage_minutes }}m</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('tables.show', $table) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('tables.edit', $table) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('tables.destroy', $table) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
.table-card {
    transition: transform 0.2s;
}
.table-card:hover {
    transform: translateY(-5px);
}
.current-match {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}
</style>
@endsection