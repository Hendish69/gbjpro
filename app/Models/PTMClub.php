<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PTMClub extends Model
{
    use HasFactory;

    // Tentukan nama tabel secara explicit
    protected $table = 'ptm_clubs';

    protected $fillable = [
        'name', 'code', 'city', 'province', 'address', 'lat','lon',
        'phone', 'email', 'website', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function setCityAttribute($value)
    {
        $this->attributes['city'] = strtoupper($value);
    }

    public function setProvinceAttribute($value)
    {
        $this->attributes['province'] = strtoupper($value);
    }
    // Relationships
    public function players()
    {
        return $this->hasMany(Player::class, 'ptm_club_id')->where('is_active', 1)->OrderBy('nickname','asc');
    }

    public function activePlayers()
    {
        return $this->hasMany(Player::class, 'ptm_club_id')->where('is_active', 1);
    }
    // Accessors
    public function getFullAddressAttribute()
    {
        $addressParts = array_filter([$this->address, $this->city, $this->province]);
        return implode(', ', $addressParts);
    }

    public function getDisplayNameAttribute()
    {
        return $this->code ? $this->code.' - ' .$this->name : $this->name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('code', 'ilike', "%{$search}%")
              ->orWhere('city', 'ilike', "%{$search}%");
        });
    }

    // Methods
    public function getPlayersCount()
    {
        return $this->players()->count();
    }

    public function getActivePlayersCount()
    {
        return $this->players()->active()->count();
    }
}