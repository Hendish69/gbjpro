<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    use HasFactory;

     protected $fillable = [
        'tournament_id',
        'match_type',
        'match_format',
        'player1_id',
        'player2_id',
        'player1_partner_id', // Pastikan ini ada
        'player2_partner_id', // Pastikan ini ada
        'team1_id',
        'team2_id',
        'bracket_type',
        'round_number',
        'match_number',
        'group_name',
        'match_date',
        'table_id',
        'duration_minutes', // Pastikan ini ada
        'status',
        'winner_id',
        'winning_side',
        'player1_score',
        'player2_score',
        'team1_score',
        'team2_score',
        'set_scores',
        'notes',
        'metadata',
        'next_match_id',
        'next_match_position'
    ];
    // protected $fillable = [
    //     'tournament_id',
    //     'match_type',
    //     'match_format',
    //     'player1_id',
    //     'player2_id',
    //     'player1_partner_id',
    //     'player2_partner_id',
    //     'team1_id',
    //     'team2_id',
    //     'bracket_type',
    //     'round_number',
    //     'match_number',
    //     'group_name',
    //     'match_date',
    //     'table_id',
    //     'duration_minutes',
    //     'status',
    //     'winner_id',
    //     'winning_side',
    //     'player1_score',
    //     'player2_score',
    //     'team1_score',
    //     'team2_score',
    //     'set_scores',
    //     'notes',
    //     'metadata',
    //     'next_match_id',
    //     'next_match_position',
    //     'duration_minutes'
    // ];

    
    protected $casts = [
        'match_date' => 'datetime',
        'set_scores' => 'array',
        'metadata' => 'array'
    ];

    public function getEndTimeAttribute()
    {
        if (!$this->match_date || !$this->duration_minutes) {
            return null;
        }
        
        return $this->match_date->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Check if match is ongoing
     */
    public function getIsOngoingAttribute()
    {
        if ($this->status !== 'ongoing') {
            return false;
        }

        $now = now();
        return $this->match_date <= $now && $this->end_time >= $now;
    }
    // Relationships
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player1()
    {
        return $this->belongsTo(Player::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(Player::class, 'player2_id');
    }

    public function player1Partner()
    {
        return $this->belongsTo(Player::class, 'player1_partner_id');
    }

    public function player2Partner()
    {
        return $this->belongsTo(Player::class, 'player2_partner_id');
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function winner()
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function nextMatch()
    {
        return $this->belongsTo(Matches::class, 'next_match_id');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByRound($query, $round)
    {
        return $query->where('round_number', $round);
    }

    public function scopeByBracket($query, $bracket)
    {
        return $query->where('bracket_type', $bracket);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group_name', $group);
    }

    // Attributes
    public function getIsScheduledAttribute()
    {
        return $this->status === 'scheduled';
    }

   
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getParticipantsAttribute()
    {
        if ($this->match_type === 'team') {
            return [
                'team1' => $this->team1,
                'team2' => $this->team2
            ];
        }

        if ($this->match_type === 'double') {
            return [
                'pair1' => [
                    'player1' => $this->player1,
                    'player2' => $this->player1Partner
                ],
                'pair2' => [
                    'player1' => $this->player2,
                    'player2' => $this->player2Partner
                ]
            ];
        }

        return [
            'player1' => $this->player1,
            'player2' => $this->player2
        ];
    }

    // Methods
    public function canStart()
    {
        return $this->isScheduled && 
               $this->hasBothParticipants() &&
               !$this->isByeMatch();
    }

    public function hasBothParticipants()
    {
        if ($this->match_type === 'team') {
            return $this->team1_id && $this->team2_id;
        }

        if ($this->match_type === 'double') {
            return $this->player1_id && $this->player1_partner_id && 
                   $this->player2_id && $this->player2_partner_id;
        }

        return $this->player1_id && $this->player2_id;
    }

    public function isByeMatch()
    {
        if ($this->match_type === 'team') {
            return !$this->team1_id || !$this->team2_id;
        }

        return !$this->player1_id || !$this->player2_id;
    }

    public function recordScore($score1, $score2, $setScores = null)
    {
        if ($this->match_type === 'team') {
            $this->team1_score = $score1;
            $this->team2_score = $score2;
            $winnerId = $score1 > $score2 ? $this->team1_id : $this->team2_id;
            $winningSide = $score1 > $score2 ? 'team1' : 'team2';
        } else {
            $this->player1_score = $score1;
            $this->player2_score = $score2;
            $winnerId = $score1 > $score2 ? $this->player1_id : $this->player2_id;
            $winningSide = $score1 > $score2 ? 'player1' : 'player2';
        }

        $this->set_scores = $setScores;
        $this->winner_id = $winnerId;
        $this->winning_side = $winningSide;
        $this->status = 'completed';
        
        $this->save();

        // Update next match if exists
        $this->updateNextMatch();

        return $this;
    }

    protected function updateNextMatch()
    {
        if ($this->next_match_id && $this->next_match_position) {
            $nextMatch = Matches::find($this->next_match_id);
            
            if ($nextMatch) {
                if ($this->next_match_position === 'player1') {
                    if ($nextMatch->match_type === 'team') {
                        $nextMatch->team1_id = $this->winner_id;
                    } else {
                        $nextMatch->player1_id = $this->winner_id;
                    }
                } else {
                    if ($nextMatch->match_type === 'team') {
                        $nextMatch->team2_id = $this->winner_id;
                    } else {
                        $nextMatch->player2_id = $this->winner_id;
                    }
                }
                
                $nextMatch->save();
            }
        }
    }


    public function parentMatch1()
    {
        return $this->belongsTo(Matches::class, 'parent_match1_id');
    }

    public function parentMatch2()
    {
        return $this->belongsTo(Matches::class, 'parent_match2_id');
    }

    public function childMatches()
    {
        return $this->hasMany(Matches::class, 'parent_match1_id')
            ->orWhere('parent_match2_id', $this->id);
    }
    

    public function sets()
    {
        return $this->hasMany(Set::class, 'match_id'); // Explicitly specify foreign key
    }

    // Accessors
    public function getScoreAttribute()
    {
        return $this->player1_score . ' - ' . $this->player2_score;
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->duration_minutes) return '-';
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        
        return "{$minutes}m";
    }

    // Method untuk menghitung durasi match
    public function calculateDuration()
    {
        if ($this->sets->count() > 0) {
            // Estimasi: 5 menit per set + 2 menit istirahat antar set
            $estimatedMinutes = ($this->sets->count() * 5) + (($this->sets->count() - 1) * 2);
            $this->update(['duration_minutes' => $estimatedMinutes]);
        }
    }

    // Method untuk assign meja
    public function assignTable($tableId)
    {
        // Kosongkan meja sebelumnya jika ada
        if ($this->table_id) {
            $previousTable = Table::find($this->table_id);
            if ($previousTable) {
                $previousTable->update(['status' => 'available']);
            }
        }

        // Assign meja baru
        $table = Table::find($tableId);
        if ($table) {
            $this->update(['table_id' => $tableId]);
            $table->update(['status' => 'occupied']);
        }
    }

    // Method ketika match selesai
    public function completeMatch()
    {
        $this->update(['status' => 'completed']);
        
        // Kosongkan meja
        if ($this->table_id) {
            $table = Table::find($this->table_id);
            if ($table) {
                $table->update(['status' => 'available']);
            }
        }

        // Hitung durasi
        $this->calculateDuration();

        // Update durasi turnamen
        $this->tournament->calculateActualDuration();
    }
    
    // Method untuk menentukan pemenang berdasarkan sets
    public function determineWinnerFromSets()
    {
        $player1Sets = 0;
        $player2Sets = 0;

        foreach ($this->sets as $set) {
            if ($set->winner === 'player1') {
                $player1Sets++;
            } elseif ($set->winner === 'player2') {
                $player2Sets++;
            }
        }

        // Update scores
        $this->update([
            'player1_score' => $player1Sets,
            'player2_score' => $player2Sets,
            'winner_id' => $player1Sets > $player2Sets ? $this->player1_id : 
                          ($player2Sets > $player1Sets ? $this->player2_id : null)
        ]);

        return $this->winner_id;
    }
}