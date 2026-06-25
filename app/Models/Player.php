<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'nickname', 'ptm_number', 'ptm_club_id', 'division_ranking', 
        'previous_division_ranking', 'email', 'phone', 'date_of_birth', 'gender', 
        'photo', 'bio', 'is_active', 'is_in_library', 'playing_style', 
        'grip_style', 'preferences', 'last_played_at', 'total_tournaments'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_played_at' => 'datetime',
        'preferences' => 'array',
        'is_active' => 'boolean',
        'is_in_library' => 'boolean',
        'division_ranking' => 'integer',
        'previous_division_ranking' => 'integer',
    ];
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setNicknameAttribute($value)
    {
        $this->attributes['nickname'] = strtoupper($value);
    }

    // Relationships
    public function matchesAsPlayer1()
    {
        return $this->hasMany(Matches::class, 'player1_id');
    }

    public function matchesAsPlayer2()
    {
        return $this->hasMany(Matches::class, 'player2_id');
    }

    public function wonMatches()
    {
        return $this->hasMany(Matches::class, 'winner_id');
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_player')
            ->withPivot('seed', 'group')
            ->withTimestamps();
    }

    // public function categories()
    // {
    //     // return $this->belongsToMany(PlayerCategory::class, 'player_category');
    //     return $this->belongsToMany(PlayerCategory::class, 'player_category', 'player_id', 'player_category_id')
    //                 ->withTimestamps();
    // }

   // Relationship dengan players dengan representation
public function players()
{
    return $this->belongsToMany(Player::class, 'tournament_player')
        ->withPivot('seed', 'group', 'representing_ptm_club_id', 'is_representing_different_club', 'representation_notes')
        ->withTimestamps();
}

// Method untuk mendapatkan players dengan representation info
public function getPlayersWithRepresentation()
{
    return $this->players()->with(['ptmClub', 'representingPtmClub'])->get();
}

// Method untuk menambahkan player dengan representation
    public function addPlayerWithRepresentation(Player $player, $representingClubId = null, $seed = null, $group = null, $notes = null)
    {
        $isRepresentingDifferent = $representingClubId && $representingClubId != $player->ptm_club_id;
        
        $this->players()->attach($player->id, [
            'seed' => $seed,
            'group' => $group,
            'representing_ptm_club_id' => $representingClubId,
            'is_representing_different_club' => $isRepresentingDifferent,
            'representation_notes' => $notes
        ]);

        // Record representation history
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
    public function ptmClub()
    {
        return $this->belongsTo(PTMClub::class, 'ptm_club_id');
    }
    
    public function representingPtmClub()
    {
        return $this->belongsTo(PTMClub::class, 'representing_ptm_club_id');
    }
    public function representationHistories()
    {
        return $this->hasMany(PlayerRepresentationHistory::class)->orderBy('created_at', 'desc');
    }

    // Accessor untuk mendapatkan klub yang diwakili di turnamen tertentu
    public function getRepresentingClubForTournament($tournamentId)
    {
        $participation = $this->tournaments()->where('tournament_id', $tournamentId)->first();
        return $participation ? $participation->pivot->representing_ptm_club_id : null;
    }

    // Method untuk mewakili klub lain di turnamen
    public function representClubInTournament(Tournament $tournament, $representingClubId, $notes = null)
    {
        $isRepresentingDifferent = $representingClubId && $representingClubId != $this->ptm_club_id;
        
        // Update tournament participation
        $this->tournaments()->updateExistingPivot($tournament->id, [
            'representing_ptm_club_id' => $representingClubId,
            'is_representing_different_club' => $isRepresentingDifferent,
            'representation_notes' => $notes
        ]);

        // Record history jika berbeda klub
        if ($isRepresentingDifferent) {
            PlayerRepresentationHistory::create([
                'player_id' => $this->id,
                'tournament_id' => $tournament->id,
                'original_ptm_club_id' => $this->ptm_club_id,
                'representing_ptm_club_id' => $representingClubId,
                'reason' => 'tournament_representation',
                'notes' => $notes
            ]);
        }

        return $this;
    }

    // Method untuk mendapatkan klub yang sedang diwakili
    public function getCurrentRepresentationAttribute()
    {
        $currentTournament = $this->tournaments()
            ->where('status', 'ongoing')
            ->orWhere('status', 'upcoming')
            ->orderBy('start_date', 'desc')
            ->first();

        if ($currentTournament) {
            $representingClubId = $currentTournament->pivot->representing_ptm_club_id;
            if ($representingClubId && $representingClubId != $this->ptm_club_id) {
                return PTMClub::find($representingClubId);
            }
        }

        return null;
    }
    public function divisionRankingHistories()
    {
        return $this->hasMany(DivisionRankingHistory::class)->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getTotalMatchesAttribute()
    {
        return $this->matchesAsPlayer1->count() + $this->matchesAsPlayer2->count();
    }

    public function getWinRateAttribute()
    {
        $totalMatches = $this->total_matches;
        $wonMatches = $this->wonMatches->count();
        
        return $totalMatches > 0 ? round(($wonMatches / $totalMatches) * 100, 2) : 0;
    }

    public function getAllMatchesAttribute()
    {
        return $this->matchesAsPlayer1->merge($this->matchesAsPlayer2)->sortByDesc('created_at');
    }

    public function getWinsCountAttribute()
    {
        return $this->wonMatches->count();
    }

    public function getLossesCountAttribute()
    {
        return $this->total_matches - $this->wins_count;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getDisplayNameAttribute()
    {
        return $this->name ? $this->name.' ('.$this->nickname.')' : $this->name;
    }

    public function getFullPtmInfoAttribute()
    {
        $info = [];
        if ($this->ptm_number) {
            $info[] = 'No: ' . $this->ptm_number;
        }
        if ($this->ptmClub) {
            $info[] = $this->ptmClub->name;
        }
        return $info ? implode(' | ', $info) : null;
    }

    public function getDivisionNameAttribute()
    {
        return $this->division_ranking ? 'Division ' . $this->division_ranking : 'Belum ada divisi';
    }

    public function getDivisionColorAttribute()
    {
        // Warna berdasarkan divisi (1=merah, 11=abu-abu)
        $colors = [
            1 => '#dc3545',   // Merah - Elite
            2 => '#fd7e14',   // Orange
            3 => '#ffc107',   // Kuning
            4 => '#20c997',   // Hijau
            5 => '#0dcaf0',   // Biru
            6 => '#6f42c1',   // Ungu
            7 => '#d63384',   // Pink
            8 => '#6610f2',   // Indigo
            9 => '#6c757d',   // Abu-abu
            10 => '#adb5bd',  // Abu-abu muda
            11 => '#dee2e6',  // Abu-abu sangat muda
        ];

        return $colors[$this->division_ranking] ?? '#6c757d';
    }

    public function getRankingChangeAttribute()
    {
        if ($this->previous_division_ranking && $this->division_ranking) {
            return $this->division_ranking - $this->previous_division_ranking;
        }
        return 0;
    }

    public function getRankingChangeTypeAttribute()
    {
        $change = $this->ranking_change;
        
        if ($change < 0) {
            return 'promotion'; // Ranking turun = lebih baik
        } elseif ($change > 0) {
            return 'demotion'; // Ranking naik = lebih buruk
        } else {
            return 'no_change';
        }
    }

   // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInLibrary($query)
    {
        return $query->where('is_in_library', true);
    }

    public function scopeByDivision($query, $division)
    {
        return $query->where('division_ranking', $division);
    }

    public function scopeByDivisionRange($query, $min, $max)
    {
        return $query->whereBetween('division_ranking', [$min, $max]);
    }

    public function scopeByPtmClub($query, $clubId)
    {
        return $query->where('ptm_club_id', $clubId);
    }

    public function scopeOrderByDivision($query, $direction = 'asc')
    {
        return $query->orderBy('division_ranking', $direction);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
            ->orWhere('nickname', 'ilike', "%{$search}%")
            ->orWhere('ptm_number', 'ilike', "%{$search}%")
            ->orWhere('email', 'ilike', "%{$search}%")
            ->orWhereHas('ptmClub', function($clubQuery) use ($search) {
                $clubQuery->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%");
            });
        });
    }

    // Methods
    public function updateStats()
    {
        $this->update([
            'total_tournaments' => $this->tournaments()->count(),
            'last_played_at' => $this->getLastPlayedDate(),
        ]);
    }

    private function getLastPlayedDate()
    {
        $lastMatch = $this->all_matches->where('status', 'completed')->sortByDesc('match_date')->first();
        return $lastMatch ? $lastMatch->match_date : null;
    }

    public function addToTournament(Tournament $tournament, $seed = null, $group = null)
    {
        if (!$this->is_in_library) {
            $this->update(['is_in_library' => true]);
        }

        $this->tournaments()->attach($tournament->id, [
            'seed' => $seed,
            'group' => $group
        ]);

        $this->updateStats();
        return $this;
    }

    public function removeFromTournament(Tournament $tournament)
    {
        $this->tournaments()->detach($tournament->id);
        $this->updateStats();
    }

    public function updateDivisionRanking($newRanking, $reason = 'tournament_result', $notes = null)
    {
        // Validasi ranking 1-11
        if ($newRanking < 1 || $newRanking > 11) {
            throw new \Exception('Division ranking must be between 1 and 11');
        }

        $oldRanking = $this->division_ranking;
        
        // Update ranking
        $this->update([
            'previous_division_ranking' => $oldRanking,
            'division_ranking' => $newRanking,
        ]);

        // Record history
        DivisionRankingHistory::create([
            'player_id' => $this->id,
            'old_ranking' => $oldRanking,
            'new_ranking' => $newRanking,
            'reason' => $reason,
            'notes' => $notes,
        ]);

        return [
            'old_ranking' => $oldRanking,
            'new_ranking' => $newRanking,
            'change_type' => $this->ranking_change_type
        ];
    }

    public function promote($reason = 'tournament_win', $notes = null)
    {
        $currentRanking = $this->division_ranking ?? 11;
        $newRanking = max(1, $currentRanking - 1); // Ranking lebih kecil = lebih baik
        
        return $this->updateDivisionRanking($newRanking, $reason, $notes);
    }

    public function demote($reason = 'tournament_loss', $notes = null)
    {
        $currentRanking = $this->division_ranking ?? 1;
        $newRanking = min(11, $currentRanking + 1); // Ranking lebih besar = lebih buruk
        
        return $this->updateDivisionRanking($newRanking, $reason, $notes);
    }

    public static function findOrCreateFromData(array $playerData)
    {
        // Cari berdasarkan PTM number, email, atau nama
        $player = static::when(isset($playerData['ptm_club_id']), function($query) use ($playerData) {
                return $query->where('ptm_club_id', $playerData['ptm_club_id']);
            })
            ->when(!isset($playerData['ptm_club_id']), function($query) use ($playerData) {
                return $query->where('email', $playerData['email'] ?? '')
                           ->orWhere('name', $playerData['name']);
            })
            ->first();

        if (!$player) {
            // Buat player baru
            $player = static::create([
                'name' => $playerData['name'],
                'nickname' => $playerData['nickname'] ?? null,
                'ptm_club_id' => $playerData['ptm_club_id'] ?? null,
                'division_ranking' => $playerData['division_ranking'] ?? 11, // Default divisi 11 (pemula)
                'email' => $playerData['email'] ?? null,
                'phone' => $playerData['phone'] ?? null,
                'gender' => $playerData['gender'] ?? null,
                'date_of_birth' => $playerData['date_of_birth'] ?? null,
                'playing_style' => $playerData['playing_style'] ?? null,
                'is_in_library' => true,
            ]);
        } else {
            // Update data jika diperlukan
            $updateData = [];
            $fields = ['nickname', 'division_ranking', 'ptm_club_id', 'phone', 'gender', 'date_of_birth', 'playing_style'];
            
            foreach ($fields as $field) {
                if (isset($playerData[$field]) && empty($player->$field)) {
                    $updateData[$field] = $playerData[$field];
                }
            }
            
            // Update division ranking jika diberikan
            if (isset($playerData['division_ranking'])) {
                $updateData['division_ranking'] = $playerData['division_ranking'];
            }
            
            if (!empty($updateData)) {
                $player->update($updateData);
            }

            // Pastikan player ada di library
            if (!$player->is_in_library) {
                $player->update(['is_in_library' => true]);
            }
        }

        return $player;
    }
}