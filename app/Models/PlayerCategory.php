<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerCategory extends Model
{
    use HasFactory;

    // Jika nama tabel berbeda, definisikan
    protected $table = 'player_categories'; // Sesuai dengan nama di constraint FOREIGN KEY

    protected $fillable = [
        'name', 'description', 'min_rating', 'max_rating', 
        'min_age', 'max_age', 'gender', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_rating' => 'integer',
        'max_rating' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    /**
     * Relationship dengan players - SESUAIKAN DENGAN TABEL YANG ADA
     */
    public function players()
    {
        return $this->belongsToMany(Player::class, 'player_category', 'player_category_id', 'player_id')
                    ->withTimestamps();
    }

    /**
     * Scope untuk kategori aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor untuk range rating
     */
    public function getRatingRangeAttribute()
    {
        if ($this->min_rating && $this->max_rating) {
            return "{$this->min_rating} - {$this->max_rating}";
        }
        return 'N/A';
    }

    /**
     * Accessor untuk range age
     */
    public function getAgeRangeAttribute()
    {
        if ($this->min_age && $this->max_age) {
            return "{$this->min_age} - {$this->max_age} years";
        }
        return 'N/A';
    }
}