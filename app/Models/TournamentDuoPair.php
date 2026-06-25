<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentDuoPair extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'pair_name',
        'team_name',
        'player1_id',
        'player1_club_id',
        'player2_id',
        'player2_club_id',
        'status',
        'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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

    public function player1Club()
    {
        return $this->belongsTo(PtmClub::class, 'player1_club_id');
    }

    public function player2Club()
    {
        return $this->belongsTo(PtmClub::class, 'player2_club_id');
    }

    // Accessors
    public function getPairDisplayNameAttribute()
    {
        if ($this->pair_name) {
            return $this->pair_name;
        }
        
        return $this->player1->name . ' & ' . $this->player2->name;
    }

    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }
}