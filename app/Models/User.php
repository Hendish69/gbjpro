<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'is_active'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission($menuName, $action = 'view')
    {
        // Cek jika user memiliki role
        if (!$this->role) {
            return false;
        }

        $permission = $this->role->permissions()
            ->whereHas('menu', function($query) use ($menuName) {
                $query->where('name', $menuName);
            })
            ->first();

        if (!$permission) {
            return false;
        }

        return $permission->{"can_$action"} ?? false;
    }

    public function getMenuPermissions()
    {
        if (!$this->role) {
            return collect();
        }

        return $this->role->permissions()
            ->with('menu')
            ->whereHas('menu', function($query) {
                $query->where('is_active', true);
            })
            ->get();
    }
}