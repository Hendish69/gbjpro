<?php

namespace App\Http\Controllers;

use App\Models\PTMClub;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PTMClubController extends Controller
{
    public function index(Request $request)
    {
       
        $query = PTMClub::withCount(['players','players as active_players_count' => function($query) {
            $query->active();
        }]);

        // Search
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        // dd($query,'$query');
        // Sort
        $sort = $request->get('sort', 'name');
        $order = $request->get('order', 'asc');
        
        $clubs = $query->orderBy($sort, $order)->paginate(15)->withQueryString();
        // dd( $clubs);
        $totalClubs = PTMClub::count();
        $activeClubs = PTMClub::active()->count();
        $totalPlayers = Player::count();
        return view('ptm-clubs.index', compact(
            'clubs', 'totalClubs', 'activeClubs', 'totalPlayers'
        ));
    }

    public function create()
    {
        return view('ptm-clubs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:ptm_clubs,code',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'lat' => 'nullable|decimal',
            'lon' => 'nullable|decimal',
        ]);

        $validated['is_active'] = $request->has('is_active');

        PTMClub::create($validated);

        return redirect()->route('ptm-clubs.index')
            ->with('success', 'PTM Club berhasil dibuat.');
    }

    public function show(PTMClub $ptmClub)
    {
        $ptmClub->load(['players' => function($query) {
            $query->withCount(['matchesAsPlayer1', 'matchesAsPlayer2', 'wonMatches'])
            ->orderBy('division_ranking')
            ->orderBy('name');
        }]);
       

        // Statistics
        $stats = [
            'total_players' => $ptmClub->players->count(),
            'active_players' => $ptmClub->players->where('is_active', true)->count(),
            'average_division' => $ptmClub->players->avg('division_ranking'),
            'top_division' => $ptmClub->players->min('division_ranking'),
        ];

        // Division distribution
        $divisionDistribution = $ptmClub->players
            ->groupBy('division_ranking')
            ->map(function($players, $division) use ($ptmClub) {
                return [
                    'division' => $division,
                    'count' => $players->count(),
                    'percentage' => round(($players->count() / $ptmClub->players->count()) * 100, 1)
                ];
            })
            ->sortBy('division')
            ->values();
        // dd($ptmClub);
        return view('ptm-clubs.show', compact('ptmClub', 'stats', 'divisionDistribution'));
    }

    public function edit(PTMClub $ptmClub)
    {
        // dd($ptmClub);
        return view('ptm-clubs.edit', compact('ptmClub'));
    }

    public function update(Request $request, PTMClub $ptmClub)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:ptm_clubs,code,' . $ptmClub->id,
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $ptmClub->update($validated);

        return redirect()->route('ptm-clubs.show', $ptmClub)
            ->with('success', 'PTM Club berhasil diperbarui.');
    }

    public function destroy(PTMClub $ptmClub)
    {
        // Cek apakah club memiliki players
        if ($ptmClub->players()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus club yang masih memiliki players. Pindahkan players terlebih dahulu.');
        }

        $ptmClub->delete();

        return redirect()->route('ptm-clubs.index')
            ->with('success', 'PTM Club berhasil dihapus.');
    }

    public function bulkActions(Request $request)
    {
        $validated = $request->validate([
            'club_ids' => 'required|array',
            'club_ids.*' => 'exists:ptm_clubs,id',
            'action' => 'required|in:activate,deactivate,delete'
        ]);

        $clubs = PTMClub::whereIn('id', $validated['club_ids'])->get();

        foreach ($clubs as $club) {
            switch ($validated['action']) {
                case 'activate':
                    $club->update(['is_active' => true]);
                    break;
                case 'deactivate':
                    $club->update(['is_active' => false]);
                    break;
                case 'delete':
                    // Only delete if no players
                    if ($club->players()->count() === 0) {
                        $club->delete();
                    }
                    break;
            }
        }

        return redirect()->back()->with('success', 'Bulk action completed successfully.');
    }
}