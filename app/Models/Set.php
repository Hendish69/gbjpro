<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id', 'set_number', 'player1_score', 'player2_score',
        'team1_score', 'team2_score', 'duration_minutes', 
        'point_sequence', 'notes'
    ];

    protected $casts = [
        'point_sequence' => 'array'
    ];

    public function match()
    {
        return $this->belongsTo(Matches::class);
    }

    public function getWinnerAttribute()
    {
        if ($this->player1_score > $this->player2_score) {
            return 'player1';
        } elseif ($this->player2_score > $this->player1_score) {
            return 'player2';
        } else {
            return 'draw';
        }
    }

    // Accessor untuk format score
    public function getScoreAttribute()
    {
        return $this->player1_score . ' - ' . $this->player2_score;
    }

    public function getTeamScoreAttribute()
    {
        return $this->team1_score . ' - ' . $this->team2_score;
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->duration_minutes) return '-';
        
        $minutes = $this->duration_minutes;
        return "{$minutes}m";
    }

    public function isCompleted()
    {
        return $this->player1_score > 0 || $this->player2_score > 0;
    }

    public function getPointDifference()
    {
        return abs($this->player1_score - $this->player2_score);
    }

    public function recordPoint($pointForPlayer1 = true)
    {
        if ($pointForPlayer1) {
            $this->player1_score++;
        } else {
            $this->player2_score++;
        }

        // Update point sequence
        $sequence = $this->point_sequence ?? [];
        $sequence[] = [
            'player' => $pointForPlayer1 ? 'player1' : 'player2',
            'timestamp' => now()->toDateTimeString(),
            'score_after' => $this->player1_score . '-' . $this->player2_score
        ];

        $this->point_sequence = $sequence;
        $this->save();

        return $this;
    }

    public static function createSet($matchId, $setNumber, $scores = [])
    {
        return self::create([
            'match_id' => $matchId,
            'set_number' => $setNumber,
            'player1_score' => $scores['player1'] ?? 0,
            'player2_score' => $scores['player2'] ?? 0,
            'team1_score' => $scores['team1'] ?? 0,
            'team2_score' => $scores['team2'] ?? 0,
            'duration_minutes' => $scores['duration'] ?? null
        ]);
    }
}