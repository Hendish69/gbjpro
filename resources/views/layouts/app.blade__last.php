<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GBJPro - @yield('title', 'Table Tennis Management')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('images/GbjPro.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tambahkan DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background: linear-gradient(180deg, var(--dark-color) 0%, #343a40 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
            border-left-color: var(--primary-color);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .bg-gradient-primary {
            background: linear-gradient(45deg, var(--primary-color), #e95c6d);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .badge-live {
            background-color: var(--warning-color);
            color: var(--dark-color);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .player-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Breadcrumb improvements */
        .breadcrumb {
            background: transparent;
            padding: 0;
        }
        
        .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        /* Button improvements */
        .btn {
            border-radius: 6px;
            font-weight: 500;
        }
        
        /* Mobile responsive improvements */
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .navbar-brand .d-flex {
                flex-direction: column;
                text-align: center;
            }
            
            .navbar-brand h2 {
                font-size: 1.2rem;
            }
            
            .navbar-brand p {
                font-size: 0.8rem;
            }
        }
        
        /* Tournament status badges */
        .badge-status-scheduled { background-color: var(--secondary-color); }
        .badge-status-ongoing { background-color: var(--warning-color); color: #000; }
        .badge-status-completed { background-color: var(--success-color); }
        .badge-status-cancelled { background-color: var(--primary-color); }
        
        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #343a40;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 3px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <img src="{{ asset('images/GbjPro.png') }}" 
                        alt="Logo PTM GBJ PRO" 
                        class="rounded-circle border border-2 border-white shadow-sm"
                        style="width: 90px; height: 90px; object-fit: contain;">
                    <div class="text-start">
                        <h2 class="fw-bold mb-1">
                            PTM GBJ PRO
                        </h2>
                        <p class="mb-0 fs-5">
                            Griya Bukit Jaya – Gunung Putri, Bogor
                        </p>
                    </div>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mx-3 mt-3" role="alert">
        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar d-none d-md-block">
                <div class="pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('ptm*') ? 'active' : '' }}" href="{{ route('ptm.index') }}">
                                <i class="fas fa-users"></i> PTM Clubs
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('players*') ? 'active' : '' }}" href="{{ route('players.index') }}">
                                <i class="fas fa-table-tennis-paddle-ball"></i> Players
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('tournaments*') ? 'active' : '' }}" href="{{ route('tournaments.index') }}">
                                <i class="fas fa-trophy"></i> Tournaments
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('matches*') ? 'active' : '' }}" href="{{ route('matches.index') }}">
                                <i class="fas fa-medal"></i> Matches
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('tables*') ? 'active' : '' }}" href="{{ route('tables.index') }}">
                                <i class="fas fa-table"></i> Tables
                            </a>
                        </li>
                        
                        <!-- Quick Actions Section -->
                        <li class="nav-item mt-3">
                            <small class="text-uppercase text-muted px-3">Quick Tournament Actions</small>
                        </li>

                        <!-- @if(isset($activeTournament) && $activeTournament) -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('tournaments.registration-management', $activeTournament->id) }}">
                                <i class="fas fa-user-plus"></i> Tournament Registration
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('tournaments.schedule-management', $activeTournament->id) }}">
                                <i class="fas fa-calendar-plus"></i> Tournament Schedule
                            </a>
                        </li>
                        <!-- @endif -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('players/library*') ? 'active' : '' }}" href="{{ route('players.library.index') }}">
                                <i class="fas fa-book"></i> Player Library
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('tables.usage-report') }}">
                                <i class="fas fa-chart-bar"></i> Table Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-10 ms-sm-auto px-4 py-4">
                <!-- Page Title & Breadcrumb -->
                @hasSection('page-title')
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="page-title-box">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="page-title mb-0">@yield('page-title')</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb mb-0">
                                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                            @yield('breadcrumb')
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-auto">
                                    @yield('page-actions')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Main Content -->
                @yield('content')
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Global CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Auto-dismiss alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });

        // Loading state for buttons
        $(document).on('click', '.btn-loading', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="loading-spinner me-2"></span>Loading...');
        });

        // Confirm deletion
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }

        // Initialize DataTables with common settings
        function initDataTable(selector, options = {}) {
            const defaultOptions = {
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                    lengthMenu: "_MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                pageLength: 25,
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
            };

            return $(selector).DataTable({
                ...defaultOptions,
                ...options
            });
        }

        // Tournament type badges
        function getTournamentTypeBadge(type) {
            const types = {
                'single': 'bg-primary',
                'double': 'bg-success', 
                'duo': 'bg-info',
                'team': 'bg-warning text-dark'
            };
            return `<span class="badge ${types[type] || 'bg-secondary'}">${type.toUpperCase()}</span>`;
        }

        // Status badges
        function getStatusBadge(status) {
            const statuses = {
                'scheduled': 'badge-status-scheduled',
                'ongoing': 'badge-status-ongoing',
                'completed': 'badge-status-completed',
                'cancelled': 'badge-status-cancelled'
            };
            return `<span class="badge ${statuses[status] || 'bg-secondary'}">${status.toUpperCase()}</span>`;
        }
    </script>

    @stack('scripts')
</body>
</html>