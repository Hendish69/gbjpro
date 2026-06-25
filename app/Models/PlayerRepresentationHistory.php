<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerRepresentationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id', 'tournament_id', 'original_ptm_club_id', 
        'representing_ptm_club_id', 'reason', 'notes'
    ];

    // Relationships
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function originalClub()
    {
        return $this->belongsTo(PTMClub::class, 'original_ptm_club_id');
    }

    public function representingClub()
    {
        return $this->belongsTo(PTMClub::class, 'representing_ptm_club_id');
    }

    // Accessors
    public function getRepresentationTypeAttribute()
    {
        if (!$this->representing_ptm_club_id) {
            return 'own_club';
        }

        if ($this->original_ptm_club_id == $this->representing_ptm_club_id) {
            return 'own_club';
        }

        return 'different_club';
    }

    public function getRepresentationDescriptionAttribute()
    {
        switch ($this->representation_type) {
            case 'own_club':
                return 'Mewakili klub sendiri';
            case 'different_club':
                return 'Mewakili klub lain';
            default:
                return 'Tidak diketahui';
        }
    }

    public function getIsRepresentingDifferentClubAttribute()
    {
        return $this->representation_type === 'different_club';
    }
}