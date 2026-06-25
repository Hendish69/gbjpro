<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'level', 'min_points', 'max_points', 'color', 
        'description', 'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function promotionHistories()
    {
        return $this->hasMany(DivisionHistory::class, 'new_division_id');
    }

    public function demotionHistories()
    {
        return $this->hasMany(DivisionHistory::class, 'old_division_id');
    }

    // Accessors
    public function getPointsRangeAttribute()
    {
        if ($this->max_points) {
            return $this->min_points . ' - ' . $this->max_points;
        }
        return $this->min_points . '+';
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->level . ')';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('min_points', 'desc');
    }

    // Methods
    public function getDivisionForPoints($points)
    {
        return static::active()
            ->where('min_points', '<=', $points)
            ->when($this->max_points, function($query) use ($points) {
                return $query->where('max_points', '>=', $points);
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }

    public static function getDefaultDivision()
    {
        return static::active()->ordered()->first();
    }
}