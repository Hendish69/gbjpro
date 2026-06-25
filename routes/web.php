<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\SetController;
use App\Http\Controllers\PTMClubController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\PlayerLibraryController;
use App\Http\Controllers\TournamentPlayerController;
use App\Http\Controllers\BracketPDFController;
use App\Http\Controllers\TournamentRegistrationController;
use App\Http\Controllers\MatchScheduleController;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MenuController;



use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});
// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::resource('menus', MenuController::class)->except(['show']);
Route::post('/menus/update-order', [MenuController::class, 'updateOrder'])->name('menus.update-order');
// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management dengan Permission
    Route::resource('users', UserController::class)->except(['show']);
    
    Route::resource('roles', RoleController::class)->except(['create', 'store', 'destroy', 'show']);
    Route::get('/check-session', [AuthController::class, 'checkSession'])->name('session.check');
    Route::post('/extend-session', [AuthController::class, 'extendSession'])->name('session.extend');
        
    Route::get('/reports', function () {
        return view('reports');
    })->middleware('permission:Reports,view')->name('reports');
});

// Fallback route
Route::fallback(function () {
    return redirect('/');
});

Route::resource('ptm', PTMClubController::class);
Route::resource('matches', MatchesController::class);

// Additional match routes
Route::prefix('matches/{match}')->group(function () {
    Route::post('/start', [MatchesController::class, 'startMatch'])->name('matches.start');
    Route::post('/cancel', [MatchesController::class, 'cancelMatch'])->name('matches.cancel');
    Route::post('/assign-table', [MatchesController::class, 'assignTable'])->name('matches.assign-table');
    Route::post('/update-status', [MatchesController::class, 'updateStatus'])->name('matches.update-status');
    Route::get('/score', [MatchesController::class, 'showScoreForm'])->name('matches.score-form');
    Route::post('/score', [MatchesController::class, 'recordScore'])->name('matches.record-score');
    Route::post('/update-schedule', [MatchScheduleController::class, 'updateMatchSchedule'])->name('matches.update-schedule');
});

// Set Routes
Route::prefix('matches/{match}/sets')->group(function () {
    Route::post('/', [SetController::class, 'store'])->name('sets.store');
    Route::put('/{set}', [SetController::class, 'update'])->name('sets.update');
    Route::delete('/{set}', [SetController::class, 'destroy'])->name('sets.destroy');
});

// API Routes
Route::get('/tournaments/{tournament}/matches', [MatchesController::class, 'byTournament'])->name('matches.by-tournament');

// Additional routes for tournament operations
Route::get('/bracket-pdf/{tournament}', [BracketPDFController::class, 'generate'])->name('bracket-pdf');
Route::get('/doubleBracket/{tournament}', [TournamentController::class, 'doubleBracket'])->name('doubleBracket');;


// Match routess
// Route::post('/matches/{match}/record-score', [MatchController::class, 'recordScore'])
//     ->name('matches.record-score');
// Route::post('/matches/{match}/start', [MatchController::class, 'startMatch'])
//     ->name('matches.start');
// Route::post('/matches/{match}/complete', [MatchController::class, 'completeMatch'])
//     ->name('matches.complete');
    
// Route::resource('matches', MatchController::class);
// Route::post('/matches/{match}/record-score', [MatchController::class, 'recordScore'])->name('matches.record-score');
// Route::post('/matches/{match}/start', [MatchController::class, 'startMatch'])->name('matches.start');
// Route::post('/matches/{match}/complete', [MatchController::class, 'completeMatch'])->name('matches.complete');
// Route::get('/matches/{match}/record-score', [MatchController::class, 'showRecordScoreForm'])->name('matches.record-score-form');
    // Table routes
Route::resource('tables', TableController::class);
Route::get('/tables-usage-report', [TableController::class, 'usageReport'])->name('tables.usage-report');
// Tournament drawing routes
Route::resource('tournaments', TournamentController::class);

// Group untuk route tambahan yang berhubungan dengan satu tournament
Route::prefix('tournaments/{tournament}')
    ->name('tournaments.')
    ->group(function () {
    Route::get('/registration-management', [TournamentRegistrationController::class, 'manageRegistration'])->name('registration-management');
    Route::post('/register-single-player', [TournamentRegistrationController::class, 'registerSinglePlayer'])->name('register-single-player');
    Route::post('/create-duo-pair', [TournamentRegistrationController::class, 'createDuoPair'])->name('create-duo-pair');
    Route::post('/auto-pair-players', [TournamentRegistrationController::class, 'autoPairPlayers'])->name('auto-pair-players');
    Route::post('/register-team', [TournamentRegistrationController::class, 'registerTeam'])->name('register-team');
    Route::post('/remove-participant', [TournamentRegistrationController::class, 'removeParticipant'])->name('remove-participant');
    Route::get('/available-players-ajax', [TournamentRegistrationController::class, 'getAvailablePlayers'])->name('available-players-ajax');

    // Schedule management - INTEGRASI DENGAN YANG SUDAH ADA
    Route::get('/schedule-management', [MatchScheduleController::class, 'createSchedule'])->name('schedule-management');
    Route::post('/schedule-single-match', [MatchScheduleController::class, 'generateSingleMatch'])->name('schedule-single-match');
    Route::post('/schedule-round-matches', [MatchScheduleController::class, 'generateRoundMatches'])->name('schedule-round-matches');
    Route::post('/generate-elimination-bracket', [MatchScheduleController::class, 'generateEliminationBracket'])->name('generate-elimination-bracket');
    Route::get('/optimization-data', [MatchScheduleController::class, 'getOptimization'])->name('optimization-data');

        Route::post('generate-bracket', [TournamentController::class, 'generateBracket'])->name('generate-bracket');
        Route::delete('/', [TournamentController::class, 'destroy'])->name('destroy');
        Route::post('/', [TournamentController::class, 'store'])->name('store');
        // Route::post('deleted', [TournamentController::class, 'destroy'])->name('destroy');
        Route::get('draw', [TournamentController::class, 'showDrawForm'])->name('draw');
        Route::post('generate-draw', [TournamentController::class, 'generateDraw'])->name('generate-draw');
        Route::post('/update-status', [TournamentController::class, 'updateStatus'])->name('update-status');
        Route::get('bracket', [TournamentController::class, 'showBracket'])->name('bracket');
            // Pairing routes
        Route::get('pairing', [TournamentController::class, 'showPairingForm'])->name('pairing');
        Route::post('generate-pairs', [TournamentController::class, 'generatePairs'])->name('generate-pairs');
        Route::post('add-player', [TournamentController::class, 'addPlayer'])->name('add-player');
        Route::put('update-player/{player}', [TournamentController::class, 'updatePlayer'])->name('updatePlayer');
        Route::post('change-player/{player}', [TournamentController::class, 'changePlayer'])->name('change-player'); // ← NEW ROUTE
        Route::delete('remove-player/{player}', [TournamentController::class, 'removePlayer'])->name('remove-player');
        Route::get('statistics', [TournamentController::class, 'statistics'])->name('statistics');
        Route::get('optimize-tables', [TournamentController::class, 'optimizeTables'])->name('optimize-tables');

        Route::post('add-pair', [TournamentController::class, 'addPair'])->name('add-pair');
        Route::post('create-pair', [TournamentController::class, 'createPair'])->name('create-pair');
        Route::post('save-pairing-progress', [TournamentController::class, 'savePairingProgress'])->name('save-pairing-progress');
        Route::delete('remove-pair/{pair}', [TournamentController::class, 'removePair'])->name('remove-pair');

        Route::prefix('players')->name('players.')->group(function () {
            Route::get('/', [TournamentPlayerController::class, 'index'])->name('index');
            Route::get('/create', [TournamentPlayerController::class, 'create'])->name('create');
            Route::post('/', [TournamentPlayerController::class, 'store'])->name('store');
            Route::get('/{tournamentPlayer}/edit', [TournamentPlayerController::class, 'edit'])->name('edit');
            Route::put('/{tournamentPlayer}', [TournamentPlayerController::class, 'update'])->name('update');
            Route::delete('/{tournamentPlayer}', [TournamentPlayerController::class, 'destroy'])->name('destroy');

             // Bulk actions
            Route::post('/bulk-assign-seeds', [TournamentPlayerController::class, 'bulkAssignSeeds'])->name('bulk-assign-seeds');
            Route::post('/bulk-assign-groups', [TournamentPlayerController::class, 'bulkAssignGroups'])->name('bulk-assign-groups');

        });
        // Keep compatibility routes for existing functionality
        // Route::post('add-players', [TournamentController::class, 'addPlayers'])->name('add-players');
        // Route::post('add-new-player', [TournamentController::class, 'addNewPlayer'])->name('add-new-player');
        // Route::post('update-representation/{player}', [TournamentController::class, 'updatePlayerRepresentation'])->name('update-representation');
        // Route::delete('remove-player/{player}', [TournamentController::class, 'removePlayerFromTournament'])->name('remove-player');


    });
// Tournament Player Routes
// Route::prefix('tournaments/{tournament}/players')->name('tournaments.players.')->group(function () {
//     Route::get('/', [TournamentPlayerController::class, 'index'])->name('index');
//     Route::get('/create', [TournamentPlayerController::class, 'create'])->name('create');
//     Route::post('/', [TournamentPlayerController::class, 'store'])->name('store');
//     Route::get('/{tournamentPlayer}/edit', [TournamentPlayerController::class, 'edit'])->name('edit');
//     Route::put('/{tournamentPlayer}', [TournamentPlayerController::class, 'update'])->name('update');
//     Route::delete('/{tournamentPlayer}', [TournamentPlayerController::class, 'destroy'])->name('destroy');
    
//     // Bulk actions
//     Route::post('/bulk-assign-seeds', [TournamentPlayerController::class, 'bulkAssignSeeds'])->name('bulk-assign-seeds');
//     Route::post('/bulk-assign-groups', [TournamentPlayerController::class, 'bulkAssignGroups'])->name('bulk-assign-groups');
// });

// // Keep existing routes for compatibility
// Route::get('tournaments/{tournament}/players', [TournamentController::class, 'showTournamentPlayers'])->name('tournaments.players');




// Route::resource('tournaments', TournamentController::class);
// Route::post('/tournaments/{tournament}/generate-bracket', [TournamentController::class, 'generateBracket'])
//     ->name('tournaments.generate-bracket');
// Route::get('/tournaments/{tournament}/draw', [TournamentController::class, 'showDrawForm'])->name('tournaments.draw');
// Route::post('/tournaments/{tournament}/add-players', [TournamentController::class, 'addPlayers'])->name('tournaments.add-players');
// Route::post('/tournaments/{tournament}/generate-draw', [TournamentController::class, 'generateDraw'])->name('tournaments.generate-draw');
// Route::get('/tournaments/{tournament}/bracket', [TournamentController::class, 'showBracket'])->name('tournaments.bracket');
// Route::get('/tournaments/{tournament}/players', [TournamentController::class, 'showTournamentPlayers'])->name('tournaments.players');
// Route::post('/tournaments/{tournament}/update-representation/{player}', [TournamentController::class, 'updatePlayerRepresentation'])->name('tournaments.update-representation');
// Route::delete('/tournaments/{tournament}/remove-player/{player}', [TournamentController::class, 'removePlayerFromTournament'])->name('tournaments.remove-player');
// Route::post('/tournaments/{tournament}/add-new-player', [TournamentController::class, 'addNewPlayer']);

// Player Library Routes
// Player Library Routes
// =====================
// 🧩 PLAYER ROUTES
// =====================
Route::prefix('players')->name('players.')->group(function () {
    // 🔍 Search route
    Route::get('/search', [PlayerController::class, 'search'])->name('search');
    // Tambahkan route AJAX check duplicate di sini
    Route::get('/check-duplicate', [PlayerController::class, 'checkDuplicate'])->name('check-duplicate');
    // 📋 Player resource utama
    Route::get('-index/', [PlayerController::class, 'index'])->name('index');
    Route::get('/create', [PlayerController::class, 'create'])->name('create');
    // ➕ Create dari Club (auto-fill club)
    Route::get('/create/from-club/{club}', [PlayerController::class, 'create'])
        ->name('create.fromClub');
    Route::post('-index/', [PlayerController::class, 'store'])->name('store');
    Route::get('/{player}', [PlayerController::class, 'show'])->name('show');
    Route::get('/{player}/edit', [PlayerController::class, 'edit'])->name('edit');
    Route::put('/{player}', [PlayerController::class, 'update'])->name('update');
    Route::delete('/{player}', [PlayerController::class, 'destroy'])->name('destroy');
   

    // 🗂️ Player Library
    Route::prefix('library')->name('library.')->group(function () {
        Route::get('/', [PlayerLibraryController::class, 'index'])->name('index');
        Route::get('/create', [PlayerLibraryController::class, 'create'])->name('create');
        Route::post('/', [PlayerLibraryController::class, 'store'])->name('store');
        Route::get('/{player}', [PlayerLibraryController::class, 'show'])->name('show');
        Route::get('/{player}/edit', [PlayerLibraryController::class, 'edit'])->name('edit');
        Route::put('/{player}', [PlayerLibraryController::class, 'update'])->name('update');
        Route::delete('/{player}', [PlayerLibraryController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-actions', [PlayerLibraryController::class, 'bulkActions'])->name('bulk-actions');
        Route::get('/json', [PlayerLibraryController::class, 'getPlayersJson'])->name('json');
    });
});



// PTM Clubs Routes
Route::resource('ptm-clubs', PTMClubController::class);
Route::get('ptm-clubs/{player}/edit', [PlayerLibraryController::class, 'edit'])->name('ptm-clubs.edit');
Route::put('ptm-clubs/{player}', [PlayerLibraryController::class, 'update'])->name('ptm-clubs.update');
Route::post('/ptm-clubs/bulk-actions', [PTMClubController::class, 'bulkActions'])->name('ptm-clubs.bulk-actions');