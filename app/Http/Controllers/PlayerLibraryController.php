<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\PlayerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlayerLibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = Player::inLibrary()->with('categories');

        // Search
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('player_category_id', $request->category_id);
            });
        }

        // Filter by rating
        if ($request->has('rating_min') && $request->rating_min) {
            $query->where('rating', '>=', $request->rating_min);
        }

        if ($request->has('rating_max') && $request->rating_max) {
            $query->where('rating', '<=', $request->rating_max);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $sort = $request->get('sort', 'name');
        $order = $request->get('order', 'asc');
        
        $query->orderBy($sort, $order);

        $players = $query->paginate(20);
        $categories = PlayerCategory::all();
        $totalPlayers = Player::inLibrary()->count();
        $activePlayers = Player::inLibrary()->active()->count();

        return view('players.library', compact(
            'players', 'categories', 'totalPlayers', 'activePlayers'
        ));
    }

    public function create()
    {
        $categories = PlayerCategory::all();
        return view('players.library.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:players,email',
            'phone' => 'nullable|string|max:20',
            'rating' => 'required|integer|min:0|max:3000',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'playing_style' => 'nullable|in:offensive,defensive,all_round',
            'grip_style' => 'nullable|in:shakehand,penhold',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:player_categories,id',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('players', 'public');
        }

        $validated['is_in_library'] = true;
        $validated['is_active'] = true;

        $player = Player::create($validated);

        // Attach categories
        if ($request->has('category_ids')) {
            $player->categories()->sync($request->category_ids);
        }

        return redirect()->route('players.library')
            ->with('success', 'Player added to library successfully.');
    }

    public function show(Player $player)
    {
        $player->load(['categories', 'tournaments' => function($query) {
            $query->orderBy('start_date', 'desc');
        }]);

        return view('players.show-library', compact('player'));
    }

    public function edit(Player $player)
    {
        $categories = PlayerCategory::all();
        $player->load('categories');
        
        return view('players.edit-library', compact('player', 'categories'));
    }

    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:players,email,' . $player->id,
            'phone' => 'nullable|string|max:20',
            'rating' => 'required|integer|min:0|max:3000',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'playing_style' => 'nullable|in:offensive,defensive,all_round',
            'grip_style' => 'nullable|in:shakehand,penhold',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:player_categories,id',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($player->photo) {
                Storage::disk('public')->delete($player->photo);
            }
            $validated['photo'] = $request->file('photo')->store('players', 'public');
        }

        // Handle photo removal
        if ($request->has('remove_photo')) {
            if ($player->photo) {
                Storage::disk('public')->delete($player->photo);
            }
            $validated['photo'] = null;
        }

        $player->update($validated);

        // Sync categories
        $player->categories()->sync($request->category_ids ?? []);

        return redirect()->route('players.library')
            ->with('success', 'Player updated successfully.');
    }

    public function destroy(Player $player)
    {
        // Soft delete - set tidak aktif dan tidak di library
        $player->update([
            'is_active' => false,
            'is_in_library' => false
        ]);

        return redirect()->route('players.library')
            ->with('success', 'Player removed from library successfully.');
    }

    public function bulkActions(Request $request)
    {
        $validated = $request->validate([
            'player_ids' => 'required|array',
            'player_ids.*' => 'exists:players,id',
            'action' => 'required|in:activate,deactivate,delete,add_category'
        ]);

        $players = Player::whereIn('id', $validated['player_ids'])->get();

        foreach ($players as $player) {
            switch ($validated['action']) {
                case 'activate':
                    $player->update(['is_active' => true]);
                    break;
                case 'deactivate':
                    $player->update(['is_active' => false]);
                    break;
                case 'delete':
                    $player->update(['is_in_library' => false, 'is_active' => false]);
                    break;
                case 'add_category':
                    if ($request->has('category_id')) {
                        $player->categories()->syncWithoutDetaching([$request->category_id]);
                    }
                    break;
            }
        }

        return redirect()->back()->with('success', 'Bulk action completed successfully.');
    }

    public function getPlayersJson(Request $request)
    {
        $players = Player::inLibrary()
            ->active()
            ->when($request->has('q'), function($query) use ($request) {
                return $query->search($request->q);
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'nickname', 'rating', 'photo']);

        return response()->json($players);
    }
}