<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id', 'old_division_id', 'new_division_id',
        'old_points', 'new_points', 'reason', 'notes'
    ];

    protected $casts = [
        'old_points' => 'integer',
        'new_points' => 'integer'
    ];

    // Relationships
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function oldDivision()
    {
        return $this->belongsTo(Division::class, 'old_division_id');
    }

    public function newDivision()
    {
        return $this->belongsTo(Division::class, 'new_division_id');
    }

    // Accessors
    public function getPointsChangeAttribute()
    {
        return $this->new_points - $this->old_points;
    }

    public function getChangeTypeAttribute()
    {
        if ($this->old_division_id === $this->new_division_id) {
            return 'points_change';
        }
        
        $oldDivision = $this->oldDivision;
        $newDivision = $this->newDivision;
        
        if ($oldDivision && $newDivision) {
            if ($newDivision->order > $oldDivision->order) {
                return 'promotion';
            } else {
                return 'demotion';
            }
        }
        
        return 'unknown';
    }

    public function getChangeDescriptionAttribute()
    {
        switch ($this->change_type) {
            case 'promotion':
                return 'Promosi ke ' . $this->newDivision->name;
            case 'demotion':
                return 'Degradasi ke ' . $this->newDivision->name;
            case 'points_change':
                $change = $this->points_change;
                return ($change >= 0 ? '+' : '') . $change . ' points';
            default:
                return 'Perubahan divisi';
        }
    }
}