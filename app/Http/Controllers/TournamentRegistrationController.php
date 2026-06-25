<?php
// app/Http/Controllers/TournamentRegistrationController.php
namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Player;
use App\Models\TournamentPlayer;
use App\Models\TournamentDuoPair;
use App\Models\Team;
use App\Models\PtmClub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TournamentRegistrationController extends Controller
{
    /**
     * Show registration management page - INTEGRASI DENGAN EXISTING
     */
     public function manageRegistration(Tournament $tournament)
    {
        try {
            // Load data dengan error handling
            $tournament->load([
                'tournamentPlayers.player.ptmClub', 
                'duoPairs.player1', 
                'duoPairs.player2',
            ]);

            // Load teams jika relationship exists
            if (method_exists($tournament, 'teams')) {
                $tournament->load(['teams.players', 'teams.captain']);
            }

            $availablePlayers = Player::active()->inLibrary()->get();
            $ptmClubs = PtmClub::active()->get();
            
            // Get teams hanya jika relationship exists
            $teams = [];
            if (method_exists($tournament, 'teams')) {
                $teams = Team::active()->get();
            }

            // Statistics dengan error handling
            $stats = [
                'single_players' => $tournament->tournamentPlayers->count(),
                'duo_pairs' => $tournament->duoPairs->where('status', 'confirmed')->count(),
                'teams' => method_exists($tournament, 'teams') ? $tournament->teams->count() : 0,
                'total_participants' => $this->calculateTotalParticipants($tournament)
            ];

            return view('tournaments.registration-management', compact(
                'tournament',
                'availablePlayers',
                'ptmClubs',
                'teams',
                'stats'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Error loading registration data: ' . $e->getMessage());
        }
    }

    /**
     * Calculate total participants - DIPERBAIKI
     */
    private function calculateTotalParticipants(Tournament $tournament)
    {
        try {
            return match($tournament->type) {
                Tournament::TYPE_SINGLE => $tournament->tournamentPlayers->count(),
                Tournament::TYPE_DOUBLE, Tournament::TYPE_DOUBLEDUO => $tournament->duoPairs->where('status', 'confirmed')->count() * 2,
                Tournament::TYPE_TEAM => method_exists($tournament, 'teams') ? $tournament->teams->count() : 0,
                default => 0
            };
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Register single player - COMPATIBLE DENGAN EXISTING SYSTEM
     */
    public function registerSinglePlayer(Request $request, Tournament $tournament)
    {
        // Validasi untuk single tournament
        if (!$tournament->canAddIndividualPlayers() && $tournament->type !== Tournament::TYPE_SINGLE) {
            return back()->with('error', 'Tidak dapat menambah pemain individual untuk tournament type ini.');
        }

        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'representing_club_id' => 'nullable|exists:ptm_clubs,id',
            'seed' => 'nullable|integer|min:1',
            'group' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Check if player already registered menggunakan existing method
            $existing = TournamentPlayer::where([
                'tournament_id' => $tournament->id,
                'player_id' => $validated['player_id']
            ])->first();

            if ($existing) {
                return back()->with('error', 'Pemain sudah terdaftar di turnamen ini.');
            }

            // Gunakan method yang sudah ada dari Tournament model
            $tournament->addPlayerWithRepresentation(
                Player::find($validated['player_id']),
                $validated['representing_club_id'],
                $validated['seed'],
                $validated['group'],
                $validated['notes']
            );

            DB::commit();

            return back()->with('success', 'Pemain berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mendaftarkan pemain: ' . $e->getMessage());
        }
    }

    /**
     * Create duo pair - INTEGRASI DENGAN EXISTING PAIRING SYSTEM
     */
    public function createDuoPair(Request $request, Tournament $tournament)
    {
        // Validasi untuk duo tournament
        if (!$tournament->isDuoType()) {
            return back()->with('error', 'Tournament ini bukan tournament double/duo.');
        }

        $validated = $request->validate([
            'player1_id' => 'required|exists:players,id',
            'player2_id' => 'required|exists:players,id|different:player1_id',
            'pair_name' => 'required|string|max:255',
            'team_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $player1 = Player::find($validated['player1_id']);
            $player2 = Player::find($validated['player2_id']);

            // Check if players are available for pairing menggunakan existing method
            if ($tournament->isPlayerInPair($player1->id)) {
                return back()->with('error', $player1->name . ' sudah berada dalam pasangan lain.');
            }

            if ($tournament->isPlayerInPair($player2->id)) {
                return back()->with('error', $player2->name . ' sudah berada dalam pasangan lain.');
            }

            // Create duo pair menggunakan model yang sudah ada
            $duoPair = TournamentDuoPair::create([
                'tournament_id' => $tournament->id,
                'pair_name' => $validated['pair_name'],
                'team_name' => $validated['team_name'],
                'player1_id' => $player1->id,
                'player1_club_id' => $player1->ptm_club_id,
                'player2_id' => $player2->id,
                'player2_club_id' => $player2->ptm_club_id,
                'status' => 'confirmed',
                'notes' => $validated['notes']
            ]);

            // Remove players from individual registration jika ada
            TournamentPlayer::where([
                'tournament_id' => $tournament->id,
                'player_id' => $player1->id
            ])->delete();

            TournamentPlayer::where([
                'tournament_id' => $tournament->id,
                'player_id' => $player2->id
            ])->delete();

            DB::commit();

            return back()->with('success', 'Pasangan double berhasil dibuat: ' . $duoPair->pair_display_name);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat pasangan: ' . $e->getMessage());
        }
    }

    /**
     * Auto-pair players - COMPLEMENT EXISTING PAIRING FUNCTIONALITY
     */
    public function autoPairPlayers(Request $request, Tournament $tournament)
    {
        if (!$tournament->isDuoType()) {
            return back()->with('error', 'Hanya untuk tournament double/duo.');
        }

        try {
            DB::beginTransaction();

            $unpairedPlayers = $tournament->getUnpairedPlayers();
            
            if ($unpairedPlayers->count() < 2) {
                return back()->with('error', 'Minimal 2 pemain yang belum dipasangkan.');
            }

            if ($unpairedPlayers->count() % 2 !== 0) {
                return back()->with('warning', 'Jumlah pemain tidak genap. ' . ($unpairedPlayers->count() - 1) . ' pemain akan dipasangkan.');
            }

            $pairsCreated = 0;
            $players = $unpairedPlayers->shuffle();

            // Pair sisa pemain yang genap
            $pairCount = floor($players->count() / 2);
            
            for ($i = 0; $i < $pairCount; $i++) {
                $player1 = $players[$i * 2];
                $player2 = $players[$i * 2 + 1];

                TournamentDuoPair::create([
                    'tournament_id' => $tournament->id,
                    'pair_name' => $player1->nickname . ' & ' . $player2->nickname,
                    'player1_id' => $player1->id,
                    'player1_club_id' => $player1->ptm_club_id,
                    'player2_id' => $player2->id,
                    'player2_club_id' => $player2->ptm_club_id,
                    'status' => 'confirmed'
                ]);

                // Remove from individual registration
                TournamentPlayer::where([
                    'tournament_id' => $tournament->id,
                    'player_id' => $player1->id
                ])->delete();

                TournamentPlayer::where([
                    'tournament_id' => $tournament->id,
                    'player_id' => $player2->id
                ])->delete();

                $pairsCreated++;
            }

            DB::commit();

            $message = 'Berhasil membuat ' . $pairsCreated . ' pasangan secara otomatis.';
            if ($players->count() % 2 !== 0) {
                $message .= ' 1 pemain tersisa tanpa pasangan.';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan auto-pairing: ' . $e->getMessage());
        }
    }

    /**
     * Register team - INTEGRASI DENGAN EXISTING TEAM SYSTEM
     */
    public function registerTeam(Request $request, Tournament $tournament)
    {
        if ($tournament->type !== Tournament::TYPE_TEAM) {
            return back()->with('error', 'Hanya untuk tournament team.');
        }

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'seed' => 'nullable|integer|min:1',
            'group' => 'nullable|string|max:10'
        ]);

        try {
            DB::beginTransaction();

            $team = Team::find($validated['team_id']);

            // Check if team already registered menggunakan existing relationship
            if ($team->tournaments()->where('tournament_id', $tournament->id)->exists()) {
                return back()->with('error', 'Team sudah terdaftar di turnamen ini.');
            }

            // Register team menggunakan existing pivot
            $team->tournaments()->attach($tournament->id, [
                'seed' => $validated['seed'],
                'group' => $validated['group'],
                'status' => 'confirmed'
            ]);

            DB::commit();

            return back()->with('success', 'Team ' . $team->name . ' berhasil didaftarkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mendaftarkan team: ' . $e->getMessage());
        }
    }

    /**
     * Remove participant - COMPATIBLE DENGAN EXISTING
     */
    public function removeParticipant(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'type' => 'required|in:player,pair,team',
            'id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            switch ($validated['type']) {
                case 'player':
                    TournamentPlayer::where([
                        'tournament_id' => $tournament->id,
                        'player_id' => $validated['id']
                    ])->delete();
                    break;

                case 'pair':
                    TournamentDuoPair::where([
                        'tournament_id' => $tournament->id,
                        'id' => $validated['id']
                    ])->delete();
                    break;

                case 'team':
                    $tournament->teams()->detach($validated['id']);
                    break;
            }

            DB::commit();

            return back()->with('success', 'Peserta berhasil dihapus dari turnamen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus peserta: ' . $e->getMessage());
        }
    }

    /**
     * Get available players for pairing (AJAX) - UNTUK DROPDOWN
     */
    public function getAvailablePlayers(Tournament $tournament)
    {
        if (!$tournament->isDuoType()) {
            return response()->json(['players' => []]);
        }

        $players = $tournament->getAvailablePlayersForPairing();
        
        return response()->json([
            'players' => $players->map(function($player) {
                return [
                    'id' => $player->id,
                    'name' => $player->display_name,
                    'division' => $player->division_name,
                    'club' => $player->ptmClub->name ?? '-'
                ];
            })
        ]);
    }
}