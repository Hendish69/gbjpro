<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GBJPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="{{ asset('images/GbjPro.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
            --success-color: #198754;
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
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
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
            background-color: #ffc107;
            color: var(--dark-color);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .player-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user"></i> Admin</a>
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
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar d-none d-md-block">
                <div class="pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('ptm*') ? 'active' : '' }}" href="{{ route('ptm.index') }}">
                                <i class="fas fa-users"></i> PTM Names
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
                    </ul>
                </div>
            </div>
            <!-- Main content -->
            <div class="col-md-10 ms-sm-auto px-4 py-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>