<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionRankingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id', 'old_ranking', 'new_ranking', 'reason', 'notes'
    ];

    protected $casts = [
        'old_ranking' => 'integer',
        'new_ranking' => 'integer'
    ];

    // Relationships
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    // Accessors
    public function getRankingChangeAttribute()
    {
        if ($this->old_ranking && $this->new_ranking) {
            return $this->new_ranking - $this->old_ranking;
        }
        return 0;
    }

    public function getChangeTypeAttribute()
    {
        if (!$this->old_ranking || !$this->new_ranking) {
            return 'initial';
        }

        $change = $this->ranking_change;
        
        if ($change < 0) {
            return 'promotion'; // Ranking lebih kecil = lebih baik
        } elseif ($change > 0) {
            return 'demotion'; // Ranking lebih besar = lebih buruk
        } else {
            return 'no_change';
        }
    }

    public function getChangeDescriptionAttribute()
    {
        switch ($this->change_type) {
            case 'promotion':
                return 'Promosi ke divisi ' . $this->new_ranking;
            case 'demotion':
                return 'Degradasi ke divisi ' . $this->new_ranking;
            case 'initial':
                return 'Penetapan divisi ' . $this->new_ranking;
            default:
                return 'Tetap di divisi ' . $this->new_ranking;
        }
    }

    public function getOldRankingNameAttribute()
    {
        return $this->old_ranking ? 'Divisi ' . $this->old_ranking : 'Belum ada';
    }

    public function getNewRankingNameAttribute()
    {
        return $this->new_ranking ? 'Divisi ' . $this->new_ranking : 'Belum ada';
    }
}