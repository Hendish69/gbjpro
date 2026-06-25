<?php

namespace App\Http\Controllers;

use App\Models\Player;
// use App\Models\PlayerCategory;
use App\Models\PTMClub;
use Illuminate\Http\Request; // Pastikan ini di-import
use Illuminate\Support\Facades\Storage;

class PlayerController extends Controller
{
    public function index(Request $request) // Tambah Request $request di parameter
    {
        $players = Player::with(['ptmClub'])
            ->when($request->has('search') && $request->search, function($query) use ($request) {
                return $query->search($request->search);
            })
            ->where('is_active', true)
            ->orderBy('nickname')
            ->paginate(10)->withQueryString()
            ->appends(['search' => $request->search]);
            // dd($players);
        return view('players.index', compact('players'));
    }
    public function search(Request $request)
    {
        $players = Player::with(['ptmClub'])
            ->when($request->has('search') && $request->search, function($query) use ($request) {
                return $query->search($request->search);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(10)
            ->appends(['search' => $request->search]);

        return view('players.partials.table', compact('players'))->render();
    }
    public function create(Request $request)
    {
        $ptmClubs = PTMClub::active()->orderBy('name')->get();
        // $categories = PlayerCategory::all();
        $selectedClubId = $request->get('ptm_club_id');
        // return view('players.create', compact('ptmClubs', 'categories', 'selectedClubId'));
        return view('players.create', compact('ptmClubs', 'selectedClubId'));
    }
    public function edit(Player $player)
    {
        $ptmClubs = PTMClub::active()->orderBy('name')->get();
        // $categories = PlayerCategory::all();
        // $player->load('categories');
        
        // return view('players.edit', compact('player', 'ptmClubs', 'categories'));
        return view('players.edit', compact('player', 'ptmClubs'));
    }
    public function store(Request $request) // Pastikan ada Request $request
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'ptm_number' => 'nullable|string|max:50|unique:players,ptm_number',
            'ptm_club_id' => 'nullable|exists:ptm_clubs,id',
            'division_ranking' => 'required|integer|min:1|max:11',
            'email' => 'required|email|unique:players,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'playing_style' => 'nullable|in:offensive,defensive,all_round',
            'grip_style' => 'nullable|in:shakehand,penhold',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'category_ids' => 'nullable|array',
            // 'category_ids.*' => 'exists:player_categories,id',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
           
            $file = $request->file('photo');
            // Buat nama unik
            $filename = time().'_'.$file->getClientOriginalName();
            // Simpan ke folder public/images
            $file->move(public_path('images/players'), $filename);
            $validated['photo'] = 'players/'. $filename;
        }

        $validated['is_in_library'] = true;
        $validated['is_active'] = true;
        // dd($validated);
         $duplicate = Player::where('name', $validated['name'])
        ->where('ptm_club_id', $validated['ptm_club_id'])
        ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['name' => 'A player with this name and club already exists.'])
                ->withInput();
        }

        $player = Player::create($validated);

        // // Attach categories
        // if ($request->has('category_ids')) {
        //     $player->categories()->sync($request->category_ids);
        // }
        $redirectUrl = $request->input('redirect_to', route('players.index'));
        return redirect($redirectUrl)->with('success', 'Player created successfully.');
        // return redirect()->route('players.index')->with('success', 'Player created successfully.');
    }
    public function checkDuplicate(Request $request)
    {
        $name = trim($request->get('name'));
        $clubId = $request->get('ptm_club_id');

        // Validasi input dasar
        if (!$name || !$clubId) {
            return response()->json([
                'exists' => false,
                'message' => '⚠️ Please enter both name and club before checking for duplicates.'
            ]);
        }
      
        // Cek apakah player sudah ada dengan kombinasi nama dan club
        $exists = Player::where('name', $name)
            ->where('ptm_club_id', $clubId)
            ->exists();
        // dd($name,$clubId,$exists);
        return response()->json([
            'exists' => $exists,
            'message' => $exists 
                ? '⚠️ A player with this name and club already exists.' 
                : '✅ No duplicate found, safe to save.'
        ]);
    }
    public function show(Player $player)
    {
        $player->load([
            'matchesAsPlayer1.tournament', 
            'matchesAsPlayer1.player2',
            'matchesAsPlayer2.tournament', 
            'matchesAsPlayer2.player1',
            'wonMatches',
            'ptmClub',
            // 'categories',
            'divisionRankingHistories'
        ]);

        return view('players.show', compact('player'));
    }

    

    public function update(Request $request, Player $player) // Pastikan ada Request $request
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'ptm_number' => 'nullable|string|max:50|unique:players,ptm_number,' . $player->id,
            'ptm_club_id' => 'nullable|exists:ptm_clubs,id',
            'division_ranking' => 'required|integer|min:1|max:11',
            'email' => 'required|email|unique:players,email,' . $player->id,
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'playing_style' => 'nullable|in:offensive,defensive,all_round',
            'grip_style' => 'nullable|in:shakehand,penhold',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'category_ids' => 'nullable|array',
            // 'category_ids.*' => 'exists:player_categories,id',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($player->photo) {
                Storage::disk('public')->delete($player->photo);
            }
            $validated['photo'] = $request->file('photo');
            $file = $request->file('photo');
            // Buat nama unik
            $filename = time().'_'.$file->getClientOriginalName();
            // Simpan ke folder public/images
            $file->move(public_path('images/players'), $filename);
            $validated['photo'] = 'players/'. $filename;
           
        }

        // Handle photo removal
        if ($request->has('remove_photo')) {
            if ($player->photo) {
                Storage::disk('public')->delete($player->photo);
            }
            $validated['photo'] = null;
        }
        // dd($validated);
        $player->update($validated);

        // Sync categories
        // $player->categories()->sync($request->category_ids ?? []);

        return redirect()->route('players.index')->with('success', 'Player updated successfully.');
    }

    public function destroy(Player $player)
    {
        // Soft delete - set tidak aktif dan tidak di library
        if ($player->photo) {
            Storage::disk('public')->delete($player->photo);
        }
        
        $player->update([
            'is_active' => false,
            'is_in_library' => false
        ]);

        return redirect()->route('players.index')->with('success', 'Player deleted successfully.');
    }
}