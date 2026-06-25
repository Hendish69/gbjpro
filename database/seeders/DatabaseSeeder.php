<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Roles
        $adminRole = Role::create(['name' => 'Administrator', 'description' => 'Full access']);
        $managerRole = Role::create(['name' => 'Manager', 'description' => 'Manager access']);
        $userRole = Role::create(['name' => 'User', 'description' => 'Basic user']);

        // Create Menus
        $menus = [
            ['name' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fas fa-home', 'sort_order' => 1],
            ['name' => 'User Management', 'route' => 'users.index', 'icon' => 'fas fa-users', 'sort_order' => 2],
            ['name' => 'Role Management', 'route' => 'roles.index', 'icon' => 'fas fa-shield-alt', 'sort_order' => 3],
            ['name' => 'Menu Management', 'route' => 'menus.index', 'icon' => 'fas fa-bars', 'sort_order' => 4],
            ['name' => 'Reports', 'route' => 'reports', 'icon' => 'fas fa-chart-bar', 'sort_order' => 5],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }

        // Create Permissions for Admin (all permissions)
        foreach (Menu::all() as $menu) {
            Permission::create([
                'role_id' => $adminRole->id,
                'menu_id' => $menu->id,
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
            ]);
        }

        // Create Permissions for Manager
        $managerMenus = ['Dashboard', 'User Management', 'Reports'];
        foreach ($managerMenus as $menuName) {
            $menu = Menu::where('name', $menuName)->first();
            if ($menu) {
                Permission::create([
                    'role_id' => $managerRole->id,
                    'menu_id' => $menu->id,
                    'can_view' => true,
                    'can_create' => $menuName === 'User Management',
                    'can_edit' => $menuName === 'User Management',
                    'can_delete' => false,
                ]);
            }
        }

        // Create Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'hendi.sh@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        // Create Manager User
        User::create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role_id' => $managerRole->id,
        ]);

        // Create Regular User
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id,
        ]);
    }
}