<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Player;
use App\Models\Matches;
use Illuminate\Support\Collection;

class TournamentDrawService
{
    private $tournament;
    private $players;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
        $this->players = $tournament->players ?? collect();
    }

    /**
     * Generate draw berdasarkan tipe turnamen
     */
    public function generateDraw(string $method = 'random'): array
{
    $availablePlayers = $this->getAvailablePlayers();

    if ($availablePlayers->count() < 2) {
        throw new \Exception('Minimum 2 players required for tournament draw.');
    }

    // Clear existing matches
    $this->tournament->matches()->delete();

    $matches = [];

    // DEBUG: Log tournament type
    \Log::info('Tournament type detected:', ['type' => $this->tournament->type]);

    // Convert type to lowercase untuk case-insensitive comparison
    $tournamentType = strtolower($this->tournament->type);

    switch ($tournamentType) {
        case 'single':
        case 'single elimination':
            $matches = $this->generateSingleEliminationDraw($availablePlayers, $method);
            break;
        case 'double':
        case 'double elimination':
            $matches = $this->generateDoubleEliminationDraw($availablePlayers, $method);
            break;
        case 'team':
        case 'team tournament':
            $matches = $this->generateTeamDraw($availablePlayers, $method);
            break;
        case 'doubleduo':
            $matches = $this->generateDoubleDuoDraw($availablePlayers, $method);
            break;
        default:
            // Fallback ke single elimination jika type tidak dikenal
            \Log::warning('Unknown tournament type, defaulting to single elimination', [
                'provided_type' => $this->tournament->type,
                'tournament_id' => $this->tournament->id
            ]);
            $matches = $this->generateSingleEliminationDraw($availablePlayers, $method);
            break;
    }

    return $matches;
}
private function generateDoubleDuoDraw(Collection $players, string $method): array
{
    // Untuk sekarang, treat sebagai single elimination
    \Log::info('Using Double Duo draw type, treating as single elimination for now');
    return $this->generateSingleEliminationDraw($players, $method);
}
    /**
     * Single Elimination Draw
     */
    private function generateSingleEliminationDraw(Collection $players, string $method): array
    {
        $shuffledPlayers = $this->shufflePlayers($players, $method);
        $totalPlayers = $shuffledPlayers->count();
        
        // Cari jumlah pemain yang sesuai dengan bracket (power of 2)
        $bracketSize = $this->getNextPowerOfTwo($totalPlayers);
        $byes = $bracketSize - $totalPlayers;

        $matches = [];
        $round = 1;
        $matchNumber = 1;

        // Round 1 - dengan bye jika diperlukan
        $currentRoundPlayers = $shuffledPlayers;
        $nextRoundPlayers = collect();

        // Handle byes di round pertama
        if ($byes > 0) {
            $playersWithBye = $currentRoundPlayers->take($byes);
            $playersPlaying = $currentRoundPlayers->slice($byes);

            // Pemain dengan bye langsung ke round berikutnya
            $nextRoundPlayers = $playersWithBye;

            // Buat matches untuk pemain yang bertanding
            for ($i = 0; $i < $playersPlaying->count(); $i += 2) {
                if (isset($playersPlaying[$i + 1])) {
                    $match = $this->createMatch(
                        $playersPlaying[$i],
                        $playersPlaying[$i + 1],
                        $round,
                        $matchNumber
                    );
                    $matches[] = $match;
                    $matchNumber++;
                }
            }
        } else {
            // Tidak ada bye, semua pemain bertanding
            for ($i = 0; $i < $currentRoundPlayers->count(); $i += 2) {
                if (isset($currentRoundPlayers[$i + 1])) {
                    $match = $this->createMatch(
                        $currentRoundPlayers[$i],
                        $currentRoundPlayers[$i + 1],
                        $round,
                        $matchNumber
                    );
                    $matches[] = $match;
                    $matchNumber++;
                }
            }
        }

        // Generate subsequent rounds
        $currentRound = $round + 1;
        $currentMatches = $matches;

        while (count($currentMatches) > 1) {
            $nextRoundMatches = [];
            $nextMatchNumber = 1;

            for ($i = 0; $i < count($currentMatches); $i += 2) {
                if (isset($currentMatches[$i + 1])) {
                    // Placeholder match - players akan diisi nanti
                    $match = $this->createPlaceholderMatch($currentRound, $nextMatchNumber);
                    $nextRoundMatches[] = $match;
                    $nextMatchNumber++;
                }
            }

            $matches = array_merge($matches, $nextRoundMatches);
            $currentMatches = $nextRoundMatches;
            $currentRound++;
        }

        return $matches;
    }

    /**
     * Double Elimination Draw (lebih kompleks)
     */
    private function generateDoubleEliminationDraw(Collection $players, string $method): array
    {
        $shuffledPlayers = $this->shufflePlayers($players, $method);
        $totalPlayers = $shuffledPlayers->count();
        
        $bracketSize = $this->getNextPowerOfTwo($totalPlayers);
        $byes = $bracketSize - $totalPlayers;

        $matches = [];
        $round = 1;
        $matchNumber = 1;

        // Winners Bracket Round 1
        $currentPlayers = $shuffledPlayers;
        
        if ($byes > 0) {
            $winnersWithBye = $currentPlayers->take($byes);
            $playersPlaying = $currentPlayers->slice($byes);

            // Buat matches untuk winners bracket
            for ($i = 0; $i < $playersPlaying->count(); $i += 2) {
                if (isset($playersPlaying[$i + 1])) {
                    $match = $this->createMatch(
                        $playersPlaying[$i],
                        $playersPlaying[$i + 1],
                        $round,
                        $matchNumber,
                        'winners'
                    );
                    $matches[] = $match;
                    $matchNumber++;
                }
            }
        } else {
            for ($i = 0; $i < $currentPlayers->count(); $i += 2) {
                if (isset($currentPlayers[$i + 1])) {
                    $match = $this->createMatch(
                        $currentPlayers[$i],
                        $currentPlayers[$i + 1],
                        $round,
                        $matchNumber,
                        'winners'
                    );
                    $matches[] = $match;
                    $matchNumber++;
                }
            }
        }

        // Losers Bracket akan dibuat setelah winners bracket round 1 selesai
        // Ini adalah implementasi sederhana, double elimination yang lengkap lebih kompleks

        return $matches;
    }

    /**
     * Team Tournament Draw
     */
    private function generateTeamDraw(Collection $players, string $method): array
    {
        $shuffledPlayers = $this->shufflePlayers($players, $method);
        $matches = [];
        $matchNumber = 1;

        // Untuk team tournament, kita kelompokkan pemain menjadi tim
        $teams = $this->createTeams($shuffledPlayers);

        // Round robin sederhana - setiap tim bertanding dengan tim lainnya
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                $match = $this->createMatch(
                    $teams[$i]['players']->first(), // Captain team 1
                    $teams[$j]['players']->first(), // Captain team 2
                    1,
                    $matchNumber,
                    'team'
                );
                $matches[] = $match;
                $matchNumber++;
            }
        }

        return $matches;
    }

    /**
     * Shuffle players berdasarkan metode yang dipilih
     */
    private function shufflePlayers(Collection $players, string $method): Collection
    {
        switch ($method) {
            case 'division':
                // Urutkan berdasarkan divisi ranking (1 adalah tertinggi)
                return $players->sortBy('division_ranking')->values();
                
            case 'seeded':
                // Seeded drawing berdasarkan divisi ranking
                return $this->seededDrawByDivisionRanking($players);
                
            case 'random':
            default:
                // Acak sederhana
                return $players->shuffle();
        }
    }
    private function seededDrawByDivisionRanking(Collection $players): Collection
    {
        $sortedPlayers = $players->sortBy('division_ranking')->values();
        
        $totalPlayers = $sortedPlayers->count();
        $seededPlayers = collect();

        if ($totalPlayers <= 2) {
            return $sortedPlayers;
        }

        // Algorithm untuk seeded draw berdasarkan divisi ranking (snake method)
        for ($i = 0; $i < ceil($totalPlayers / 2); $i++) {
            // Pemain dari atas (divisi tinggi/ranking kecil)
            if (isset($sortedPlayers[$i])) {
                $seededPlayers->push($sortedPlayers[$i]);
            }
            
            // Pemain dari bawah (divisi rendah/ranking besar)
            $oppositeIndex = $totalPlayers - 1 - $i;
            if ($oppositeIndex > $i && isset($sortedPlayers[$oppositeIndex])) {
                $seededPlayers->push($sortedPlayers[$oppositeIndex]);
            }
        }

        return $seededPlayers;
    }

    /**
     * Seeded drawing - pemain terbaik ditempatkan di bracket yang berbeda
     */
    private function seededDraw(Collection $players): Collection
    {
        $sortedPlayers = $players->sortByDesc('rating')->values();
        $totalPlayers = $sortedPlayers->count();
        $seededPlayers = collect();

        if ($totalPlayers <= 2) {
            return $sortedPlayers;
        }

        // Algorithm untuk seeded draw (snake method)
        for ($i = 0; $i < ceil($totalPlayers / 2); $i++) {
            // Pemain dari atas
            if (isset($sortedPlayers[$i])) {
                $seededPlayers->push($sortedPlayers[$i]);
            }
            
            // Pemain dari bawah
            $oppositeIndex = $totalPlayers - 1 - $i;
            if ($oppositeIndex > $i && isset($sortedPlayers[$oppositeIndex])) {
                $seededPlayers->push($sortedPlayers[$oppositeIndex]);
            }
        }

        return $seededPlayers;
    }

    /**
     * Buat tim untuk team tournament
     */
    private function createTeams(Collection $players): array
    {
        $teams = [];
        $playerCount = $players->count();
        $teamSize = 2; // Default 2 pemain per tim

        // Kelompokkan pemain menjadi tim
        for ($i = 0; $i < $playerCount; $i += $teamSize) {
            $teamPlayers = $players->slice($i, $teamSize);
            if ($teamPlayers->count() > 0) {
                $teams[] = [
                    'name' => 'Team ' . (count($teams) + 1),
                    'players' => $teamPlayers
                ];
            }
        }

        return $teams;
    }

    /**
     * Cari next power of two
     */
    private function getNextPowerOfTwo(int $n): int
    {
        $power = 1;
        while ($power < $n) {
            $power *= 2;
        }
        return $power;
    }

    /**
     * Buat match dengan pemain tertentu
     */
    private function createMatch($player1, $player2, int $round, int $matchNumber, string $bracket = 'main'): Matches
    {
        $matchDate = $this->calculateMatchDate($round);

        return Matches::create([
            'tournament_id' => $this->tournament->id,
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'round_number' => $round,
            'match_date' => $matchDate,
            'status' => 'scheduled',
            'bracket_type' => $bracket,
            'match_number' => $matchNumber
        ]);
    }

    /**
     * Buat placeholder match untuk round berikutnya
     */
    private function createPlaceholderMatch(int $round, int $matchNumber): Matches
    {
        $matchDate = $this->calculateMatchDate($round);

        return Matches::create([
            'tournament_id' => $this->tournament->id,
            'round_number' => $round,
            'match_date' => $matchDate,
            'status' => 'pending', // Status khusus untuk match yang belum ada pemainnya
            'match_number' => $matchNumber
        ]);
    }

    /**
     * Hitung tanggal match berdasarkan round
     */
    private function calculateMatchDate(int $round): \DateTime
    {
        $startDate = $this->tournament->start_date;
        $daysToAdd = ($round - 1) * 2; // Setiap round selang 2 hari

        return $startDate->copy()->addDays($daysToAdd)->setHour(10); // Jam 10 pagi
    }

    /**
     * Dapatkan pemain yang available untuk turnamen
     */
    private function getAvailablePlayers(): Collection
    {
        if ($this->tournament->players) {
            return $this->tournament->players;
        }

        // Jika tidak ada relasi players, ambil dari semua pemain
        return Player::inRandomOrder()
            ->limit($this->tournament->max_players ?? 16)
            ->get();
    }

    /**
     * Update match berikutnya ketika match selesai
     */
    public function updateNextMatch(Matches $completedMatch): void
    {
        if ($completedMatch->status !== 'completed') {
            return;
        }

        $nextRound = $completedMatch->round_number + 1;
        $matchPosition = $completedMatch->match_number;
        $nextMatchNumber = ceil($matchPosition / 2);

        $nextMatch = Matches::where('tournament_id', $this->tournament->id)
            ->where('round_number', $nextRound)
            ->where('match_number', $nextMatchNumber)
            ->first();

        if ($nextMatch && $nextMatch->status === 'pending') {
            // Tentukan pemain berdasarkan posisi (ganjil/genap)
            $isWinnerFromTop = ($matchPosition % 2 === 1);
            
            if ($isWinnerFromTop) {
                $nextMatch->player1_id = $completedMatch->winner_id;
            } else {
                $nextMatch->player2_id = $completedMatch->winner_id;
            }

            $nextMatch->status = 'scheduled';
            $nextMatch->save();
        }
    }

    /**
     * Generate bracket visualization data
     */
    public function getBracketData(): array
    {
        $matches = $this->tournament->matches()
            ->with(['player1', 'player2', 'winner'])
            ->orderBy('round_number')
            ->orderBy('match_number')
            ->get();

        $bracket = [];
        $maxRounds = $matches->max('round_number') ?? 0;

        for ($round = 1; $round <= $maxRounds; $round++) {
            $roundMatches = $matches->where('round_number', $round)->values();
            $bracket[] = [
                'round' => $round,
                'matches' => $roundMatches,
                'title' => $this->getRoundTitle($round, $maxRounds)
            ];
        }

        return $bracket;
    }

    /**
     * Dapatkan judul round
     */
    private function getRoundTitle(int $round, int $maxRounds): string
    {
        $titles = [
            1 => 'First Round',
            2 => 'Second Round',
            3 => 'Quarter Finals',
            4 => 'Semi Finals',
            5 => 'Final'
        ];

        if (isset($titles[$round])) {
            return $titles[$round];
        }

        if ($round === $maxRounds) {
            return 'Final';
        }

        if ($round === $maxRounds - 1) {
            return 'Semi Finals';
        }

        if ($round === $maxRounds - 2) {
            return 'Quarter Finals';
        }

        return "Round {$round}";
    }
}