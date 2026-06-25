@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Table Usage Report</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('tables.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tables
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Table Usage Summary</h6>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3 text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4>{{ $tables->count() }}</h4>
                        <small>Total Tables</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h4>{{ $tables->where('status', 'available')->count() }}</h4>
                        <small>Available</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h4>{{ $tables->where('status', 'occupied')->count() }}</h4>
                        <small>Occupied</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 text-center">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <h4>{{ $tables->where('status', 'maintenance')->count() }}</h4>
                        <small>Maintenance</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Completed Matches</th>
                        <th>Today Usage</th>
                        <th>Total Usage</th>
                        <th>Usage Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tables as $table)
                    <tr>
                        <td>
                            <strong>{{ $table->name }}</strong>
                            @if($table->currentMatch)
                            <br><small class="text-warning">Live match in progress</small>
                            @endif
                        </td>
                        <td>{{ $table->location ?: '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $table->status == 'available' ? 'success' : ($table->status == 'occupied' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($table->status) }}
                            </span>
                        </td>
                        <td>{{ $table->completed_matches_count }}</td>
                        <td>{{ $table->today_usage_minutes }}m</td>
                        <td>{{ $table->total_usage_minutes }}m</td>
                        <td>
                            @php
                                $usageRate = $totalUsage > 0 ? round(($table->total_usage_minutes / $totalUsage) * 100, 1) : 0;
                            @endphp
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-info" style="width: {{ $usageRate }}%"></div>
                            </div>
                            <small>{{ $usageRate }}%</small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <td colspan="4"><strong>Total</strong></td>
                        <td><strong>{{ $tables->sum('today_usage_minutes') }}m</strong></td>
                        <td><strong>{{ $totalUsage }}m</strong></td>
                        <td><strong>100%</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection