<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentPlayer extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda
    protected $table = 'tournament_player';

    protected $fillable = [
        'tournament_id',
        'player_id', 
        'seed',
        'group',
        'representing_ptm_club_id',
        'is_representing_different_club',
        'representation_notes'
    ];

    protected $casts = [
        'is_representing_different_club' => 'boolean',
    ];

    // Relationships
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }
    
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function representingClub()
    {
        return $this->belongsTo(PtmClub::class, 'representing_ptm_club_id');
    }
}