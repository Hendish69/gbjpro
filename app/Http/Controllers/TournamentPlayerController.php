<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentPlayer;
use App\Models\Player;
use App\Models\PTMClub;
use Illuminate\Http\Request;

class TournamentPlayerController extends Controller
{
    /**
     * Display listing of tournament players
     */
    public function index(Tournament $tournament)
    {
        $tournamentPlayers = $tournament->tournamentPlayers()
            ->with(['player.ptmClub', 'representingClub'])
            ->orderBy('seed')
            ->orderBy('group')
            ->get();

        $ptmClubs = PTMClub::active()->orderBy('name')->get();

        return view('tournament-players.index', compact('tournament', 'tournamentPlayers', 'ptmClubs'));
    }

    /**
     * Show form untuk add player ke tournament
     */
    public function create(Tournament $tournament)
    {
        $players = Player::active()->inLibrary()->orderBy('name')->get();
        $ptmClubs = PTMClub::active()->orderBy('name')->get();

        return view('tournament-players.create', compact('tournament', 'players', 'ptmClubs'));
    }

    /**
     * Store new tournament player
     */
    public function store(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'representing_ptm_club_id' => 'nullable|exists:ptm_clubs,id',
            'representation_notes' => 'nullable|string|max:500',
            'seed' => 'nullable|integer|min:1',
            'group' => 'nullable|string|max:255'
        ]);

        // Cek apakah player sudah terdaftar
        $existingPlayer = $tournament->tournamentPlayers()
            ->where('player_id', $validated['player_id'])
            ->first();

        if ($existingPlayer) {
            return redirect()->back()
                ->with('error', 'Player already registered in this tournament.')
                ->withInput();
        }

        try {
            $player = Player::find($validated['player_id']);
            $isRepresentingDifferent = $validated['representing_ptm_club_id'] && 
            $validated['representing_ptm_club_id'] != $player->ptm_club_id;

            $tournamentPlayer = TournamentPlayer::create([
                'tournament_id' => $tournament->id,
                'player_id' => $validated['player_id'],
                'representing_ptm_club_id' => $validated['representing_ptm_club_id'],
                'is_representing_different_club' => $isRepresentingDifferent,
                'representation_notes' => $validated['representation_notes'],
                'seed' => $validated['seed'],
                'group' => $validated['group']
            ]);

        return redirect()->route('tournaments.draw', $tournament)
            ->with('success', 'Player added to tournament successfully.');


            // $tournamentPlayer = $tournament->addPlayerWithRepresentation(
            //     Player::find($validated['player_id']),
            //     $validated['representing_ptm_club_id'],
            //     $validated['seed'],
            //     $validated['group'],
            //     $validated['representation_notes']
            // );

            // return redirect()->route('tournaments.players', $tournament)
            //     ->with('success', 'Player added to tournament successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error adding player: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show form untuk edit tournament player
     */
    public function edit(Tournament $tournament, TournamentPlayer $tournamentPlayer)
    {
        // Pastikan tournament player belongs to tournament
        if ($tournamentPlayer->tournament_id != $tournament->id) {
            abort(404);
        }

        $ptmClubs = PTMClub::active()->orderBy('name')->get();

        return view('tournament-players.edit', compact('tournament', 'tournamentPlayer', 'ptmClubs'));
    }

    /**
     * Update tournament player
     */
    public function update(Request $request, Tournament $tournament, TournamentPlayer $tournamentPlayer)
    {
        // Pastikan tournament player belongs to tournament
        if ($tournamentPlayer->tournament_id != $tournament->id) {
            abort(404);
        }

        $validated = $request->validate([
            'representing_ptm_club_id' => 'nullable|exists:ptm_clubs,id',
            'representation_notes' => 'nullable|string|max:500',
            'seed' => 'nullable|integer|min:1',
            'group' => 'nullable|string|max:255'
        ]);

        try {
            $tournamentPlayer->updateRepresentation(
                $validated['representing_ptm_club_id'],
                $validated['representation_notes']
            );

            // Update seed dan group jika provided
            if (isset($validated['seed'])) {
                $tournamentPlayer->assignSeed($validated['seed']);
            }

            if (isset($validated['group'])) {
                $tournamentPlayer->assignToGroup($validated['group']);
            }

            return redirect()->route('tournaments.players', $tournament)
                ->with('success', 'Player information updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating player: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove player from tournament
     */
    public function destroy(Tournament $tournament, TournamentPlayer $tournamentPlayer)
    {
        // Pastikan tournament player belongs to tournament
        if ($tournamentPlayer->tournament_id != $tournament->id) {
            abort(404);
        }

        try {
            $playerName = $tournamentPlayer->player->display_name;
            $tournamentPlayer->delete();

            return redirect()->route('tournaments.players', $tournament)
                ->with('success', "Player {$playerName} removed from tournament successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error removing player: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign seeds
     */
    public function bulkAssignSeeds(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'seeds' => 'required|array',
            'seeds.*.player_id' => 'required|exists:tournament_player,player_id,tournament_id,' . $tournament->id,
            'seeds.*.seed' => 'required|integer|min:1'
        ]);

        try {
            foreach ($validated['seeds'] as $seedData) {
                $tournamentPlayer = $tournament->tournamentPlayers()
                    ->where('player_id', $seedData['player_id'])
                    ->first();

                if ($tournamentPlayer) {
                    $tournamentPlayer->assignSeed($seedData['seed']);
                }
            }

            return redirect()->back()->with('success', 'Seeds assigned successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error assigning seeds: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign groups
     */
    public function bulkAssignGroups(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
            'player_ids' => 'required|array',
            'player_ids.*' => 'exists:tournament_player,player_id,tournament_id,' . $tournament->id
        ]);

        try {
            $tournament->tournamentPlayers()
                ->whereIn('player_id', $validated['player_ids'])
                ->update(['group' => $validated['group_name']]);

            return redirect()->back()->with('success', 'Players assigned to group successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error assigning groups: ' . $e->getMessage());
        }
    }
}