<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Tournament extends Model
{
    use HasFactory;
    const TYPE_SINGLE = 'single';
    const TYPE_DOUBLE = 'double'; 
    const TYPE_DOUBLEDUO = 'duo';
    const TYPE_TEAM = 'team';

    const VALID_TYPES = [
        self::TYPE_SINGLE,
        self::TYPE_DOUBLE,
        self::TYPE_DOUBLEDUO,
        self::TYPE_TEAM,
    ];
    const FORMAT_ELIMINATION = 'elimination';
    const FORMAT_LEAGUE = 'league';
    const FORMAT_GROUP = 'group';

    const STATUS_PENDING = 'pending';
    const STATUS_REGISTRATION_OPEN = 'registration_open';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

   protected $fillable = [
        'name',
        'description', 
        'type',
        'format',
        'start_date',
        'end_date',
        'registration_deadline',
        'max_players',
        'max_teams',
        'status',
        'settings',
        // New fields
        'available_tables',
        'matches_per_table',
        'estimated_match_duration',
        'break_between_matches', 
        'warmup_time',
        'daily_start_time',
        'daily_end_time',
        'max_daily_playing_hours',
        'actual_duration_minutes',
        'actual_start_date',
        'actual_end_date',
        'venue',
        'lat',
        'lon'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'date',
        'settings' => 'array',
        'daily_start_time' => 'datetime',
        'daily_end_time' => 'datetime',
        'actual_start_date' => 'datetime',
        'actual_end_date' => 'datetime'
    ];

    public function matches()
    {
        return $this->hasMany(Matches::class);
    }

    // Accessors untuk durasi
    public function getEstimatedDurationHoursAttribute()
    {
        return $this->estimated_duration_minutes ? round($this->estimated_duration_minutes / 60, 1) : 0;
    }

    public function getActualDurationHoursAttribute()
    {
        return $this->actual_duration_minutes ? round($this->actual_duration_minutes / 60, 1) : 0;
    }

    public function getTotalTableUsageMinutesAttribute()
    {
        return $this->matches()->where('status', 'completed')->sum('duration_minutes');
    }

    public function getTotalTableUsageHoursAttribute()
    {
        return round($this->total_table_usage_minutes / 60, 1);
    }
 // 🔥 TAMBAHKAN RELATIONSHIP INI:

    /**
     * Relationship dengan teams melalui pivot table
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'tournament_team')
                    ->withPivot(['seed', 'group', 'status'])
                    ->withTimestamps();
    }

    public function getRegistrationSummary()
    {
        return [
            'single_players' => $this->tournamentPlayers()->count(),
            'duo_pairs' => $this->duoPairs()->where('status', 'confirmed')->count(),
            'teams' => $this->teams()->count(),
            'total_participants' => $this->calculateTotalParticipants(),
            'registration_progress' => ($this->players()->count() / $this->max_players) * 100
        ];
    }

    /**
     * Relationship dengan matches
     */
   
    public function getAverageMatchDurationMinutesAttribute()
    {
        $completedMatches = $this->matches()->where('status', 'completed')->count();
        return $completedMatches > 0 ? round($this->total_table_usage_minutes / $completedMatches) : 0;
    }
    public function getTotalDailyMatchesAttribute()
    {
        try {
            $matchesPerTable = $this->matches_per_table ?? 1;
            $availableTables = $this->available_tables ?? 1;
            
            // Validasi nilai
            $matchesPerTable = max(1, $matchesPerTable); // Minimal 1 match per table
            $availableTables = max(1, $availableTables); // Minimal 1 table
            
            $totalDailyMatches = $matchesPerTable * $availableTables;
            
            // Pastikan nilai reasonable dan tidak nol
            return max(1, min($totalDailyMatches, 1000)); // Batasi antara 1-1000
        } catch (\Exception $e) {
            return 1; // Fallback value
        }
    }

    // Perbaiki juga matches_per_day accessor
    public function getMatchesPerDayAttribute()
    {
        try {
            $dailyMinutes = $this->daily_playing_minutes;
            $totalMatchTime = $this->total_match_time;
            
            // Safety checks
            if ($totalMatchTime <= 0) {
                $totalMatchTime = 30; // Default 30 menit per match
            }
            
            if ($dailyMinutes <= 0) {
                $dailyMinutes = 480; // Default 8 hours
            }
            
            $calculated = floor($dailyMinutes / $totalMatchTime);
            return max(1, $calculated); // Pastikan minimal 1 match per day
        } catch (\Exception $e) {
            return 4; // Fallback: 4 matches per day
        }
    }

// Pastikan daily_playing_minutes selalu return nilai valid
    public function getDailyPlayingMinutesAttribute()
    {
        try {
            // Jika data tidak ada, return default
            if (!$this->daily_start_time || !$this->daily_end_time) {
                return 480; // Default 8 hours dalam menit
            }
            
            $start = \Carbon\Carbon::parse($this->daily_start_time);
            $end = \Carbon\Carbon::parse($this->daily_end_time);
            
            // Pastikan end time setelah start time
            if ($end <= $start) {
                return 480; // Default jika waktu tidak valid
            }
            
            $totalMinutes = $end->diffInMinutes($start);
            
            // Batasi dengan max_daily_playing_hours
            $maxDailyHours = $this->max_daily_playing_hours ?? 8;
            $maxMinutes = max(60, $maxDailyHours * 60); // Minimal 1 jam
            
            return min($totalMinutes, $maxMinutes);
        } catch (\Exception $e) {
            return 480; // Fallback 8 hours
        }
    }

// Pastikan total_match_time selalu valid
    public function getTotalMatchTimeAttribute()
    {
        try {
            $matchDuration = $this->estimated_match_duration ?? 15;
            $breakTime = $this->break_between_matches ?? 5;
            $warmupTime = $this->warmup_time ?? 5;
            
            // Validasi nilai minimal
            $matchDuration = max(1, $matchDuration); // Minimal 1 menit
            $breakTime = max(0, $breakTime); // Minimal 0
            $warmupTime = max(0, $warmupTime); // Minimal 0
            
            $total = $matchDuration + $breakTime + $warmupTime;
            return max(5, $total); // Pastikan minimal 5 menit total
        } catch (\Exception $e) {
            return 25; // Fallback: 25 menit per match
        }
    }


    // TIME ESTIMATION METHODS
    /**
     * Estimate total matches berdasarkan tournament type dan format
     */
    public function estimateTotalMatches()
    {
        $participantCount = $this->getParticipantCount();
        
        // Handle case ketika participant count kurang dari 2
        if ($participantCount < 2) {
            return 0;
        }
        
        $estimated = match($this->format) {
            self::FORMAT_ELIMINATION => $this->estimateEliminationMatches($participantCount),
            self::FORMAT_LEAGUE => $this->estimateLeagueMatches($participantCount),
            self::FORMAT_GROUP => $this->estimateGroupMatches($participantCount),
            default => max(0, $participantCount - 1) // Default elimination, pastikan tidak negatif
        };
        
        return max(0, $estimated); // Pastikan tidak return nilai negatif
    }

    private function getParticipantCount()
{
    // Untuk tournament baru, gunakan max_players sebagai estimasi
    // Untuk tournament yang sudah ada, hitung dari data aktual
    if ($this->exists && $this->wasRecentlyCreated === false) {
        if ($this->type === self::TYPE_TEAM) {
            return max(0, $this->teams()->count());
        }
        return max(0, $this->players()->count());
    }
    
    // Untuk tournament baru, gunakan max_players
    return max(1, $this->max_players); // Pastikan minimal 1
}

    private function estimateEliminationMatches($participantCount)
{
    if ($participantCount < 2) return 0;
    
    return match($this->type) {
        self::TYPE_SINGLE => max(0, $participantCount - 1),
        self::TYPE_DOUBLE => max(0, ($participantCount * 2) - 2),
        self::TYPE_DOUBLEDUO => max(0, $participantCount * 2),
        self::TYPE_TEAM => max(0, $participantCount - 1),
        default => max(0, $participantCount - 1)
    };
}

    private function estimateLeagueMatches($participantCount)
{
    if ($participantCount < 2) return 0;
    return max(0, ($participantCount * ($participantCount - 1)) / 2);
}

private function estimateGroupMatches($participantCount)
{
    if ($participantCount < 2) return 0;
    
    $groupCount = 4; // Default 4 groups
    $participantsPerGroup = ceil($participantCount / $groupCount);
    
    if ($participantsPerGroup < 2) return 0;
    
    // Matches dalam group (round robin)
    $groupMatches = $groupCount * (($participantsPerGroup * ($participantsPerGroup - 1)) / 2);
    
    // Knockout matches (quarter, semi, final)
    $knockoutMatches = $groupCount / 2 + $groupCount / 4 + 1;
    
    return max(0, $groupMatches + $knockoutMatches);
}

    /**
     * Estimate tournament duration dalam menit
     */
    public function estimateTotalDuration()
    {
        $totalMatches = $this->estimateTotalMatches();
        
        if ($totalMatches === 0 || $this->total_daily_matches === 0) {
            return 0;
        }

        $totalMatchTime = $totalMatches * $this->total_match_time;
        $daysNeeded = ceil($totalMatches / $this->total_daily_matches);
        
        return $daysNeeded * $this->daily_playing_minutes;
    }

    /**
     * Estimate tournament end date berdasarkan start date
     */
    public function estimateEndDate()
    {
        $totalDuration = $this->estimateTotalDuration();
        
        if ($totalDuration === 0) {
            return $this->start_date;
        }

        $daysNeeded = ceil($totalDuration / $this->daily_playing_minutes);
        return $this->start_date->copy()->addDays($daysNeeded - 1); // -1 karena start day termasuk
    }

    /**
     * Generate detailed schedule estimation
     */
    public function getScheduleEstimation()
    {
        $totalMatches = $this->estimateTotalMatches();
        $totalDuration = $this->estimateTotalDuration();
        $daysNeeded = ceil($totalMatches / $this->total_daily_matches);
        $estimatedEndDate = $this->estimateEndDate();

        return [
            'total_matches' => $totalMatches,
            'total_duration_minutes' => $totalDuration,
            'total_duration_hours' => round($totalDuration / 60, 1),
            'total_duration_days' => $daysNeeded,
            'estimated_end_date' => $estimatedEndDate,
            'matches_per_day' => $this->matches_per_day,
            'total_daily_matches' => $this->total_daily_matches,
            'daily_capacity' => $this->total_daily_matches,
            'utilization_rate' => $this->calculateSafeUtilizationRate($totalMatches, $daysNeeded),
        ];
    }
private function calculateSafeUtilizationRate($totalMatches, $daysNeeded)
{
    try {
        // Validasi input dasar
        if ($this->total_daily_matches <= 0 || $daysNeeded <= 0 || $totalMatches <= 0) {
            return 0;
        }
        
        $denominator = $this->total_daily_matches * $daysNeeded;
        
        if ($denominator <= 0) {
            return 0;
        }
        
        $rate = ($totalMatches / $denominator) * 100;
        return round(max(0, min(100, $rate)), 1);
        
    } catch (\Exception $e) {
        return 0;
    }
}
    /**
     * Update actual duration ketika tournament selesai
     */
    public function updateActualDuration()
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        $firstMatch = $this->matches()->orderBy('match_date')->first();
        $lastMatch = $this->matches()->orderBy('match_date', 'desc')->first();

        if ($firstMatch && $lastMatch) {
            $this->actual_start_date = $firstMatch->match_date;
            $this->actual_end_date = $lastMatch->match_date;
            $this->actual_duration_minutes = $this->actual_start_date->diffInMinutes($this->actual_end_date);
            
            return $this->save();
        }

        return false;
    }

    /**
     * Calculate table utilization statistics
     */
    public function getTableStatistics()
    {
        $totalMatches = $this->matches()->count();
        $completedMatches = $this->matches()->where('status', 'completed')->count();
        $totalMatchMinutes = $completedMatches * $this->estimated_match_duration;
        
        $totalAvailableMinutes = 0;
        $currentDate = $this->start_date->copy();
        
        // Calculate total available minutes selama tournament
        while ($currentDate->lte($this->end_date)) {
            $totalAvailableMinutes += $this->daily_playing_minutes * $this->available_tables;
            $currentDate->addDay();
        }

        $utilizationRate = $totalAvailableMinutes > 0 ? 
            round(($totalMatchMinutes / $totalAvailableMinutes) * 100, 1) : 0;

        return [
            'available_tables' => $this->available_tables,
            'matches_per_table' => $this->matches_per_table,
            'total_matches' => $totalMatches,
            'completed_matches' => $completedMatches,
            'total_match_minutes' => $totalMatchMinutes,
            'total_available_minutes' => $totalAvailableMinutes,
            'table_utilization_rate' => $utilizationRate,
            'efficiency_score' => $this->calculateEfficiencyScore($utilizationRate)
        ];
    }

    private function calculateEfficiencyScore($utilizationRate)
    {
        // Scoring system untuk efficiency
        if ($utilizationRate >= 80) return 'Excellent';
        if ($utilizationRate >= 60) return 'Good';
        if ($utilizationRate >= 40) return 'Fair';
        return 'Poor';
    }

    /**
     * Optimize table allocation berdasarkan participant count
     */
    // Di model Tournament.php - perbaiki method optimizeTableAllocation
// Di model Tournament.php - perbaiki method optimizeTableAllocation dengan safety checks
// Perbaiki bagian yang menggunakan division di optimizeTableAllocation
    // Perbaiki method optimizeTableAllocation - gunakan approach yang berbeda
public function optimizeTableAllocation()
{
    $participantCount = $this->getParticipantCount();
    $totalMatches = $this->estimateTotalMatches();
    
    \Log::info("=== OPTIMIZATION START ===", [
        'tournament_id' => $this->id,
        'participant_count' => $participantCount,
        'total_matches' => $totalMatches,
        'available_tables' => $this->available_tables,
        'matches_per_table' => $this->matches_per_table
    ]);

    // Handle case ketika tidak ada participants atau matches
    if ($participantCount < 2 || $totalMatches < 1) {
        \Log::info("Optimization: Insufficient data", [
            'participant_count' => $participantCount,
            'total_matches' => $totalMatches
        ]);
        return $this->getDefaultOptimizationResult($participantCount, $totalMatches);
    }

    // Dapatkan current daily matches dengan safety
    $currentDailyMatches = $this->getSafeTotalDailyMatches();
    
    \Log::info("Current daily matches calculation:", [
        'currentDailyMatches' => $currentDailyMatches,
        'matches_per_day' => $this->matches_per_day,
        'available_tables' => $this->available_tables
    ]);

    // Jika current daily matches tidak valid, gunakan fallback
    if ($currentDailyMatches <= 0) {
        \Log::warning("Optimization: Invalid current daily matches, using fallback", [
            'currentDailyMatches' => $currentDailyMatches
        ]);
        return $this->getFallbackOptimizationResult($totalMatches, $participantCount);
    }

    // Hitung days needed dengan current setup
    $currentDaysNeeded = $this->safeCeilDivision($totalMatches, $currentDailyMatches);
    
    \Log::info("Current setup calculation:", [
        'currentDaysNeeded' => $currentDaysNeeded,
        'totalMatches' => $totalMatches,
        'currentDailyMatches' => $currentDailyMatches
    ]);

    // Cari optimal table count
    $recommendedTables = $this->available_tables ?? 1;
    $minDays = $currentDaysNeeded;
    $bestDailyMatches = $currentDailyMatches;

    for ($tables = 1; $tables <= 10; $tables++) {
        $dailyMatches = $this->calculateDailyMatchesForTables($tables);
        
        if ($dailyMatches > 0) {
            $daysNeeded = $this->safeCeilDivision($totalMatches, $dailyMatches);
            
            \Log::debug("Table optimization check:", [
                'tables' => $tables,
                'dailyMatches' => $dailyMatches,
                'daysNeeded' => $daysNeeded,
                'minDays' => $minDays
            ]);

            if ($daysNeeded > 0 && $daysNeeded < $minDays) {
                $minDays = $daysNeeded;
                $recommendedTables = $tables;
                $bestDailyMatches = $dailyMatches;
            }
        }
    }

    // Final safety check
    if ($minDays <= 0 || $bestDailyMatches <= 0) {
        \Log::warning("Optimization: Invalid final values, using fallback", [
            'minDays' => $minDays,
            'bestDailyMatches' => $bestDailyMatches
        ]);
        return $this->getFallbackOptimizationResult($totalMatches, $participantCount);
    }

    // Hitung efficiency dengan approach yang lebih aman
    $efficiency = $this->calculateEfficiencySafe($totalMatches, $bestDailyMatches, $minDays);

    \Log::info("=== OPTIMIZATION RESULT ===", [
        'recommended_tables' => $recommendedTables,
        'estimated_days' => $minDays,
        'efficiency' => $efficiency,
        'bestDailyMatches' => $bestDailyMatches,
        'totalMatches' => $totalMatches
    ]);

    return [
        'recommended_tables' => $recommendedTables,
        'estimated_days' => $minDays,
        'efficiency' => $efficiency,
        'matches_per_day' => $bestDailyMatches,
        'total_duration_days' => $minDays,
        'total_matches' => $totalMatches,
        'participant_count' => $participantCount,
        'status' => 'calculated'
    ];
}

/**
 * Alternative efficiency calculation yang lebih aman
 */
private function calculateEfficiencySafe($totalMatches, $dailyMatches, $days)
{
    // Approach yang lebih sederhana dan aman
    $maxPossibleMatches = $dailyMatches * $days;
    
    // Jika maxPossibleMatches 0, return 0
    if ($maxPossibleMatches <= 0) {
        return 0;
    }
    
    // Jika totalMatches lebih besar dari max possible, cap di 100%
    if ($totalMatches >= $maxPossibleMatches) {
        return 100.0;
    }
    
    // Normal calculation
    $efficiency = ($totalMatches / $maxPossibleMatches) * 100;
    
    // Ensure valid result
    if (!is_numeric($efficiency) || $efficiency < 0) {
        return 0;
    }
    
    if ($efficiency > 100) {
        return 100.0;
    }
    
    return round($efficiency, 1);
}

/**
 * Safe method untuk mendapatkan total daily matches
 */
private function getSafeTotalDailyMatches()
{
    try {
        $value = $this->total_daily_matches;
        return is_numeric($value) && $value > 0 ? floatval($value) : 1;
    } catch (\Exception $e) {
        \Log::error("getSafeTotalDailyMatches error: " . $e->getMessage());
        return 1;
    }
}

/**
 * Safe division dengan ceiling - menghindari division by zero
 */
/**
 * Safe division dengan comprehensive checks
 */
private function safeCeilDivision($numerator, $denominator)
{
    // Validasi input
    if (!is_numeric($numerator) || !is_numeric($denominator)) {
        \Log::warning("safeCeilDivision: Non-numeric input", [
            'numerator' => $numerator,
            'denominator' => $denominator
        ]);
        return 1;
    }

    $numerator = floatval($numerator);
    $denominator = floatval($denominator);

    // Check untuk zero denominator
    if ($denominator == 0) {
        \Log::warning("safeCeilDivision: Denominator is zero", [
            'numerator' => $numerator,
            'denominator' => $denominator
        ]);
        return max(1, $numerator); // Return numerator atau minimal 1
    }

    // Check untuk negative values
    if ($denominator < 0) {
        \Log::warning("safeCeilDivision: Denominator is negative", [
            'numerator' => $numerator,
            'denominator' => $denominator
        ]);
        $denominator = abs($denominator); // Use absolute value
    }

    if ($numerator <= 0) {
        \Log::warning("safeCeilDivision: Numerator is zero or negative", [
            'numerator' => $numerator,
            'denominator' => $denominator
        ]);
        return 1; // Minimal 1 hari
    }

    try {
        $result = ceil($numerator / $denominator);
        
        // Validasi result
        if (!is_numeric($result) || $result <= 0 || is_infinite($result)) {
            \Log::warning("safeCeilDivision: Invalid result", [
                'result' => $result,
                'numerator' => $numerator,
                'denominator' => $denominator
            ]);
            return 1;
        }

        return (int)max(1, $result); // Pastikan minimal 1 hari

    } catch (\DivisionByZeroError $e) {
        \Log::error("safeCeilDivision: DivisionByZeroError", [
            'error' => $e->getMessage(),
            'numerator' => $numerator,
            'denominator' => $denominator
        ]);
        return 1;
    } catch (\Exception $e) {
        \Log::error("safeCeilDivision: General error", [
            'error' => $e->getMessage(),
            'numerator' => $numerator,
            'denominator' => $denominator
        ]);
        return 1;
    }
}

/**
 * Helper method untuk menghitung daily matches berdasarkan jumlah tables
 */
private function calculateDailyMatchesForTables($tableCount)
{
    $matchesPerDay = $this->matches_per_day;
    $totalDailyMatches = $matchesPerDay * $tableCount;
    
    // Pastikan nilai valid
    return max(1, min($totalDailyMatches, 1000));
}

/**
 * Helper method untuk menghitung daily matches berdasarkan jumlah tables
 */

/**
 * Calculate efficiency dengan safety checks
 */
// Di model Tournament.php - perbaiki method calculateEfficiency dengan safety checks lengkap
private function calculateEfficiency($totalMatches, $dailyMatches, $days)
{
    \Log::info("calculateEfficiency Input:", [
        'totalMatches' => $totalMatches,
        'dailyMatches' => $dailyMatches,
        'days' => $days
    ]);

    // Comprehensive safety checks
    if (!is_numeric($totalMatches) || !is_numeric($dailyMatches) || !is_numeric($days)) {
        \Log::warning("calculateEfficiency: Invalid input types", [
            'totalMatches_type' => gettype($totalMatches),
            'dailyMatches_type' => gettype($dailyMatches),
            'days_type' => gettype($days)
        ]);
        return 0;
    }

    // Convert ke float untuk memastikan
    $totalMatches = floatval($totalMatches);
    $dailyMatches = floatval($dailyMatches);
    $days = floatval($days);

    // Check untuk zero, negative, atau infinite values
    if ($dailyMatches <= 0) {
        \Log::warning("calculateEfficiency: dailyMatches is zero or negative", ['dailyMatches' => $dailyMatches]);
        return 0;
    }

    if ($days <= 0) {
        \Log::warning("calculateEfficiency: days is zero or negative", ['days' => $days]);
        return 0;
    }

    if ($totalMatches <= 0) {
        \Log::warning("calculateEfficiency: totalMatches is zero or negative", ['totalMatches' => $totalMatches]);
        return 0;
    }

    // Check untuk division by zero di dalam calculation
    $totalPossibleMatches = $dailyMatches * $days;
    
    if ($totalPossibleMatches <= 0) {
        \Log::warning("calculateEfficiency: totalPossibleMatches is zero or negative", [
            'totalPossibleMatches' => $totalPossibleMatches,
            'dailyMatches' => $dailyMatches,
            'days' => $days
        ]);
        return 0;
    }

    // Additional check untuk memastikan tidak infinity
    if (is_infinite($totalPossibleMatches)) {
        \Log::warning("calculateEfficiency: totalPossibleMatches is infinite", [
            'dailyMatches' => $dailyMatches,
            'days' => $days
        ]);
        return 0;
    }

    try {
        // Calculate efficiency dengan try-catch
        $efficiency = ($totalMatches / $totalPossibleMatches) * 100;
        
        // Check untuk valid numeric result
        if (!is_numeric($efficiency) || is_nan($efficiency) || is_infinite($efficiency)) {
            \Log::warning("calculateEfficiency: Invalid efficiency calculation", [
                'efficiency' => $efficiency,
                'totalMatches' => $totalMatches,
                'totalPossibleMatches' => $totalPossibleMatches
            ]);
            return 0;
        }

        // Batasi antara 0-100% dan round
        $efficiency = max(0, min(100, $efficiency));
        return round($efficiency, 1);

    } catch (\DivisionByZeroError $e) {
        \Log::error("calculateEfficiency: DivisionByZeroError caught", [
            'error' => $e->getMessage(),
            'totalMatches' => $totalMatches,
            'dailyMatches' => $dailyMatches,
            'days' => $days,
            'totalPossibleMatches' => $totalPossibleMatches
        ]);
        return 0;
    } catch (\Exception $e) {
        \Log::error("calculateEfficiency: General error", [
            'error' => $e->getMessage(),
            'totalMatches' => $totalMatches,
            'dailyMatches' => $dailyMatches,
            'days' => $days
        ]);
        return 0;
    }
}
/**
 * Default result ketika tidak ada data yang cukup
 */
private function getDefaultOptimizationResult($participantCount, $totalMatches)
{
    return [
        'recommended_tables' => 1,
        'estimated_days' => 0,
        'efficiency' => 0,
        'matches_per_day' => 0,
        'total_duration_days' => 0,
        'total_matches' => $totalMatches,
        'participant_count' => $participantCount,
        'status' => 'insufficient_data',
        'message' => $participantCount < 2 ? 'Need at least 2 participants' : 'No matches estimated'
    ];
}

/**
 * Fallback result ketika optimization gagal
 */
private function getFallbackOptimizationResult($totalMatches, $participantCount)
{
    // Fallback calculation
    $fallbackTables = 1;
    $fallbackDailyMatches = max(1, $this->calculateDailyMatchesForTables($fallbackTables));
    $fallbackDays = max(1, ceil($totalMatches / $fallbackDailyMatches));
    $fallbackEfficiency = $this->calculateEfficiency($totalMatches, $fallbackDailyMatches, $fallbackDays);

    return [
        'recommended_tables' => $fallbackTables,
        'estimated_days' => $fallbackDays,
        'efficiency' => $fallbackEfficiency,
        'matches_per_day' => $fallbackDailyMatches,
        'total_duration_days' => $fallbackDays,
        'total_matches' => $totalMatches,
        'participant_count' => $participantCount,
        'status' => 'fallback_calculation'
    ];
}

/**
 * Helper method untuk menghitung daily matches berdasarkan jumlah tables
 */
   
    // Hitung durasi aktual turnamen
    public function calculateActualDuration()
    {
        if ($this->status === 'completed' && $this->matches()->where('status', 'completed')->exists()) {
            $firstMatch = $this->matches()->where('status', 'completed')->orderBy('match_date')->first();
            $lastMatch = $this->matches()->where('status', 'completed')->orderBy('match_date', 'desc')->first();
            
            if ($firstMatch && $lastMatch) {
                $start = $firstMatch->match_date;
                $end = $lastMatch->match_date;
                $duration = $end->diffInMinutes($start);
                
                $this->update(['actual_duration_minutes' => $duration]);
            }
        }
    }

    // Estimasi durasi berdasarkan jumlah pemain dan match
    public function estimateDuration()
    {
        $estimatedMinutes = 0;
        
        switch ($this->type) {
            case 'single':
                // Single elimination: (n-1) matches, avg 15 menit per match
                $estimatedMinutes = ($this->max_players - 1) * 15;
                break;
            case 'double':
                // Double elimination: lebih banyak matches
                $estimatedMinutes = ($this->max_players * 2 - 2) * 15;
                break;
            case 'duo':
                // Double elimination: lebih banyak matches
                $estimatedMinutes = ($this->max_players * 2 - 2) * 15;
                break;
            case 'team':
                // Team tournament: lebih lama per match
                $estimatedMinutes = ($this->max_players / 2) * 25;
                break;
        }

        $this->update(['estimated_duration_minutes' => $estimatedMinutes]);
    }

    public function getCompletedMatchesAttribute()
    {
        return $this->matches->where('status', 'completed')->count();
    }

    public function getTotalMatchesAttribute()
    {
        return $this->matches->count();
    }

    public function getProgressAttribute()
    {
        return $this->total_matches > 0 ? 
            round(($this->completed_matches / $this->total_matches) * 100, 2) : 0;
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'tournament_player')
            ->withPivot('seed', 'group', 'representing_ptm_club_id', 'is_representing_different_club', 'representation_notes')
            ->withTimestamps();
    }

    // Method untuk generate draw
    public function generateDraw(string $method = 'random')
    {
        $drawService = new \App\Services\TournamentDrawService($this);
        return $drawService->generateDraw($method);
    }

    // Method untuk mendapatkan bracket data
    public function getBracketData()
    {
        $drawService = new \App\Services\TournamentDrawService($this);
        return $drawService->getBracketData();
    }
    // Method untuk mendapatkan players dengan representation info
    // public function getPlayersWithRepresentation()
    // {
    //     return $this->players()->with(['ptmClub', 'representingPtmClub'])->get();
    // }
    /**
     * Method untuk mendapatkan players dengan representation info
     */
    public function tournamentPlayers()
    {
        return $this->hasMany(TournamentPlayer::class)->OrderBy('id', 'asc');
    }

    public function getPlayersWithRepresentation()
    {
        return $this->tournamentPlayers()->with(['player.ptmClub', 'representingClub'])->get();
    }
    public function addPlayerWithRepresentation(Player $player, $representingClubId = null, $seed = null, $group = null, $notes = null)
    {
        $isRepresentingDifferent = $representingClubId && $representingClubId != $player->ptm_club_id;
        
        // Gunakan TournamentPlayer model
        return TournamentPlayer::create([
            'tournament_id' => $this->id,
            'player_id' => $player->id,
            'representing_ptm_club_id' => $representingClubId,
            'is_representing_different_club' => $isRepresentingDifferent,
            'representation_notes' => $notes,
            'seed' => $seed,
            'group' => $group
        ]);

        // Record representation history jika berbeda klub
        if ($isRepresentingDifferent) {
            PlayerRepresentationHistory::create([
                'player_id' => $player->id,
                'tournament_id' => $this->id,
                'original_ptm_club_id' => $player->ptm_club_id,
                'representing_ptm_club_id' => $representingClubId,
                'reason' => 'tournament_representation',
                'notes' => $notes
            ]);
        }

        // Update player stats
        $player->updateStats();

        return $this;
    }
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'secondary',
            'registration_open' => 'info',
            'ongoing' => 'success',
            'completed' => 'primary',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Scope for active tournaments
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['registration_open', 'ongoing']);
    }

    /**
     * Scope for upcoming tournaments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
                    ->where('status', 'registration_open');
    }

    /**
     * Check if registration is open
     */
    public function getIsRegistrationOpenAttribute()
    {
        return $this->status === 'registration_open' && 
            now()->lte($this->registration_deadline);
    }

    /**
     * Get remaining spots
     */
    public function getRemainingSpotsAttribute()
    {
        $registeredCount = $this->players()->count();
        return max(0, $this->max_players - $registeredCount);
    }
    public function isDuoType()
    {
        return in_array($this->type, [self::TYPE_DOUBLE, self::TYPE_DOUBLEDUO]);
    }

    /**
     * Check if player can be added individually for this tournament type
     */
    public function canAddIndividualPlayers()
    {
        return $this->isDuoType();
    }

    /**
     * Get registered players count for duo pairing
     */
    public function getIndividualPlayersCount()
    {
        return $this->players()->count();
    }

    /**
     * Check if we have even number of players for duo tournament
     */
    public function hasEvenPlayersForDuo()
    {
        return $this->getIndividualPlayersCount() % 2 === 0;
    }

    /**
     * Get unpaired players for duo tournament
     */
    public function getUnpairedPlayers()
    {
        // Dapatkan semua player IDs yang sudah dipasangkan
        $pairedPlayerIds = $this->duoPairs()
            ->where(function($query) {
                $query->whereNotNull('player1_id')
                    ->orWhereNotNull('player2_id');
            })
            ->get()
            ->flatMap(function($pair) {
                return [$pair->player1_id, $pair->player2_id];
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Return players yang belum dipasangkan
        return $this->players()
            ->whereNotIn('players.id', $pairedPlayerIds)
            ->get();
    }

/**
 * Get players that are available for pairing (not in any confirmed pair)
 */
    public function getAvailablePlayersForPairing()
    {
        $confirmedPairPlayerIds = $this->duoPairs()
            ->where('status', 'confirmed')
            ->get()
            ->flatMap(function($pair) {
                return [$pair->player1_id, $pair->player2_id];
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return $this->players()
            ->whereNotIn('players.id', $confirmedPairPlayerIds)
            ->OrderBy('nickname', 'asc')
            ->get();
    }
    public function duoPairs()
    {
        return $this->hasMany(TournamentDuoPair::class);
    }

    public function confirmedDuoPairs()
    {
        return $this->hasMany(TournamentDuoPair::class)->where('status', 'confirmed');
    }


    public function isPlayerInPair($playerId)
    {
        return $this->duoPairs()
            ->where(function($query) use ($playerId) {
                $query->where('player1_id', $playerId)
                    ->orWhere('player2_id', $playerId);
            })
            ->where('status', 'confirmed')
            ->exists();
    }

/**
 * Get pair for a specific player
 */
    public function getPlayerPair($playerId)
    {
        return $this->duoPairs()
            ->where(function($query) use ($playerId) {
                $query->where('player1_id', $playerId)
                    ->orWhere('player2_id', $playerId);
            })
            ->where('status', 'confirmed')
            ->first();
    }

    /**
     * Get all individual players (not in any pair)
     */
    public function getIndividualPlayers()
    {
        return $this->getUnpairedPlayers();
    }

    public function logTournamentSettings()
    {
        \Log::info("Tournament Settings Debug:", [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'available_tables' => $this->available_tables,
            'matches_per_table' => $this->matches_per_table,
            'estimated_match_duration' => $this->estimated_match_duration,
            'break_between_matches' => $this->break_between_matches,
            'warmup_time' => $this->warmup_time,
            'daily_start_time' => $this->daily_start_time,
            'daily_end_time' => $this->daily_end_time,
            'max_daily_playing_hours' => $this->max_daily_playing_hours,
            'calculated_total_daily_matches' => $this->total_daily_matches,
            'calculated_matches_per_day' => $this->matches_per_day,
            'calculated_daily_playing_minutes' => $this->daily_playing_minutes,
            'calculated_total_match_time' => $this->total_match_time
        ]);
    }

    // Panggil method ini di controller sebelum optimizeTableAllocation
    public function getOptimizationWithDebug()
    {
        $this->logTournamentSettings();
        return $this->optimizeTableAllocation();
    }
}