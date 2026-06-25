<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('parent')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        $parentMenus = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return view('menus.index', compact('menus', 'parentMenus'));
    }

    public function create()
    {
        $parentMenus = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return view('menus.create', compact('parentMenus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:menus',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:menus,id',
            'sort_order' => 'required|integer|min:0',
        ]);

        Menu::create([
            'name' => $request->name,
            'route' => $request->route,
            'icon' => $request->icon,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu)
    {
        $parentMenus = Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $menu->id)
            ->orderBy('sort_order')
            ->get();
            
        return view('menus.edit', compact('menu', 'parentMenus'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:menus,name,' . $menu->id,
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:menus,id',
            'sort_order' => 'required|integer|min:0',
        ]);

        // Prevent circular reference
        if ($request->parent_id == $menu->id) {
            return back()->withErrors(['parent_id' => 'Cannot set itself as parent.']);
        }

        $menu->update([
            'name' => $request->name,
            'route' => $request->route,
            'icon' => $request->icon,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        // Check if menu has children
        if ($menu->children()->exists()) {
            return redirect()->route('menus.index')
                ->with('error', 'Cannot delete menu that has sub-menus. Please delete sub-menus first.');
        }

        // Check if menu has permissions
        if ($menu->permissions()->exists()) {
            return redirect()->route('menus.index')
                ->with('error', 'Cannot delete menu that has permissions assigned. Please remove permissions first.');
        }

        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'menus' => 'required|array',
        ]);

        foreach ($request->menus as $menuData) {
            Menu::where('id', $menuData['id'])->update([
                'sort_order' => $menuData['sort_order'],
                'parent_id' => $menuData['parent_id'] ?? null,
            ]);
        }

        return response()->json(['success' => 'Menu order updated successfully.']);
    }
}