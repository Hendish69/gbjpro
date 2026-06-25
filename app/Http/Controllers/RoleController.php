<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Menu;
use App\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions.menu'])->get();
        return view('roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $menus = Menu::where('is_active', true)->orderBy('sort_order')->get();
        $permissions = $role->permissions->keyBy('menu_id');
        return view('roles.edit', compact('role', 'menus', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Update permissions
        if ($request->has('permissions')) {
            foreach ($request->permissions as $menuId => $actions) {
                $permission = Permission::where('role_id', $role->id)
                    ->where('menu_id', $menuId)
                    ->first();

                if ($permission) {
                    $permission->update([
                        'can_view' => in_array('view', $actions),
                        'can_create' => in_array('create', $actions),
                        'can_edit' => in_array('edit', $actions),
                        'can_delete' => in_array('delete', $actions),
                    ]);
                } else {
                    Permission::create([
                        'role_id' => $role->id,
                        'menu_id' => $menuId,
                        'can_view' => in_array('view', $actions),
                        'can_create' => in_array('create', $actions),
                        'can_edit' => in_array('edit', $actions),
                        'can_delete' => in_array('delete', $actions),
                    ]);
                }
            }
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }
}