<?php
// app/Http/Controllers/MatchScheduleController.php
namespace App\Http\Controllers;

use App\Models\Matches;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\Table;
use App\Models\TournamentPlayer;
use App\Models\TournamentDuoPair;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MatchScheduleController extends Controller
{
    /**
     * Show schedule creation form - INTEGRASI DENGAN EXISTING MATCHES
     */
    public function createSchedule(Tournament $tournament)
    {
        try {
            // Load data dengan error handling
            $tournament->load([
                'tournamentPlayers.player', 
                'duoPairs.player1', 
                'duoPairs.player2',
            ]);

            // Load teams jika relationship exists
            if (method_exists($tournament, 'teams')) {
                $tournament->load(['teams.players']);
            }

            $tables = Table::get();
            \Log::info('Available tables: ' . $tables->count());
            
            $participants = $this->getTournamentParticipants($tournament);
            // dd($participants);
            \Log::info('Participants count: ' . $participants->count());
            
            $existingMatches = $tournament->matches()
                ->with(['player1', 'player2', 'team1', 'team2', 'table'])
                ->orderBy('match_date')
                ->get();

            \Log::info('Existing matches: ' . $existingMatches->count());
            $macthformat=[
                ['format' => 'Elimination'],
                ['format' => 'Group'],
                ['format' => 'League'],
                 
                   
                    ];
            $timeSlots = $this->generateTimeSlots($tournament);
            \Log::info('Time slots generated: ' . count($timeSlots));

            return view('tournaments.schedule-management', compact(
                'tournament',
                'tables',
                'participants',
                'existingMatches',
                'timeSlots',
                'macthformat'
            ));

        } catch (\Exception $e) {
            Log::error('Error in createSchedule: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Error loading schedule data: ' . $e->getMessage());
        }
    }

    /**
     * Get participants dengan error handling
     */
   
     private function getAvailableSinglePlayers(Tournament $tournament)
    {
        try {
            // Dapatkan player IDs yang sudah digunakan di matches sebagai single player
            $usedPlayerIds = $tournament->matches()
                ->where(function($query) {
                    $query->whereNotNull('player1_id')
                          ->whereNull('player1_partner_id') // Pastikan ini single player, bukan pair
                          ->orWhereNotNull('player2_id')
                          ->whereNull('player2_partner_id');
                })
                ->get()
                ->flatMap(function($match) {
                    return [$match->player1_id, $match->player2_id];
                })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            \Log::info('Used single player IDs', ['used_ids' => $usedPlayerIds]);

            // Dapatkan players yang terdaftar di tournament dan belum digunakan
            $availablePlayers = $tournament->tournamentPlayers()
                ->with('player.ptmClub')
                ->whereNotIn('player_id', $usedPlayerIds)
                ->get()
                ->map(function($tp) {
                    return [
                        'id' => $tp->player->id,
                        'name' => $tp->player->display_name,
                        'type' => 'player',
                        'object' => $tp->player,
                        'division' => 'Div ' . $tp->player->division_ranking,
                        'club' => $tp->player->ptmClub->name ?? 'No Club'
                    ];
                });

            \Log::info('Available single players', [
                'total_players' => $tournament->tournamentPlayers()->count(),
                'used_players' => count($usedPlayerIds),
                'available_players' => $availablePlayers->count()
            ]);

            return $availablePlayers;

        } catch (\Exception $e) {
            \Log::error('Error getting available single players: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get available teams (belum ada di match)
     */
    private function getAvailableTeams(Tournament $tournament)
    {
        try {
            if (!method_exists($tournament, 'teams')) {
                \Log::warning('Teams relationship not found for tournament');
                return collect();
            }

            // Dapatkan team IDs yang sudah digunakan di matches
            $usedTeamIds = $tournament->matches()
                ->whereNotNull('team1_id')
                ->orWhereNotNull('team2_id')
                ->get()
                ->flatMap(function($match) {
                    return [$match->team1_id, $match->team2_id];
                })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            \Log::info('Used team IDs', ['used_ids' => $usedTeamIds]);

            // Dapatkan teams yang terdaftar di tournament dan belum digunakan
            $availableTeams = $tournament->teams()
                ->with(['players', 'captain'])
                ->whereNotIn('id', $usedTeamIds)
                ->get()
                ->map(function($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'type' => 'team',
                        'object' => $team,
                        'member_count' => $team->players->count() . ' members',
                        'captain' => $team->captain->display_name ?? 'No Captain'
                    ];
                });

            \Log::info('Available teams', [
                'total_teams' => $tournament->teams()->count(),
                'used_teams' => count($usedTeamIds),
                'available_teams' => $availableTeams->count()
            ]);

            return $availableTeams;

        } catch (\Exception $e) {
            \Log::error('Error getting available teams: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get available duo pairs (belum ada di match) - IMPROVED VERSION
     */
    private function getAvailableDuoPairs(Tournament $tournament)
    {
        try {
            // Dapatkan semua player combinations yang sudah digunakan di matches
            $usedCombinations = $this->getUsedPlayerCombinations($tournament);

            // Dapatkan pairs yang belum digunakan
            $availablePairs = $tournament->duoPairs()
                ->where('status', 'confirmed')
                ->with(['player1.ptmClub', 'player2.ptmClub'])
                ->get()
                ->filter(function($pair) use ($usedCombinations) {
                    $pairCombination = $pair->player1_id . '-' . $pair->player2_id;
                    $reverseCombination = $pair->player2_id . '-' . $pair->player1_id;
                    
                    return !in_array($pairCombination, $usedCombinations) && 
                           !in_array($reverseCombination, $usedCombinations);
                })
                ->map(function($pair) {
                    return [
                        'id' => $pair->id,
                        'name' => $pair->pair_display_name,
                        'type' => 'pair',
                        'object' => $pair,
                        'players' => $pair->player1->display_name . ' & ' . $pair->player2->display_name,
                        'division_info' => 'Div ' . $pair->player1->division_ranking . ' & Div ' . $pair->player2->division_ranking,
                        'clubs' => ($pair->player1->ptmClub->name ?? 'No Club') . ' / ' . ($pair->player2->ptmClub->name ?? 'No Club')
                    ];
                });

            \Log::info('Available duo pairs', [
                'total_pairs' => $tournament->duoPairs()->where('status', 'confirmed')->count(),
                'available_pairs' => $availablePairs->count(),
                'used_combinations' => count($usedCombinations)
            ]);

            return $availablePairs;

        } catch (\Exception $e) {
            \Log::error('Error getting available duo pairs: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Helper: Get used player combinations dari matches
     */
    private function getUsedPlayerCombinations(Tournament $tournament)
    {
        $matches = $tournament->matches()
            ->where(function($query) {
                $query->whereNotNull('player1_id')
                      ->orWhereNotNull('player2_id');
            })
            ->get();

        $usedCombinations = [];

        foreach ($matches as $match) {
            // Untuk single matches (tanpa partner)
            if ($match->player1_id && !$match->player1_partner_id && $match->player2_id && !$match->player2_partner_id) {
                $usedCombinations[] = $match->player1_id . '-' . $match->player2_id;
                $usedCombinations[] = $match->player2_id . '-' . $match->player1_id;
            }
            
            // Untuk pair matches (dengan partner)
            if ($match->player1_id && $match->player1_partner_id) {
                $usedCombinations[] = $match->player1_id . '-' . $match->player1_partner_id;
                $usedCombinations[] = $match->player1_partner_id . '-' . $match->player1_id;
            }
            
            if ($match->player2_id && $match->player2_partner_id) {
                $usedCombinations[] = $match->player2_id . '-' . $match->player2_partner_id;
                $usedCombinations[] = $match->player2_partner_id . '-' . $match->player2_id;
            }
        }

        return array_unique($usedCombinations);
    }

    /**
     * Main method untuk get participants berdasarkan tournament type
     */
    private function getTournamentParticipants(Tournament $tournament)
    {
        try {
            \Log::info('Getting participants for tournament type: ' . $tournament->type);
            
            $participants = match($tournament->type) {
                Tournament::TYPE_SINGLE => $this->getAvailableSinglePlayers($tournament),
                Tournament::TYPE_DOUBLE, Tournament::TYPE_DOUBLEDUO => $this->getAvailableDuoPairs($tournament),
                Tournament::TYPE_TEAM => $this->getAvailableTeams($tournament),
                default => collect()
            };

            \Log::info('Final participants count: ' . $participants->count());
            return $participants;

        } catch (\Exception $e) {
            \Log::error('Error in getTournamentParticipants: ' . $e->getMessage());
            return collect();
        }
    }
    /**
     * Generate time slots dengan error handling
     */
    private function generateTimeSlots(Tournament $tournament)
    {
        try {
            $slots = [];
            $startTime = $tournament->daily_start_time ? Carbon::parse($tournament->daily_start_time) : Carbon::parse('08:00');
            $endTime = $tournament->daily_end_time ? Carbon::parse($tournament->daily_end_time) : Carbon::parse('22:00');
            $matchDuration = $tournament->estimated_match_duration ?? 30;
            $breakTime = $tournament->break_between_matches ?? 5;

            Log::info("Time slot params - Start: {$startTime}, End: {$endTime}, Duration: {$matchDuration}, Break: {$breakTime}");

            $currentTime = $startTime->copy();
            
            while ($currentTime->lt($endTime)) {
                $slotEnd = $currentTime->copy()->addMinutes($matchDuration);
                
                if ($slotEnd->lte($endTime)) {
                    $slots[] = [
                        'start' => $currentTime->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'display' => $currentTime->format('H:i') . ' - ' . $slotEnd->format('H:i')
                    ];
                }
                
                $currentTime->addMinutes($matchDuration + $breakTime);
            }

            Log::info('Generated time slots: ' . count($slots));
            return $slots;

        } catch (\Exception $e) {
            Log::error('Error in generateTimeSlots: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate single match manually - INTEGRASI DENGAN EXISTING MATCHES
     */
// app/Http/Controllers/MatchScheduleController.php

/**
 * Generate single match manually - DITAMBAH DEBUGGING
 */
public function generateSingleMatch(Request $request, Tournament $tournament)
{
    $validated = $request->validate([
        'participant1_id' => 'required',
        'participant2_id' => 'required',
        'participant_type' => 'required|in:player,pair,team',
        'match_format' => 'required', // 'best_of_3', dll
        'match_date' => 'required|date',
        'match_time' => 'required',
        'table_id' => 'required|exists:tables,id',
        'round_number' => 'required|integer|min:1',
        'match_number' => 'required|integer|min:1'
    ]);

    try {
        DB::beginTransaction();

        $matchDateTime = $validated['match_date'] . ' ' . $validated['match_time'];
        $participantType = $validated['participant_type'];

        // Check table availability
        if (!$this->isTableAvailable($validated['table_id'], $matchDateTime, $tournament->estimated_match_duration ?? 30)) {
            return back()->with('error', 'Meja tidak tersedia pada waktu yang dipilih.');
        }

        // // FIXED: Gunakan match format yang valid
        // $validMatchFormats = ['bo1', 'bo3', 'bo5', 'single', 'double']; // Sesuaikan dengan constraint di DB
        // $matchFormat = in_array($tournament->match_format, $validMatchFormats) 
        //     ? $tournament->match_format 
        //     : 'bo3'; // Fallback ke bo3

        // Prepare match data
        $matchData = [
            'tournament_id' => $tournament->id,
            'match_type' => $tournament->type, // 'duo', 'single', dll
            'match_format' => $validated['match_format'], // 'best_of_3', dll
            'round_number' => $validated['round_number'],
            'match_number' => $validated['match_number'],
            'match_date' => $matchDateTime,
            'table_id' => $validated['table_id'],
            'duration_minutes' => $tournament->estimated_match_duration ?? 30,
            'status' => 'scheduled'
        ];

        \Log::info('Match data with valid format', $matchData);

        // Set participants berdasarkan type (sama seperti sebelumnya)
        switch ($participantType) {
            case 'player':
                $matchData['player1_id'] = $validated['participant1_id'];
                $matchData['player2_id'] = $validated['participant2_id'];
                break;

            case 'pair':
                $pair1 = TournamentDuoPair::find($validated['participant1_id']);
                $pair2 = TournamentDuoPair::find($validated['participant2_id']);
                
                if (!$pair1 || !$pair2) {
                    throw new \Exception('Pair not found');
                }
                
                $matchData['player1_id'] = $pair1->player1_id;
                $matchData['player1_partner_id'] = $pair1->player2_id;
                $matchData['player2_id'] = $pair2->player1_id;
                $matchData['player2_partner_id'] = $pair2->player2_id;
                break;

            case 'team':
                $matchData['team1_id'] = $validated['participant1_id'];
                $matchData['team2_id'] = $validated['participant2_id'];
                break;
        }
        // dd($matchData,$request,$participantType);
        // Create match
        $match = Matches::create($matchData);

        // Update table status
        $table = Table::find($validated['table_id']);
        $table->update(['status' => 'occupied']);

        DB::commit();

        return back()->with('success', 'Match created successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error creating match', [
            'error' => $e->getMessage(),
            'request_data' => $validated
        ]);
        return back()->with('error', 'Gagal membuat match: ' . $e->getMessage());
    }
}

    /**
     * Generate round matches automatically - NEW FEATURE
     */
    public function generateRoundMatches(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'round_number' => 'required|integer|min:1',
            'match_date' => 'required|date',
            'start_time' => 'required',
            'table_ids' => 'required|array|min:1',
            'table_ids.*' => 'exists:tables,id'
        ]);

        try {
            DB::beginTransaction();

            $participants = $this->getTournamentParticipants($tournament);
            
            if ($participants->count() < 2) {
                return back()->with('error', 'Minimal 2 peserta diperlukan.');
            }

            // Generate matches (shuffle untuk fairness)
            $matches = [];
            $shuffledParticipants = $participants->shuffle();
            
            for ($i = 0; $i < $shuffledParticipants->count(); $i += 2) {
                if (isset($shuffledParticipants[$i + 1])) {
                    $matches[] = [
                        'participant1' => $shuffledParticipants[$i],
                        'participant2' => $shuffledParticipants[$i + 1],
                        'match_number' => ($i / 2) + 1
                    ];
                }
            }

            // Schedule matches
            $scheduledMatches = $this->scheduleMatches($matches, $validated, $tournament);

            DB::commit();

            return back()->with('success', 'Berhasil membuat ' . count($scheduledMatches) . ' match untuk round ' . $validated['round_number']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Schedule matches dengan time assignment - NEW FEATURE
     */
    private function scheduleMatches($matches, $scheduleData, Tournament $tournament)
    {
        $scheduledMatches = [];
        $startTime = Carbon::parse($scheduleData['start_time']);
        $matchDuration = $tournament->estimated_match_duration ?? 30;
        $breakTime = $tournament->break_between_matches ?? 5;
        $tableIds = $scheduleData['table_ids'];
        
        $currentTime = $startTime->copy();
        $tableIndex = 0;

        foreach ($matches as $matchData) {
            $tableId = $tableIds[$tableIndex % count($tableIds)];
            $matchDateTime = $scheduleData['match_date'] . ' ' . $currentTime->format('H:i');

            // Skip jika table tidak available
            if (!$this->isTableAvailable($tableId, $matchDateTime, $matchDuration)) {
                continue;
            }

            // Create match data untuk existing Matches model
            $matchCreateData = [
                'tournament_id' => $tournament->id,
                'match_type' => $tournament->type,
                'match_format' => $tournament->match_format ?? 'bo3',
                'round_number' => $scheduleData['round_number'],
                'match_number' => $matchData['match_number'],
                'match_date' => $matchDateTime,
                'table_id' => $tableId,
                'duration_minutes' => $matchDuration,
                'status' => 'scheduled'
            ];

            // Set participants
            $participant1 = $matchData['participant1'];
            $participant2 = $matchData['participant2'];

            switch ($participant1['type']) {
                case 'player':
                    $matchCreateData['player1_id'] = $participant1['id'];
                    $matchCreateData['player2_id'] = $participant2['id'];
                    break;

                case 'pair':
                    $pair1 = $participant1['object'];
                    $pair2 = $participant2['object'];
                    
                    $matchCreateData['player1_id'] = $pair1->player1_id;
                    $matchCreateData['player1_partner_id'] = $pair1->player2_id;
                    $matchCreateData['player2_id'] = $pair2->player1_id;
                    $matchCreateData['player2_partner_id'] = $pair2->player2_id;
                    break;

                case 'team':
                    $matchCreateData['team1_id'] = $participant1['id'];
                    $matchCreateData['team2_id'] = $participant2['id'];
                    break;
            }

            $match = Matches::create($matchCreateData);
            $scheduledMatches[] = $match;

            // Update table status
            Table::find($tableId)->update(['status' => 'occupied']);

            // Move to next table and time
            $tableIndex++;
            if ($tableIndex % count($tableIds) === 0) {
                $currentTime->addMinutes($matchDuration + $breakTime);
            }
        }

        return $scheduledMatches;
    }

    /**
     * Check table availability - COMPATIBLE DENGAN EXISTING
     */
    private function isTableAvailable($tableId, $datetime, $duration)
    {
        $matchTime = Carbon::parse($datetime);
        $endTime = $matchTime->copy()->addMinutes($duration);
        \Log::info('Checking table availability', [
        'table_id' => $tableId,
        'match_time' => $matchTime,
        'end_time' => $endTime,
        'duration' => $duration
        ]);

        // Get all conflicting matches
        $conflictingMatches = Matches::where('table_id', $tableId)
            ->where('status', '!=', 'completed')
            ->get();
        \Log::info('Existing matches on table', [
        'matches_count' => $conflictingMatches->count(),
        'matches' => $conflictingMatches->pluck('id')
        ]);

        foreach ($conflictingMatches as $existingMatch) {
            $existingStart = Carbon::parse($existingMatch->match_date);
            $existingEnd = $existingStart->copy()->addMinutes($existingMatch->duration_minutes);
            \Log::info('Checking match overlap', [
                        'existing_match' => $existingMatch->id,
                        'existing_start' => $existingStart,
                        'existing_end' => $existingEnd,
                        'new_start' => $matchTime,
                        'new_end' => $endTime,
                        'overlaps' => $this->timeOverlaps($matchTime, $endTime, $existingStart, $existingEnd)
                    ]);
            // Check for overlap
            if ($this->timeOverlaps($matchTime, $endTime, $existingStart, $existingEnd)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Update match schedule - INTEGRASI DENGAN EXISTING MATCHES
     */
    public function updateMatchSchedule(Request $request, Matches $match)
    {
        $validated = $request->validate([
            'match_date' => 'required|date',
            'match_time' => 'required',
            'table_id' => 'required|exists:tables,id'
        ]);

        try {
            DB::beginTransaction();

            $matchDateTime = $validated['match_date'] . ' ' . $validated['match_time'];

            // Check table availability (exclude current match)
            if (!$this->isTableAvailableForUpdate($validated['table_id'], $matchDateTime, $match->duration_minutes, $match->id)) {
                return back()->with('error', 'Meja tidak tersedia pada waktu yang dipilih.');
            }

            // Free old table if changed
            if ($match->table_id != $validated['table_id'] && $match->table_id) {
                Table::find($match->table_id)->update(['status' => 'available']);
            }

            $match->update([
                'match_date' => $matchDateTime,
                'table_id' => $validated['table_id']
            ]);

            // Update new table status
            Table::find($validated['table_id'])->update(['status' => 'occupied']);

            DB::commit();

            return back()->with('success', 'Jadwal match berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Check table availability for update - COMPATIBLE
     */
    private function isTableAvailableForUpdate($tableId, $datetime, $duration, $excludeMatchId)
    {
        $matchTime = Carbon::parse($datetime);
        $endTime = $matchTime->copy()->addMinutes($duration);

        // Get all conflicting matches excluding the current one
        $conflictingMatches = Matches::where('table_id', $tableId)
            ->where('id', '!=', $excludeMatchId)
            ->where('status', '!=', 'completed')
            ->get();

        foreach ($conflictingMatches as $existingMatch) {
            $existingStart = Carbon::parse($existingMatch->match_date);
            $existingEnd = $existingStart->copy()->addMinutes($existingMatch->duration_minutes);

            // Check for overlap
            if ($this->timeOverlaps($matchTime, $endTime, $existingStart, $existingEnd)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper method to check if two time ranges overlap
     */
    private function timeOverlaps($start1, $end1, $start2, $end2)
    {
        return $start1 < $end2 && $start2 < $end1;
    }

    /**
     * Get optimization data - INTEGRASI DENGAN EXISTING TOURNAMENT OPTIMIZATION
     */
    public function getOptimization(Tournament $tournament)
    {
        $optimization = $tournament->optimizeTableAllocation();
        $scheduleEstimation = $tournament->getScheduleEstimation();
        $tableStatistics = $tournament->getTableStatistics();

        return response()->json([
            'optimization' => $optimization,
            'estimation' => $scheduleEstimation,
            'statistics' => $tableStatistics
        ]);
    }
}