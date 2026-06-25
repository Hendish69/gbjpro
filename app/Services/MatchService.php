<?php

namespace App\Services;

use App\Models\Matches;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;

class MatchService
{
    /**
     * Generate bracket matches for a tournament
     */
    public function generateBracketMatches(Tournament $tournament, $participants, $bracketType = 'single_elimination')
    {
        try {
            DB::beginTransaction();

            $matches = [];
            $rounds = $this->calculateRounds(count($participants));
            
            // Shuffle participants for random seeding
            shuffle($participants);

            for ($round = 1; $round <= $rounds; $round++) {
                $matchesInRound = $this->calculateMatchesInRound(count($participants), $round);
                
                for ($matchNum = 1; $matchNum <= $matchesInRound; $matchNum++) {
                    $match = Matches::create([
                        'tournament_id' => $tournament->id,
                        'match_type' => $tournament->tournament_type,
                        'match_format' => $tournament->match_format,
                        'bracket_type' => $bracketType,
                        'round_number' => $round,
                        'match_number' => $matchNum,
                        'status' => 'scheduled',
                        'match_date' => $tournament->start_date,
                    ]);

                    $matches[] = $match;
                }
            }

            // Assign participants to first round matches
            $this->assignParticipantsToFirstRound($matches, $participants, $rounds);

            DB::commit();
            return $matches;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate number of rounds needed
     */
    private function calculateRounds($participantCount)
    {
        return ceil(log($participantCount, 2));
    }

    /**
     * Calculate matches in a round
     */
    private function calculateMatchesInRound($participantCount, $round)
    {
        return $participantCount / pow(2, $round);
    }

    /**
     * Assign participants to first round matches
     */
    private function assignParticipantsToFirstRound($matches, $participants, $totalRounds)
    {
        $firstRoundMatches = array_filter($matches, function($match) {
            return $match->round_number === 1;
        });

        $firstRoundMatches = array_values($firstRoundMatches);

        foreach ($firstRoundMatches as $index => $match) {
            $player1Index = $index * 2;
            $player2Index = $player1Index + 1;

            if (isset($participants[$player1Index])) {
                if ($match->match_type === 'team') {
                    $match->team1_id = $participants[$player1Index]->id;
                } else {
                    $match->player1_id = $participants[$player1Index]->id;
                }
            }

            if (isset($participants[$player2Index])) {
                if ($match->match_type === 'team') {
                    $match->team2_id = $participants[$player2Index]->id;
                } else {
                    $match->player2_id = $participants[$player2Index]->id;
                }
            }

            $match->save();
        }
    }

    /**
     * Advance winner to next match
     */
    public function advanceWinner(Matches $match)
    {
        if (!$match->isCompleted || !$match->winner_id) {
            throw new \Exception('Match belum selesai atau pemenang belum ditentukan');
        }

        if ($match->next_match_id && $match->next_match_position) {
            $nextMatch = Matches::find($match->next_match_id);
            
            if ($nextMatch->match_type === 'team') {
                if ($match->next_match_position === 'player1') {
                    $nextMatch->team1_id = $match->winner_id;
                } else {
                    $nextMatch->team2_id = $match->winner_id;
                }
            } else {
                if ($match->next_match_position === 'player1') {
                    $nextMatch->player1_id = $match->winner_id;
                } else {
                    $nextMatch->player2_id = $match->winner_id;
                }
            }
            
            $nextMatch->save();
        }
    }

    /**
     * Get tournament bracket structure
     */
    public function getTournamentBracket(Tournament $tournament)
    {
        $matches = Matches::with(['player1', 'player2', 'team1', 'team2', 'winner'])
            ->where('tournament_id', $tournament->id)
            ->orderBy('round_number')
            ->orderBy('match_number')
            ->get()
            ->groupBy('round_number');

        return $matches;
    }

    /**
     * Calculate tournament standings
     */
    public function getTournamentStandings(Tournament $tournament)
    {
        // Implementation for tournament standings
        // This would calculate wins/losses for each participant
    }
}