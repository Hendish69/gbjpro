<?php

namespace App\Http\Controllers;

use App\Models\Matches;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\Table;
use App\Models\Set;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index()
    {
        $matches = Matches::with(['tournament', 'player1', 'player2', 'winner'])
            ->latest()
            ->get();
        // dd($matches);
        return view('matches.index', compact('matches'));
    }

    public function create()
    {
        $tournaments = Tournament::where('status', '!=', 'completed')->get();
        $players = Player::orderBy('name')->get();
        $tables = Table::available()->get(); // Gunakan scope available
        
        return view('matches.create', compact('tournaments', 'players', 'tables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
            'player1_id' => 'required|exists:players,id',
            'player2_id' => 'required|exists:players,id|different:player1_id',
            'match_date' => 'required|date',
            'round_number' => 'required|integer|min:1',
        ]);

        Matches::create($validated);

        return redirect()->route('matches.index')->with('success', 'Match created successfully.');
    }
    
    public function show(Matches $match)
    {
        $match->load(['tournament', 'player1', 'player2', 'winner', 'sets']);
        return view('matches.show', compact('match'));
    }

    public function edit(Matches $match)
    {
        $tournaments = Tournament::all();
        $players = Player::orderBy('name')->get();
        $tables = Table::all(); // Semua tables
        
        return view('matches.edit', compact('match', 'tournaments', 'players', 'tables'));
    }

    public function update(Request $request, Matches $match)
    {
        $validated = $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
            'player1_id' => 'required|exists:players,id',
            'player2_id' => 'required|exists:players,id|different:player1_id',
            'match_date' => 'required|date',
            'round_number' => 'required|integer|min:1',
            'status' => 'required|in:scheduled,live,completed',
        ]);

        $match->update($validated);

        return redirect()->route('matches.index')->with('success', 'Match updated successfully.');
    }

    public function destroy(Matches $match)
    {
        $match->delete();
        return redirect()->route('matches.index')->with('success', 'Match deleted successfully.');
    }

    public function recordScore(Request $request, Matches $match)
    {
        // Validasi
        $validated = $request->validate([
            'sets' => 'required|array|min:1',
            'sets.*.player1_score' => 'required|integer|min:0|max:11',
            'sets.*.player2_score' => 'required|integer|min:0|max:11',
        ]);

        // Pastikan match sedang berjalan
        if ($match->status !== 'live') {
            return redirect()->back()->with('error', 'Match is not live. Cannot record score.');
        }

        try {
            // Delete existing sets
            $match->sets()->delete();

            // Create new sets
            foreach ($validated['sets'] as $index => $setData) {
                Set::create([
                    'match_id' => $match->id,
                    'set_number' => $index + 1,
                    'player1_score' => $setData['player1_score'],
                    'player2_score' => $setData['player2_score'],
                ]);
            }

            // Calculate winner based on sets
            $match->determineWinnerFromSets();

            return redirect()->route('matches.show', $match)->with('success', 'Score recorded successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error recording score: ' . $e->getMessage());
        }
    }

    public function showRecordScoreForm(Matches $match)
    {
        if ($match->status !== 'live') {
            return redirect()->route('matches.show', $match)->with('error', 'Match is not live.');
        }

        return view('matches.record-score', compact('match'));
    }

    public function startMatch(Matches $match)
    {
        if (!$match->canStart()) {
            return redirect()->back()->with('error', 'Match cannot be started. Check if both players are assigned.');
        }

        $match->update(['status' => 'live']);

        // Jika ada table yang diassign, update status table
        if ($match->table_id) {
            $match->table->update(['status' => 'occupied']);
        }

        return redirect()->back()->with('success', 'Match started successfully!');
    }

    public function completeMatch(Matches $match)
    {
        if ($match->status !== 'live') {
            return redirect()->back()->with('error', 'Only live matches can be completed.');
        }

        $match->completeMatch();

        return redirect()->back()->with('success', 'Match completed successfully!');
    }
}