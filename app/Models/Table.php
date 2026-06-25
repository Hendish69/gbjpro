<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'location', 'description', 'status',
        'total_usage_minutes', 'total_matches_played',
        'last_maintenance_date', 'maintenance_notes'
    ];

    protected $casts = [
        'last_maintenance_date' => 'datetime'
    ];

    // Relationships
    public function matches()
    {
        return $this->hasMany(Matches::class);
    }

    // Accessors
    public function getCurrentMatchAttribute()
    {
        return $this->matches()
            ->where('status', 'ongoing')
            ->with(['player1', 'player2', 'tournament'])
            ->first();
    }

    public function getTotalUsageMinutesAttribute()
    {
        return $this->matches()
            ->where('status', 'completed')
            ->sum('duration_minutes');
    }

    public function getTodayUsageMinutesAttribute()
    {
        return $this->matches()
            ->where('status', 'completed')
            ->whereDate('match_date', today())
            ->sum('duration_minutes');
    }

    public function getUsageStatisticsAttribute()
    {
        $totalMatches = $this->matches()->where('status', 'completed')->count();
        $totalMinutes = $this->total_usage_minutes;
        
        return [
            'total_matches' => $totalMatches,
            'total_minutes' => $totalMinutes,
            'average_minutes_per_match' => $totalMatches > 0 ? round($totalMinutes / $totalMatches, 1) : 0,
            'today_minutes' => $this->today_usage_minutes,
            'today_matches' => $this->matches()->where('status', 'completed')->whereDate('match_date', today())->count()
        ];
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeInMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    // Methods
    public function markAsMaintenance($notes = null)
    {
        return $this->update([
            'status' => 'maintenance',
            'maintenance_notes' => $notes,
            'last_maintenance_date' => now()
        ]);
    }

    public function markAsAvailable()
    {
        return $this->update(['status' => 'available']);
    }

    public function markAsOccupied()
    {
        return $this->update(['status' => 'occupied']);
    }

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    public function isInMaintenance()
    {
        return $this->status === 'maintenance';
    }

    public function getScheduledMatches($date = null)
    {
        $query = $this->matches()->where('status', 'scheduled');
        
        if ($date) {
            $query->whereDate('match_date', $date);
        }

        return $query->orderBy('match_date')->get();
    }

    public function updateUsageStatistics()
    {
        $totalMinutes = $this->matches()->where('status', 'completed')->sum('duration_minutes');
        $totalMatches = $this->matches()->where('status', 'completed')->count();
        
        $this->update([
            'total_usage_minutes' => $totalMinutes,
            'total_matches_played' => $totalMatches
        ]);
    }

    public static function getAvailableTable()
    {
        return self::available()->first();
    }

    public static function getTablesByStatus()
    {
        return [
            'available' => self::available()->count(),
            'occupied' => self::occupied()->count(),
            'maintenance' => self::inMaintenance()->count()
        ];
    }
}