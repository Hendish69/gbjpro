<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Player;
use App\Models\Matches;
use App\Models\PTMClub;
use App\Models\PlayerCategory;
use App\Models\TournamentDuoPair;
use App\Models\TournamentPlayer;

use Illuminate\Http\Request;
use App\Services\TournamentDrawService;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        // $tournaments = Tournament::withCount('matches')->latest()->get();
         $tournaments = Tournament::withCount(['matches', 'players'])
        ->latest()->paginate(10);
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Search by name
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

       

    
        return view('tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        $types = Tournament::VALID_TYPES;
        $clubs = PtmClub::active()->get();
        
        return view('tournaments.create', compact('types', 'clubs'));
    }
public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', Tournament::VALID_TYPES),
            'format' => 'required|string',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before:start_date',
            'max_players' => 'required|integer|min:2',
            'max_teams' => 'nullable|integer',
            'status' => 'required|string',
            
            // Time management fields
            'available_tables' => 'required|integer|min:1',
            'matches_per_table' => 'required|integer|min:1',
            'estimated_match_duration' => 'required|integer|min:1',
            'break_between_matches' => 'required|integer|min:0',
            'warmup_time' => 'required|integer|min:0',
            'daily_start_time' => 'required|date_format:H:i',
            'daily_end_time' => 'required|date_format:H:i|after:daily_start_time',
            'max_daily_playing_hours' => 'required|integer|min:1|max:24',
        ]);

        try {
            DB::beginTransaction();

            // Calculate estimated duration
            $tournament = new Tournament($validated);
            $estimatedDuration = $tournament->estimateTotalDuration();
            $validated['estimated_duration_minutes'] = $estimatedDuration;

            $tournament = Tournament::create($validated);

            DB::commit();

            return redirect()->route('tournaments.show', $tournament)
                ->with('success', 'Tournament created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create tournament: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    // TournamentController.php - show method
// Di TournamentController - show method, update bagian duo
public function show(Tournament $tournament)
{
    // Load relationships
    $tournament->load([
        'matches.player1', 
        'matches.player2',
        'players.ptmClub',
        'tournamentPlayers.player.ptmClub',
        'tournamentPlayers.representingClub',
        'duoPairs.player1.ptmClub', 
        'duoPairs.player2.ptmClub'
    ]);

    // Data untuk modal add player
    $availablePlayers = \App\Models\Player::whereNotIn('id', function($query) use ($tournament) {
        $query->select('player_id')
              ->from('tournament_player')
              ->where('tournament_id', $tournament->id);
    })->with('ptmClub')->OrderBy('nickname')->get();

    $clubs = \App\Models\PtmClub::active()->get();

    // Data untuk duo tournaments
    $duoPairs = collect();
    $unpairedPlayers = collect();
    $availableForPairing = collect();
    if ($tournament->isDuoType()) {
        $availableForPairing = $tournament->getAvailablePlayersForPairing();
    }
    // dd($tournament,$availableForPairing);
    $scheduleEstimation = $tournament->getScheduleEstimation();
    $tableStatistics = $tournament->getTableStatistics();
    $optimization = $tournament->optimizeTableAllocation();

    return view('tournaments.show', compact(
        'tournament', 
        'scheduleEstimation', 
        'tableStatistics',
        'optimization',
        'availablePlayers',
        'clubs',
        'duoPairs',
        'unpairedPlayers',
        'availableForPairing'
    ));
}

// TournamentController.php - perbaiki method createPair
/**
 * Create pair from existing individual players (manual pairing)
 */
public function savePairingProgress(Request $request, Tournament $tournament)
{
    try {
        $pairs = $request->input('pairs', []);

        if (empty($pairs)) {
            return response()->json([
                'success' => false,
                'message' => 'No pairing data provided.'
            ], 400);
        }

        DB::beginTransaction();

        foreach ($pairs as $pair) {
            // Pastikan ID-nya ada
            if (empty($pair['player1_id']) || empty($pair['player2_id'])) {
                continue;
            }

            // Cek apakah pasangan ini sudah ada (hindari duplikasi)
            $existing = TournamentDuoPair::where('tournament_id', $tournament->id)
                ->where('player1_id', $pair['player1_id'])
                ->where('player2_id', $pair['player2_id'])
                ->first();

            if ($existing) {
                continue; // skip kalau sudah ada
            }

            TournamentDuoPair::create([
                'tournament_id'   => $tournament->id,
                'player1_id'      => $pair['player1_id'],
                'player1_club_id' => $pair['player1_club_id'] ?? null,
                'player2_id'      => $pair['player2_id'],
                'player2_club_id' => $pair['player2_club_id'] ?? null,
                'pair_name'       => 'Pair ' . $pair['pair_index'],
                'team_name'       => 'double_' . $pair['pair_index'],
                'status'          => 'confirmed',
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pairs saved successfully!',
            'count'   => count($pairs)
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Error saving pairs: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}


public function createPair(Request $request, Tournament $tournament)
{
    if (!$tournament->isDuoType()) {
        return back()->with('error', 'This tournament does not support pair creation.');
    }

    $validated = $request->validate([
        'player1_id' => 'required|exists:players,id',
        'player2_id' => 'required|exists:players,id|different:player1_id',
        'pair_name' => 'nullable|string|max:255',
        'team_name' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:500',
    ]);
   
    try {
        // Cek apakah players sudah terdaftar di tournament
        $player1Registered = $tournament->players()->where('player_id', $validated['player1_id'])->exists();
        $player2Registered = $tournament->players()->where('player_id', $validated['player2_id'])->exists();

        if (!$player1Registered || !$player2Registered) {
            return back()->with('error', 'Both players must be registered in the tournament first.');
        }

        // Cek apakah players sudah dipasangkan
        if ($tournament->isPlayerInPair($validated['player1_id'])) {
            return back()->with('error', 'Player 1 is already in another pair.');
        }

        if ($tournament->isPlayerInPair($validated['player2_id'])) {
            return back()->with('error', 'Player 2 is already in another pair.');
        }

        DB::beginTransaction();

        // Create the pair
        $pair = TournamentDuoPair::create([
            'tournament_id' => $tournament->id,
            'player1_id' => $validated['player1_id'],
            'player2_id' => $validated['player2_id'],
            'pair_name' => $validated['pair_name'],
            'team_name' => $validated['team_name'],
            'notes' => $validated['notes'],
            'status' => 'confirmed'
        ]);

        DB::commit();

        return back()->with('success', 'Player pair created successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error creating pair: ' . $e->getMessage());
        return back()->with('error', 'Failed to create player pair: ' . $e->getMessage());
    }
}

   /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tournament $tournament)
    {
        $types = Tournament::VALID_TYPES;
        $clubs = PtmClub::active()->get(); // Jika diperlukan

        // Load relationships untuk statistics
        $tournament->load(['players', 'matches']);

        return view('tournaments.edit', compact('tournament', 'types', 'clubs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tournament $tournament)
    {
       \Log::info('UPDATE TOURNAMENT REQUEST', $request->all()); // TAMBAHKAN
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', Tournament::VALID_TYPES),
            'format' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'required|date|before:start_date',
            'max_players' => 'required|integer|min:2',
            'max_teams' => 'nullable|integer|min:1',
            'status' => 'required|string',
            
            // Time management fields
            'available_tables' => 'required|integer|min:1',
            'matches_per_table' => 'required|integer|min:1',
            'estimated_match_duration' => 'required|integer|min:1',
            'break_between_matches' => 'required|integer|min:0',
            'warmup_time' => 'required|integer|min:0',
            'daily_start_time' => 'required|date_format:H:i',
            'daily_end_time' => 'required|date_format:H:i|after:daily_start_time',
            'max_daily_playing_hours' => 'required|integer|min:1|max:24',
        ]);
        \Log::info('VALIDATED DATA', $validated); // TAMBAHKAN
        try {
            DB::beginTransaction();

            // Recalculate estimated duration if relevant fields changed
            $relevantFields = [
                'available_tables', 'matches_per_table', 'estimated_match_duration',
                'break_between_matches', 'warmup_time', 'daily_start_time', 
                'daily_end_time', 'max_daily_playing_hours', 'type', 'max_players'
            ];

            $needsRecalculation = false;
            foreach ($relevantFields as $field) {
                if ($tournament->$field != $validated[$field]) {
                    $needsRecalculation = true;
                    break;
                }
            }

            if ($needsRecalculation) {
                $tempTournament = new Tournament($validated);
                $validated['estimated_duration_minutes'] = $tempTournament->estimateTotalDuration();
            }

            $tournament->update($validated);

            DB::commit();

            return redirect()->route('tournaments.show', $tournament)
                ->with('success', 'Tournament updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('UPDATE FAILED', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update tournament: ' . $e->getMessage())
                ->withInput();
        }
    }
    public function doubleBracket($tournamentId)
{
    $tournament = Tournament::with('players')->with('duoPairs')->findOrFail($tournamentId);
    $tournament->load([
        'duoPairs.player1.ptmClub', 
        'duoPairs.player2.ptmClub'
    ]);
    // dd( $tournament);
   

    $bracket = $this->generateDoubleEliminationBracket( $tournament);

    return view('tournaments.double-bracket', compact('bracket', 'tournament'));
}

 public function generateDoubleEliminationBracket($participants)
{
    // dd($participants->getAvailablePlayersForPairing());
    $participants = $participants->duoPairs->toarray();
    usort($participants, function($a, $b) {
        // data sudah harus di susun by sequence, seq Area 2 kemudian Area 3 seq berikutnya
        if ($a['id'] > $b['id']) {
            return 1;
        } elseif ($a['id'] < $b['id']) {
            return -1;
        }
        return 0;
    });
    $n = count($participants);
    if ($n < 2) return null;

    // 1. Hitung slot Winner Bracket (power of 2)
    $wbSlots = pow(2, ceil(log($n, 2)));
    $wbByes = $wbSlots - $n;

    // 2. Acak & tambahkan BYE ke WB
    shuffle($participants);
    $wb = $participants;
    for ($i = 0; $i < $wbByes; $i++) {
        $wb[] = null; // BYE
    }

    // 3. Simulasi Winner Bracket
    $wbRounds = [];
    $currentWB = $wb;
    $losersToLB = [];
    // dd($currentWB,$wb, $wbSlots, $wbByes, shuffle($participants) );
    while (count($currentWB) > 1) {
        $nextRound = [];
        $matches = [];

        for ($i = 0; $i < count($currentWB); $i += 2) {
            $p1 = $currentWB[$i];
            $p2 = $currentWB[$i + 1] ?? null;
            // dd( $p1, $p2);
            // Tentukan pemenang (acak untuk simulasi)
            $winner = $p1 && $p2 ? ($p1['id'] % 2 == 0 ? $p1 : $p2) : ($p1 ?: $p2);
            $loser = ($p1 && $p2) ? ($winner === $p1 ? $p2 : $p1) : null;

            $matches[] = [
                'p1' => $p1?->name ?? 'BYE',
                'p2' => $p2?->name ?? 'BYE',
                'winner' => $winner?->name ?? 'BYE',
                'loser' => $loser?->name ?? null,
            ];

            $nextRound[] = $winner;
            if ($loser) $losersToLB[] = $loser;
        }

        $wbRounds[] = $matches;
        $currentWB = $nextRound;
    }

    $wbChampion = $currentWB[0] ?? null;

    // 4. Loser Bracket (dari yang kalah di WB)
    $lbRounds = [];
    $currentLB = $losersToLB;

    // Tambahkan BYE jika perlu (power of 2)
    $lbSlots = pow(2, ceil(log(count($currentLB), 2)));
    $lbByes = $lbSlots - count($currentLB);
    for ($i = 0; $i < $lbByes; $i++) {
        $currentLB[] = null;
    }

    while (count($currentLB) > 1) {
        $nextRound = [];
        $matches = [];

        for ($i = 0; $i < count($currentLB); $i += 2) {
            $p1 = $currentLB[$i];
            $p2 = $currentLB[$i + 1] ?? null;

            $winner = $p1 && $p2 ? ($p1['id'] % 2 == 1 ? $p1 : $p2) : ($p1 ?: $p2);
            $loser = ($p1 && $p2) ? ($winner === $p1 ? $p2 : $p1) : null;
            // dd($winner,$loser);
            $matches[] = [
                'p1' => $p1?->team_name ?? 'BYE',
                'p2' => $p2?->team_name ?? 'BYE',
                'winner' => $winner?->team_name ?? 'BYE',
            ];
            // dd($matches,$p1);
            $nextRound[] = $winner;
        }

        $lbRounds[] = $matches;
        $currentLB = $nextRound;
    }

    $lbChampion = $currentLB[0] ?? null;
    // dd( $lbChampion);
    // 5. Grand Final
    $grandFinal = null;
    if ($wbChampion && $lbChampion) {
        $gfWinner = $wbChampion['id'] % 2 == 0 ? $wbChampion : $lbChampion;
        $grandFinal = [
            'wb_champion' => $wbChampion['team_name'],
            'lb_champion' => $lbChampion['team_name'],
            'champion' => $gfWinner['team_name'],
            'needs_second_match' => $gfWinner === $lbChampion,
        ];
    }

    return [
        'total_participants' => $n,
        'wb_slots' => $wbSlots,
        'wb_byes' => $wbByes,
        'lb_slots' => $lbSlots,
        'lb_byes' => $lbByes,
        'winner_bracket' => $wbRounds,
        'loser_bracket' => $lbRounds,
        'grand_final' => $grandFinal,
        'champion' => $grandFinal['champion'] ?? 'BYE',
    ];
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tournament $tournament)
    {
        try {
            DB::beginTransaction();

            // Check if tournament has matches
            if ($tournament->matches()->exists()) {
                return back()->with('error', 'Cannot delete tournament with existing matches.');
            }
            dd($tournament,'$tournament');
            // Delete related tournament players
            $tournament->tournamentPlayers()->delete();
            
            $tournament->delete();

            DB::commit();

            return redirect()->route('tournaments.index')
                ->with('success', 'Tournament deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete tournament: ' . $e->getMessage());
        }
    }


    /**
 * Remove player from tournament
 */
public function removePlayer(Tournament $tournament, Player $player)
{
    try {
        // Untuk duo tournaments, cek apakah player dalam pair
        if ($tournament->isDuoType()) {
            $playerPair = $tournament->getPlayerPair($player->id);
            if ($playerPair) {
                return back()->with('error', 'Cannot remove player who is in a pair. Please remove the pair first.');
            }
        }

        $tournament->players()->detach($player->id);

        return back()->with('success', 'Player removed from tournament successfully.');

    } catch (\Exception $e) {
        \Log::error('Error removing player: ' . $e->getMessage());
        return back()->with('error', 'Failed to remove player: ' . $e->getMessage());
    }
}
    /**
     * Add player to tournament with representation
     */
    /**
 * Add single player to tournament
 */
/**
 * Add single player to tournament - Safe version
 */
    public function addPlayer(Request $request, Tournament $tournament)
    {
        \Log::info('Add Player Request Data:', $request->all());

        // Manual validation untuk menghindari undefined array key
        $playerId = $request->input('player_id');
        $representingClubId = $request->input('representing_ptm_club_id');
        $seed = $request->input('seed');
        $group = $request->input('group');
        $representationNotes = $request->input('representation_notes');

        // Validasi manual
        if (!$playerId) {
            return back()->with('error', 'Player selection is required.');
        }

        try {
            // Cek apakah player exists
            $player = \App\Models\Player::find($playerId);
            if (!$player) {
                return back()->with('error', 'Selected player does not exist.');
            }

            // Cek apakah player sudah terdaftar
            if ($tournament->players()->where('player_id', $playerId)->exists()) {
                return back()->with('error', 'Player is already registered in this tournament.');
            }

            // Cek apakah tournament sudah penuh
            if ($tournament->players()->count() >= $tournament->max_players) {
                return back()->with('error', 'Tournament is already full. Cannot add more players.');
            }

            // Untuk duo tournaments, cek apakah player sudah dipasangkan
            if ($tournament->isDuoType() && $tournament->isPlayerInPair($playerId)) {
                return back()->with('error', 'Player is already in a pair in this tournament.');
            }

            // Determine club representation
            $isRepresentingDifferentClub = false;
            $finalRepresentingClubId = $representingClubId;

            if (!$finalRepresentingClubId) {
                // Jika tidak ada club yang dipilih, gunakan club player
                $finalRepresentingClubId = $player->ptm_club_id;
            } elseif ($finalRepresentingClubId != $player->ptm_club_id) {
                $isRepresentingDifferentClub = true;
            }

            // Prepare data untuk pivot table
            $pivotData = [
                'representing_ptm_club_id' => $finalRepresentingClubId,
                'is_representing_different_club' => $isRepresentingDifferentClub,
                'representation_notes' => $representationNotes,
                'seed' => $seed,
                'group' => $group
            ];

            // Remove null values
            $pivotData = array_filter($pivotData, function($value) {
                return !is_null($value);
            });

            \Log::info('Pivot data for player attachment:', $pivotData);

            // Tambahkan player ke tournament
            $tournament->players()->attach($playerId, $pivotData);

            \Log::info('Player added successfully to tournament');

            return back()->with('success', 'Player added to tournament successfully.');

        } catch (\Exception $e) {
            \Log::error('Error adding player: ' . $e->getMessage());
            \Log::error('Error trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Failed to add player: ' . $e->getMessage());
        }
    }
    public function addPair(Request $request, Tournament $tournament)
    {
        // Validasi tournament type
        if (!$tournament->isDuoType()) {
            return back()->with('error', 'This tournament does not support pair registration.');
        }

        $validated = $request->validate([
            'player1_id' => 'required|exists:players,id',
            'player2_id' => 'required|exists:players,id|different:player1_id',
            'player1_club_id' => 'nullable|exists:ptm_clubs,id',
            'player2_club_id' => 'nullable|exists:ptm_clubs,id',
            'pair_name' => 'nullable|string|max:255',
            'team_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Cek apakah players sudah terdaftar di tournament ini
            $existingPlayers = $tournament->players()
                ->whereIn('player_id', [$validated['player1_id'], $validated['player2_id']])
                ->pluck('player_id')
                ->toArray();

            if (!empty($existingPlayers)) {
                $existingNames = \App\Models\Player::whereIn('id', $existingPlayers)->pluck('name')->toArray();
                return back()->with('error', 'Some players are already registered: ' . implode(', ', $existingNames));
            }
            // dd($request,$tournament);
            // Cek apakah tournament sudah penuh
            $currentPlayerCount = $tournament->players()->count();
            if ($currentPlayerCount + 2 > $tournament->max_players) {
                return back()->with('error', 'Tournament is full. Cannot add more players.');
            }

            DB::beginTransaction();

            // Create the pair
            $pair = TournamentDuoPair::create([
                'tournament_id' => $tournament->id,
                'player1_id' => $validated['player1_id'],
                'player2_id' => $validated['player2_id'],
                'player1_club_id' => $validated['player1_club_id'] ?? null,
                'player2_club_id' => $validated['player2_club_id'] ?? null,
                'pair_name' => $validated['pair_name'],
                'team_name' => $validated['team_name'],
                'notes' => $validated['notes'],
                'status' => 'confirmed'
            ]);

            // Add players to tournament
            $player1 = \App\Models\Player::find($validated['player1_id']);
            $player2 = \App\Models\Player::find($validated['player2_id']);

            // Add player 1
            $tournament->players()->attach($player1->id, [
                'representing_ptm_club_id' => $validated['player1_club_id'] ?? null,
                'is_representing_different_club' => $validated['player1_club_id'] && $validated['player1_club_id'] != $player1->ptm_club_id,
                'representation_notes' => 'Part of pair: ' . ($validated['pair_name'] ?? 'Team ' . ($validated['team_name'] ?? 'Unknown'))
            ]);

            // Add player 2
            $tournament->players()->attach($player2->id, [
                'representing_ptm_club_id' => $validated['player2_club_id'] ?? null,
                'is_representing_different_club' => $validated['player2_club_id'] && $validated['player2_club_id'] != $player2->ptm_club_id,
                'representation_notes' => 'Part of pair: ' . ($validated['pair_name'] ?? 'Team ' . ($validated['team_name'] ?? 'Unknown'))
            ]);

            DB::commit();

            return back()->with('success', 'Player pair added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error adding player pair: ' . $e->getMessage());
            return back()->with('error', 'Failed to add player pair: ' . $e->getMessage());
        }
    }

/**
 * Remove pair from tournament
 */
    public function removePair(Tournament $tournament, TournamentDuoPair $pair)
    {
        if ($pair->tournament_id !== $tournament->id) {
            return back()->with('error', 'Invalid pair for this tournament.');
        }

        try {
            DB::beginTransaction();

            // Remove players from tournament
            $tournament->players()->detach([$pair->player1_id, $pair->player2_id]);

            // Delete the pair
            $pair->delete();

            DB::commit();

            return back()->with('success', 'Pair removed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error removing pair: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove pair: ' . $e->getMessage());
        }
    }
   
    /**
     * Generate tournament draw
     */
    public function generateDraw(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'method' => 'required|in:random,seeded,manual'
        ]);

        try {
            $draw = $tournament->generateDraw($validated['method']);

            return back()->with('success', 'Tournament draw generated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate draw: ' . $e->getMessage());
        }
    }

    /**
     * Get bracket data
     */
    public function getBracket(Tournament $tournament)
    {
        try {
            $bracketData = $tournament->getBracketData();

            return response()->json([
                'success' => true,
                'bracket' => $bracketData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tournament status
     */
    public function updateStatus(Request $request, Tournament $tournament)
{
    $validStatuses = ['pending', 'registration_open', 'ongoing', 'completed', 'cancelled'];
    
    $validated = $request->validate([
        'status' => 'required|in:' . implode(',', $validStatuses)
    ]);

    try {
        DB::beginTransaction();

        $oldStatus = $tournament->status;
        $newStatus = $validated['status'];
        
        $tournament->update(['status' => $newStatus]);

        // Jika status berubah menjadi completed, update actual duration
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $tournament->updateActualDuration();
        }

        DB::commit();

        return back()->with('success', "Tournament status updated to " . ucfirst($newStatus) . " successfully.");

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to update tournament status: ' . $e->getMessage());
    }
}

    /**
     * Get tournament statistics
     */
    public function statistics(Tournament $tournament)
    {
        $scheduleEstimation = $tournament->getScheduleEstimation();
        $tableStatistics = $tournament->getTableStatistics();
        $optimization = $tournament->optimizeTableAllocation();

        return response()->json([
            'schedule_estimation' => $scheduleEstimation,
            'table_statistics' => $tableStatistics,
            'optimization_recommendation' => $optimization,
            'progress' => $tournament->progress,
            'completed_matches' => $tournament->completed_matches,
            'total_matches' => $tournament->total_matches
        ]);
    }

    /**
     * Optimize table allocation
     */
    public function optimizeTables(Tournament $tournament)
    {
        try {
            $optimization = $tournament->optimizeTableAllocation();

            return response()->json([
                'success' => true,
                'optimization' => $optimization
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         'type' => 'required|in:single,double,doubleduo,team',
    //         'max_players' => 'nullable|integer|min:2',
    //         'estimated_duration_minutes' => 'nullable|integer|min:1',
    //     ]);

    //     $tournament = Tournament::create($validated);

    //     // Auto estimate duration jika tidak diisi
    //     if (!$request->filled('estimated_duration_minutes')) {
    //         $tournament->estimateDuration();
    //     }

    //     return redirect()->route('tournaments.index')->with('success', 'Tournament created successfully.');
    // }

    // public function show(Tournament $tournament)
    // {
    //     // dd($tournament);
    //     $tournament->load(['matches.player1', 'matches.player2', 'matches.winner']);
    //     $matches = $tournament->matches->groupBy('round_number');
    //     // dd($tournament,$matches);
    //     return view('tournaments.show', compact('tournament', 'matches'));
    // }

    // public function edit(Tournament $tournament)
    // {
    //     return view('tournaments.edit', compact('tournament'));
    // }

    // public function update(Request $request, Tournament $tournament)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //         'status' => 'required|in:upcoming,ongoing,completed',
    //         'type' => 'required|in:single,double,doubleduo,team',
    //         'max_players' => 'nullable|integer|min:2',
    //         'estimated_duration_minutes' => 'nullable|integer|min:1',
    //     ]);

    //     $tournament->update($validated);

    //     // Update actual duration jika turnamen completed
    //     if ($tournament->status === 'completed') {
    //         $tournament->calculateActualDuration();
    //     }

    //     return redirect()->route('tournaments.index')->with('success', 'Tournament updated successfully.');
    // }

    // public function destroy(Tournament $tournament)
    // {
    //     $tournament->delete();
    //     return redirect()->route('tournaments.index')->with('success', 'Tournament deleted successfully.');
    // }
    
    public function showBracket___old(Tournament $tournament)
    {
        $bracketData = $tournament->getBracketData();
        
        return view('tournaments.bracket', compact('tournament', 'bracketData'));
    }
    public function generateBracket(Tournament $tournament)
{
    try {
        $players = $tournament->players;
        
        \Log::info('Bracket Generation Started:', [
            'tournament_id' => $tournament->id,
            'players_count' => $players->count(),
            'player_ids' => $players->pluck('id')->toArray()
        ]);

        if ($players->count() < 2) {
            return redirect()->back()->with('error', 'Need at least 2 players to generate bracket. Add players first.');
        }

        // Clear existing matches
        $tournament->matches()->delete();

        // Simple bracket generation - single elimination
        $shuffledPlayers = $players->shuffle();
        $matches = [];
        $round = 1;
        $matchNumber = 1;

        \Log::info('Shuffled players:', $shuffledPlayers->pluck('id', 'name')->toArray());

        // Generate first round matches
        for ($i = 0; $i < $shuffledPlayers->count(); $i += 2) {
            if (isset($shuffledPlayers[$i + 1])) {
                $match = \App\Models\Matches::create([
                    'tournament_id' => $tournament->id,
                    'player1_id' => $shuffledPlayers[$i]->id,
                    'player2_id' => $shuffledPlayers[$i + 1]->id,
                    'round_number' => $round,
                    'match_number' => $matchNumber,
                    'match_date' => now()->addDays($round),
                    'status' => 'scheduled'
                ]);
                
                $matches[] = $match;
                $matchNumber++;

                \Log::info('Match created:', [
                    'match_id' => $match->id,
                    'player1' => $shuffledPlayers[$i]->name,
                    'player2' => $shuffledPlayers[$i + 1]->name,
                    'round' => $round
                ]);
            } else {
                // Handle odd number of players - bye untuk player terakhir
                \Log::info('Bye for player:', ['player' => $shuffledPlayers[$i]->name]);
            }
        }

        \Log::info('Bracket Generation Completed:', [
            'total_matches' => count($matches),
            'matches_created' => $matches
        ]);

        $tournament->update(['status' => 'ongoing']);

        return redirect()->route('tournaments.bracket', $tournament)
            ->with('success', 'Tournament bracket generated successfully with ' . count($matches) . ' matches!');

    } catch (\Exception $e) {
        \Log::error('Error generating bracket: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
    // public function generateBracket(Tournament $tournament)
    // {
    //     try {
    //         // Load players dengan relationship
    //         // dd($tournament);
    //         $tournament->load('players');
    //         $players = $tournament->players;
            
    //         if ($players->count() < 2) {
    //             return redirect()->route('tournaments.draw', $tournament)
    //                 ->with('error', 'Need at least 2 players to generate bracket. Please add players first.');
    //         }

    //         // Gunakan TournamentDrawService
    //         $drawService = new TournamentDrawService($tournament);
    //         $matches = $drawService->generateDraw('random');

    //         $tournament->update(['status' => 'ongoing']);

    //         return redirect()->route('tournaments.bracket', $tournament)
    //             ->with('success', 'Tournament bracket generated successfully with ' . count($matches) . ' matches!');

    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', $e->getMessage());
    //     }
    // }

    /**
 * Show pairing form untuk double/double duo tournaments
 */
// app/Http/Controllers/TournamentController.php

public function showPairingForm(Tournament $tournament)
{
    $tournament->load(['players.ptmClub','duoPairs']);
    $players = $tournament->players;

    $availableForPairing = collect();
    $playerChunks = collect();
    if ($tournament->isDuoType()) {
        $availableForPairing = $tournament->getAvailablePlayersForPairing();
        $playerChunks = $availableForPairing->chunk(2);
    }
    // Bagi pemain menjadi chunks/pasangan (setiap chunk berisi 2 pemain)
    
    // dd( $playerChunks,$tournament,$availableForPairing);
    return view('tournaments.pairing', compact('tournament', 'players','playerChunks', 'availableForPairing'));
    // // dd($tournament);
    // return view('tournaments.pairing', compact('tournament'));
}

/**
 * Generate pairs dan bracket untuk double elimination
 */
public function generatePairs(Request $request, Tournament $tournament)
{
    $validated = $request->validate([
        'pairs' => 'required|array',
        'pairs.*.player1' => 'required|exists:players,id',
        'pairs.*.player2' => 'required|exists:players,id',
        'pairing_method' => 'nullable|string'
    ]);

    try {
        // Clear existing matches
        $tournament->matches()->delete();

        $pairs = [];
        $usedPlayers = [];

        // Validate pairs (no duplicate players)
        foreach ($validated['pairs'] as $pairData) {
            $player1 = $pairData['player1'];
            $player2 = $pairData['player2'];

            if ($player1 == $player2) {
                return redirect()->back()->with('error', 'Cannot pair a player with themselves.');
            }

            if (in_array($player1, $usedPlayers) || in_array($player2, $usedPlayers)) {
                return redirect()->back()->with('error', 'Duplicate players found in pairs.');
            }

            $pairs[] = [
                'player1' => $player1,
                'player2' => $player2
            ];

            $usedPlayers[] = $player1;
            $usedPlayers[] = $player2;
        }

        // Check if all players are paired
        $totalPlayers = $tournament->players->count();
        if (count($usedPlayers) != $totalPlayers) {
            return redirect()->back()->with('warning', 'Not all players are paired. Some players will not participate.');
        }

        // Generate bracket dengan pairs
        $matches = $this->generateDoubleBracketWithPairs($tournament, $pairs);

        $tournament->update(['status' => 'ongoing']);

        return redirect()->route('tournaments.bracket', $tournament)
            ->with('success', 'Pairs confirmed and bracket generated successfully with ' . count($matches) . ' matches!');

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error generating pairs: ' . $e->getMessage());
    }
}

/**
 * Generate double elimination bracket dengan pairs
 */
private function generateDoubleBracketWithPairs(Tournament $tournament, array $pairs): array
{
    $matches = [];
    $matchNumber = 1;

    // Winners Bracket - Round 1 (Pairs vs Pairs)
    foreach (array_chunk($pairs, 2) as $index => $pairChunk) {
        if (count($pairChunk) == 2) {
            $match = \App\Models\Matches::create([
                'tournament_id' => $tournament->id,
                'player1_id' => $pairChunk[0]['player1'],
                'player2_id' => $pairChunk[1]['player1'],
                'round_number' => 1,
                'match_number' => $matchNumber,
                'bracket_type' => 'winners',
                'match_date' => now()->addDays(1),
                'status' => 'scheduled',
                'notes' => 'Team A: ' . $this->getPlayerName($pairChunk[0]['player1']) . ' & ' . $this->getPlayerName($pairChunk[0]['player2']) . 
                          ' vs Team B: ' . $this->getPlayerName($pairChunk[1]['player1']) . ' & ' . $this->getPlayerName($pairChunk[1]['player2'])
            ]);
            $matches[] = $match;
            $matchNumber++;
        }
    }

    // Generate subsequent rounds
    $this->generateDoubleSubsequentRounds($tournament, count($pairs), $matchNumber, $matches);

    return $matches;
}

private function getPlayerName($playerId)
{
    $player = \App\Models\Player::find($playerId);
    return $player ? $player->display_name : 'Unknown';
}

private function generateDoubleSubsequentRounds($tournament, $pairCount, &$matchNumber, &$matches)
{
    $rounds = ceil(log($pairCount, 2)) + 2;
    
    for ($round = 2; $round <= $rounds; $round++) {
        $matchesInRound = max(1, floor($pairCount / pow(2, $round - 1)));
        
        for ($match = 1; $match <= $matchesInRound; $match++) {
            // Winners bracket
            $winnersMatch = \App\Models\Matches::create([
                'tournament_id' => $tournament->id,
                'round_number' => $round,
                'match_number' => $matchNumber,
                'bracket_type' => 'winners',
                'match_date' => $tournament->start_date->addDays($round),
                'status' => 'pending'
            ]);
            $matches[] = $winnersMatch;
            $matchNumber++;

            // Losers bracket (setiap round setelah round 1)
            if ($round >= 2) {
                $losersMatch = \App\Models\Matches::create([
                    'tournament_id' => $tournament->id,
                    'round_number' => $round,
                    'match_number' => $matchNumber,
                    'bracket_type' => 'losers',
                    'match_date' => $tournament->start_date->addDays($round),
                    'status' => 'pending'
                ]);
                $matches[] = $losersMatch;
                $matchNumber++;
            }
        }
    }

    // Grand Final
    $grandFinal = \App\Models\Matches::create([
        'tournament_id' => $tournament->id,
        'round_number' => $rounds + 1,
        'match_number' => $matchNumber,
        'bracket_type' => 'grand_final',
        'match_date' => $tournament->start_date->addDays($rounds + 1),
        'status' => 'pending'
    ]);
    $matches[] = $grandFinal;
}

    public function showBracket(Tournament $tournament)
    {
        $tournament->load([
            'matches.player1.ptmClub', 
            'matches.player2.ptmClub', 
            'matches.winner',
            'matches.table',
            'players.ptmClub'
        ]);

        return view('tournaments.bracket', compact('tournament'));
    }
    private function calculateRounds($playerCount)
    {
        // Tentukan jumlah round berdasarkan jumlah players
        if ($playerCount <= 2) {
            return ['totalRounds' => 1, 'firstRoundMatches' => 1];
        } elseif ($playerCount <= 4) {
            return ['totalRounds' => 2, 'firstRoundMatches' => 2];
        } elseif ($playerCount <= 8) {
            return ['totalRounds' => 3, 'firstRoundMatches' => 4];
        } elseif ($playerCount <= 16) {
            return ['totalRounds' => 4, 'firstRoundMatches' => 8];
        } else {
            return ['totalRounds' => 5, 'firstRoundMatches' => 16];
        }
    }
    public function generateBracket___(Tournament $tournament)
    {
        // Simple bracket generation logic
        $players = Player::inRandomOrder()->limit(8)->get();
        
        if ($players->count() < 2) {
            return redirect()->back()->with('error', 'Need at least 2 players to generate bracket.');
        }

        // Clear existing matches
        $tournament->matches()->delete();

        // Generate matches for quarter finals
        $round = 1;
        for ($i = 0; $i < $players->count(); $i += 2) {
            if (isset($players[$i + 1])) {
                Matches::create([
                    'tournament_id' => $tournament->id,
                    'player1_id' => $players[$i]->id,
                    'player2_id' => $players[$i + 1]->id,
                    'round_number' => $round,
                    'match_date' => now()->addDays($round),
                    'status' => 'scheduled'
                ]);
            }
        }

        return redirect()->route('tournaments.show', $tournament)->with('success', 'Bracket generated successfully.');
    }
    public function showDrawForm(Tournament $tournament)
    {
        // Dapatkan ID players yang sudah terdaftar di tournament ini
        // $registeredPlayerIds = $tournament->tournamentPlayers->pluck('player_id')->toArray();

        // $players = Player::inLibrary()->active()->orderBy('name')->get();
        // $ptmClubs =PTMClub::active()->orderBy('name')->get(); // Tambahkan ini jika diperlukan
       // Dapatkan ID players yang sudah terdaftar di tournament ini
        $registeredPlayerIds = $tournament->tournamentPlayers->pluck('player_id')->toArray();

         // Filter players: hanya yang belum terdaftar, aktif, dan di library
        $players = Player::inLibrary()
            ->active()
            ->whereNotIn('id', $registeredPlayerIds) // Filter out yang sudah terdaftar
            ->with('ptmClub') // Load club relationship
            ->orderBy('division_ranking', 'asc') // Sort by division (1 = terbaik)
            ->orderBy('name', 'asc') // Then by name
            ->get();

        $ptmClubs = PTMClub::active()->orderBy('name')->get();
       // Load tournament players
        $tournament->load([
            'tournamentPlayers.player.ptmClub', 
            'tournamentPlayers.representingClub'
        ]);
        // Load tournament players dengan sorting
        // PERBAIKAN: Load tournament players dengan sorting yang benar
        $tournament->tournamentPlayers = $tournament->tournamentPlayers
        ->filter(function($tp) {
            return $tp && $tp->player;
        })
        ->sortBy(function($tp) {
            // Primary: Division ranking (ascending - lower number first)
            // Secondary: Player name (ascending)
            return [
                $tp->player->division_ranking ?? 999,
                $tp->player->name ?? 'ZZZ'
            ];
        })
        ->values();
        // dd($tournament);
        return view('tournaments.draw', compact('tournament', 'players','ptmClubs'));

        // $tournament->load(['tournamentPlayers.player.ptmClub', 'tournamentPlayers.representingClub']);
        // // $tournament->load('players');
        // // dd($players,$categories,$tournament);
        // return view('tournaments.draw', compact('tournament', 'players','ptmClubs'));
    }
public function updatePlayer(Request $request, Tournament $tournament, Player $player)
{
    $request->validate([
        'seed' => 'nullable|integer|min:1',
        'group' => 'nullable|string|max:255',
        'representing_ptm_club_id' => 'nullable|exists:clubs,id',
        'is_representing_different_club' => 'boolean'
    ]);

    // Find the tournament player record
    $tournamentPlayer = TournamentPlayer::where('tournament_id', $tournament->id)
        ->where('player_id', $player->id)
        ->firstOrFail();

    // Update the tournament player details
    $tournamentPlayer->update([
        'seed' => $request->seed,
        'group' => $request->group,
        'representing_ptm_club_id' => $request->representing_club_id,
        'is_representing_different_club' => $request->boolean('is_representing_different_club')
    ]);

    return redirect()->back()->with('success', 'Player details updated successfully!');
}
public function changePlayer(Request $request, Tournament $tournament, Player $currentPlayer)
{
    $request->validate([
        'new_player_id' => 'required|exists:players,id'
    ]);
    
    $newPlayer = Player::findOrFail($request->new_player_id);
    $currentPlayer = Player::findOrFail($request->current_player_id);
    // Cek apakah new player sudah terdaftar di tournament
    if ($tournament->players()->where('players.id', $newPlayer->id)->exists()) {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'The selected player is already registered in this tournament!'
            ], 422);
        }
        return redirect()->back()->with('error', 'The selected player is already registered in this tournament!');
    }
     $tournament->load([
            'tournamentPlayers.player.ptmClub', 
            'tournamentPlayers.representingClub'
        ]);
        // Load tournament players dengan sorting
        // PERBAIKAN: Load tournament players dengan sorting yang benar
        $tournamentPlayer = $tournament->tournamentPlayers
        ->where('tournament_id', $tournament->id)
        ->where('player_id', $currentPlayer->id)
        ->first();
    // dd($currentPlayer,$newPlayer->id,$tournamentPlayer,$request,$newPlayer);
    // Simpan data tournament
    // $tournamentData = [
    //     'seed' => $tournamentPlayer->seed,
    //     'group' => $tournamentPlayer->group,
    //     'representing_ptm_club_id' => $tournamentPlayer->representing_club_id,
    //     'is_representing_different_club' => $tournamentPlayer->is_representing_different_club,
    // ];
   
    // Update player
    $tournamentPlayer->update([
        'player_id' => $newPlayer->id
    ] );

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Player changed successfully!'
        ]);
    }

    return redirect()->back()->with('success', 'Player changed successfully!');
}
    public function addPlayers(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'player_ids' => 'required|array',
            'player_ids.*' => 'exists:players,id',
            'representing_club_ids' => 'nullable|array',
            'representing_club_ids.*' => 'nullable|exists:ptm_clubs,id',
            'representation_notes' => 'nullable|array',
            'representation_notes.*' => 'nullable|string|max:500',
        ]);

        $attachedPlayers = [];

        foreach ($validated['player_ids'] as $index => $playerId) {
            $player = Player::find($playerId);
            $representingClubId = $validated['representing_club_ids'][$index] ?? null;
            $notes = $validated['representation_notes'][$index] ?? null;

            // Gunakan method baru dengan representation
            $tournament->addPlayerWithRepresentation($player, $representingClubId, null, null, $notes);
            $attachedPlayers[] = $player;
        }

        return redirect()->route('tournaments.draw', $tournament)
            ->with('success', count($attachedPlayers) . ' players added to tournament successfully.');
    }
    public function updatePlayerRepresentation(Request $request, Tournament $tournament, Player $player)
    {
        $validated = $request->validate([
            'representing_ptm_club_id' => 'nullable|exists:ptm_clubs,id',
            'representation_notes' => 'nullable|string|max:500',
        ]);

        $player->representClubInTournament(
            $tournament, 
            $validated['representing_ptm_club_id'], 
            $validated['representation_notes']
        );

        return redirect()->back()->with('success', 'Player representation updated successfully.');
    }

    public function showTournamentPlayers(Tournament $tournament)
    {
        $players = $tournament->getPlayersWithRepresentation();
        $ptmClubs = PTMClub::active()->orderBy('name')->get();
        
        return view('tournaments.players', compact('tournament', 'players', 'ptmClubs'));
    }
    public function addNewPlayer(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:players,email',
            'phone' => 'nullable|string|max:20',
            'rating' => 'required|integer|min:0|max:3000',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'playing_style' => 'nullable|in:offensive,defensive,all_round',
            'grip_style' => 'nullable|in:shakehand,penhold',
        ]);

        try {
            // Buat player baru
            $player = Player::findOrCreateFromData($validated);

            // Tambahkan ke turnamen
            $tournament->players()->attach($player->id);

            // Update player stats
            $player->updateStats();

            return response()->json([
                'success' => true,
                'player' => $player,
                'message' => 'Player created and added to tournament successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding player: ' . $e->getMessage()
            ], 500);
        }
    }

    public function quickAddPlayers(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'player_names' => 'required|string'
        ]);

        $playerNames = array_filter(
            array_map('trim', explode("\n", $validated['player_names']))
        );

        $addedPlayers = [];
        $errors = [];

        foreach ($playerNames as $playerName) {
            if (empty($playerName)) continue;

            try {
                // Cari atau buat player
                $player = Player::findOrCreateFromData([
                    'name' => $playerName,
                    'division_rangking' => 10, // Default rating
                ]);

                // Tambahkan ke turnamen jika belum ada
                if (!$tournament->players->contains($player->id)) {
                    $tournament->players()->attach($player->id);
                    $player->updateStats();
                    $addedPlayers[] = $player;
                }

            } catch (\Exception $e) {
                $errors[] = "Failed to add player '{$playerName}': " . $e->getMessage();
            }
        }

        $message = "Successfully added " . count($addedPlayers) . " players.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->route('tournaments.draw', $tournament)
            ->with('success', $message)
            ->with('errors', $errors);
    }
    // public function generateDraw(Request $request, Tournament $tournament)
    // {
    //     $validated = $request->validate([
    //         'draw_method' => 'required|in:random,rating,seeded'
    //     ]);

    //     try {
    //         $drawService = new TournamentDrawService($tournament);
    //         $matches = $drawService->generateDraw($validated['draw_method']);

    //         // Update tournament status
    //         $tournament->update(['status' => 'ongoing']);

    //         return redirect()->route('tournaments.show', $tournament)
    //             ->with('success', "Tournament draw generated successfully with {$validated['draw_method']} method.");

    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', $e->getMessage());
    //     }
    // }

    
}