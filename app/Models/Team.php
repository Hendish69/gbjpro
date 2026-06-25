<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'captain_id',
        'club_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function captain()
    {
        return $this->belongsTo(Player::class, 'captain_id');
    }

    public function club()
    {
        return $this->belongsTo(PTMClub::class, 'club_id');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'team_player')
            ->withPivot(['role', 'position', 'joined_at'])
            ->withTimestamps();
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_team')
            ->withPivot(['seed', 'group', 'status'])
            ->withTimestamps();
    }

    public function activeTournaments()
    {
        return $this->tournaments()->whereIn('status', ['registration_open', 'ongoing']);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByClub($query, $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('code', 'ilike', "%{$search}%")
              ->orWhereHas('club', function($clubQuery) use ($search) {
                  $clubQuery->where('name', 'ilike', "%{$search}%")
                           ->orWhere('code', 'ilike', "%{$search}%");
              });
        });
    }

    public function scopeAvailableForTournament($query, Tournament $tournament)
    {
        return $query->whereDoesntHave('tournaments', function($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->active();
    }

    // Accessors
    public function getMemberCountAttribute()
    {
        return $this->players()->count();
    }

    public function getActiveMemberCountAttribute()
    {
        return $this->players()->where('is_active', true)->count();
    }

    public function getDisplayNameAttribute()
    {
        return $this->code ? "{$this->code} - {$this->name}" : $this->name;
    }

    public function getCaptainNameAttribute()
    {
        return $this->captain ? $this->captain->name : 'Belum ada kapten';
    }

    public function getClubNameAttribute()
    {
        return $this->club ? $this->club->name : 'Tidak ada klub';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-danger">Non-Aktif</span>';
    }

    // Methods
    public function addPlayer(Player $player, $role = 'member', $position = null)
    {
        // Cek apakah player sudah ada di tim
        if ($this->players()->where('player_id', $player->id)->exists()) {
            return false;
        }

        return $this->players()->attach($player->id, [
            'role' => $role,
            'position' => $position,
            'joined_at' => now()
        ]);
    }

    public function updatePlayerRole(Player $player, $role, $position = null)
    {
        return $this->players()->updateExistingPivot($player->id, [
            'role' => $role,
            'position' => $position
        ]);
    }

    public function removePlayer(Player $player)
    {
        return $this->players()->detach($player->id);
    }

    public function setCaptain(Player $player)
    {
        // Pastikan player adalah anggota tim
        if (!$this->players()->where('player_id', $player->id)->exists()) {
            return false;
        }

        // Update role player menjadi captain
        $this->updatePlayerRole($player, 'captain', 1);

        // Update captain_id di team
        return $this->update(['captain_id' => $player->id]);
    }

    public function getPlayersByRole($role = null)
    {
        $query = $this->players();
        
        if ($role) {
            $query->wherePivot('role', $role);
        }

        return $query->orderBy('team_player.position')->get();
    }

    public function getCaptain()
    {
        return $this->players()->wherePivot('role', 'captain')->first();
    }

    public function getViceCaptains()
    {
        return $this->players()->wherePivot('role', 'vice_captain')->get();
    }

    public function getRegularMembers()
    {
        return $this->players()->wherePivot('role', 'member')->get();
    }

    public function activate()
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        return $this->update(['is_active' => false]);
    }

    public function canJoinTournament(Tournament $tournament)
    {
        // Cek apakah tim sudah terdaftar di tournament
        if ($this->tournaments()->where('tournament_id', $tournament->id)->exists()) {
            return false;
        }

        // Cek apakah tim aktif dan memiliki cukup anggota
        if (!$this->is_active || $this->member_count < 2) {
            return false;
        }

        // Untuk tournament team, cek requirements khusus
        if ($tournament->type === 'team') {
            return $this->meetsTeamTournamentRequirements($tournament);
        }

        return true;
    }

    private function meetsTeamTournamentRequirements(Tournament $tournament)
    {
        // Implementasi requirements khusus untuk tournament team
        // Misalnya: minimal jumlah anggota, divisi tertentu, dll.
        $minPlayers = $tournament->settings['min_players_per_team'] ?? 2;
        
        return $this->active_member_count >= $minPlayers;
    }

    public function joinTournament(Tournament $tournament, $seed = null, $group = null)
    {
        if (!$this->canJoinTournament($tournament)) {
            return false;
        }

        return $this->tournaments()->attach($tournament->id, [
            'seed' => $seed,
            'group' => $group,
            'status' => 'registered'
        ]);
    }

    public function withdrawFromTournament(Tournament $tournament)
    {
        return $this->tournaments()->detach($tournament->id);
    }

    public static function createTeam($data, $captain = null, $initialPlayers = [])
    {
        $team = self::create($data);

        if ($captain) {
            $team->setCaptain($captain);
        }

        // Tambahkan initial players
        foreach ($initialPlayers as $player) {
            $team->addPlayer($player);
        }

        return $team;
    }

    public function getTournamentHistory()
    {
        return $this->tournaments()
            ->withPivot('status')
            ->orderBy('tournaments.start_date', 'desc')
            ->get();
    }

    public function getPerformanceStats()
    {
        $completedTournaments = $this->tournaments()
            ->where('status', 'completed')
            ->count();

        $ongoingTournaments = $this->tournaments()
            ->where('status', 'ongoing')
            ->count();

        return [
            'total_tournaments' => $this->tournaments()->count(),
            'completed_tournaments' => $completedTournaments,
            'ongoing_tournaments' => $ongoingTournaments,
            'member_count' => $this->member_count,
            'active_since' => $this->created_at->format('M Y'),
        ];
    }
}