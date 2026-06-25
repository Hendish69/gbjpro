<?php

namespace App\Http\Controllers;

use App\Models\Matches;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\Team;
use App\Models\Table;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Matches::with([
            'tournament', 
            'player1', 
            'player2', 
            'team1', 
            'team2',
            'table'
        ]);

        // Filter berdasarkan tournament
        if ($request->has('tournament_id') && $request->tournament_id) {
            $query->where('tournament_id', $request->tournament_id);
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan round
        if ($request->has('round_number') && $request->round_number) {
            $query->where('round_number', $request->round_number);
        }
        $matches = $query->orderBy('match_date', 'desc')->paginate(20);
        $tournaments = Tournament::all();

        return view('matches.index', compact('matches','tournaments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tournaments = Tournament::all();
        $players = Player::all();
        $teams = Team::all();
        $tables = Table::where('status', 'available')->get();

        return view('matches.create', compact('tournaments', 'players', 'teams', 'tables'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
            'match_type' => 'required|in:single,double,team',
            'match_format' => 'required|in:Elimination,Group,Leagues',
            'player1_id' => 'required_if:match_type,single,double',
            'player2_id' => 'required_if:match_type,single,double',
            'player1_partner_id' => 'nullable|required_if:match_type,double',
            'player2_partner_id' => 'nullable|required_if:match_type,double',
            'team1_id' => 'required_if:match_type,team',
            'team2_id' => 'required_if:match_type,team',
            'bracket_type' => 'nullable|in:winner,loser,qualification',
            'round_number' => 'required|integer',
            'match_number' => 'required|integer',
            'group_name' => 'nullable|string',
            'match_date' => 'required|date',
            'table_id' => 'nullable|exists:tables,id',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
            'notes' => 'nullable|string',
            'next_match_id' => 'nullable|exists:matches,id',
            'next_match_position' => 'nullable|in:player1,player2'
        ]);

        try {
            DB::beginTransaction();

            $match = Matches::create($validated);

            // Jika ada table_id, update status table
            if ($match->table_id) {
                $table = Table::find($match->table_id);
                if ($table) {
                    $table->update(['status' => 'occupied']);
                }
            }

            DB::commit();

            return redirect()->route('matches.show', $match->id)
                ->with('success', 'Match berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat match: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Matches $match)
    {
        $match->load([
            'tournament',
            'player1', 
            'player2', 
            'player1Partner',
            'player2Partner',
            'team1',
            'team2',
            'table',
            'sets',
            'nextMatch'
        ]);

        return view('matches.show', compact('match'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Matches $match)
    {
        $tournaments = Tournament::all();
        $players = Player::all();
        $teams = Team::all();
        $tables = Table::all();
        $nextMatches = Matches::where('id', '!=', $match->id)->get();

        $match->load(['sets']);

        return view('matches.edit', compact('match', 'tournaments', 'players', 'teams', 'tables', 'nextMatches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Matches $match)
    {
        $validated = $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
            'match_type' => 'required|in:single,double,team',
            'match_format' => 'required|in:Elimination,Group,Leagues',
            'player1_id' => 'required_if:match_type,single,double',
            'player2_id' => 'required_if:match_type,single,double',
            'player1_partner_id' => 'nullable|required_if:match_type,double',
            'player2_partner_id' => 'nullable|required_if:match_type,double',
            'team1_id' => 'required_if:match_type,team',
            'team2_id' => 'required_if:match_type,team',
            'bracket_type' => 'nullable|in:winner,loser,qualification',
            'round_number' => 'required|integer',
            'match_number' => 'required|integer',
            'group_name' => 'nullable|string',
            'match_date' => 'required|date',
            'table_id' => 'nullable|exists:tables,id',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
            'notes' => 'nullable|string',
            'next_match_id' => 'nullable|exists:matches,id',
            'next_match_position' => 'nullable|in:player1,player2'
        ]);

        try {
            DB::beginTransaction();

            $oldTableId = $match->table_id;
            $newTableId = $validated['table_id'] ?? null;

            $match->update($validated);

            // Handle table assignment changes
            if ($oldTableId != $newTableId) {
                // Free old table
                if ($oldTableId) {
                    $oldTable = Table::find($oldTableId);
                    if ($oldTable) {
                        $oldTable->update(['status' => 'available']);
                    }
                }

                // Occupy new table
                if ($newTableId) {
                    $newTable = Table::find($newTableId);
                    if ($newTable) {
                        $newTable->update(['status' => 'occupied']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('matches.show', $match->id)
                ->with('success', 'Match berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui match: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Matches $match)
    {
        try {
            DB::beginTransaction();

            // Free table if assigned
            if ($match->table_id) {
                $table = Table::find($match->table_id);
                if ($table) {
                    $table->update(['status' => 'available']);
                }
            }

            // Delete related sets
            $match->sets()->delete();

            $match->delete();

            DB::commit();

            return redirect()->route('matches.index')
                ->with('success', 'Match berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus match: ' . $e->getMessage());
        }
    }

    /**
     * Start a match
     */
    public function startMatch(Matches $match)
    {
        if (!$match->canStart()) {
            return back()->with('error', 'Match tidak dapat dimulai. Pastikan kedua peserta sudah ditentukan.');
        }

        try {
            $match->update([
                'status' => 'ongoing',
                'started_at' => now()
            ]);

            return back()->with('success', 'Match berhasil dimulai.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memulai match: ' . $e->getMessage());
        }
    }

    /**
     * Record score for a match
     */
    public function recordScore(Request $request, Matches $match)
    {
        $validated = $request->validate([
            'player1_score' => 'required_if:match_type,single,double|integer',
            'player2_score' => 'required_if:match_type,single,double|integer',
            'team1_score' => 'required_if:match_type,team|integer',
            'team2_score' => 'required_if:match_type,team|integer',
            'set_scores' => 'nullable|array',
            'duration_minutes' => 'nullable|integer'
        ]);

        try {
            DB::beginTransaction();

            $score1 = $match->match_type === 'team' ? $validated['team1_score'] : $validated['player1_score'];
            $score2 = $match->match_type === 'team' ? $validated['team2_score'] : $validated['player2_score'];

            $match->recordScore($score1, $score2, $validated['set_scores'] ?? null);

            if (isset($validated['duration_minutes'])) {
                $match->update(['duration_minutes' => $validated['duration_minutes']]);
            }

            // Complete the match
            $match->completeMatch();

            DB::commit();

            return redirect()->route('matches.show', $match->id)
                ->with('success', 'Score berhasil direkam dan match diselesaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal merekam score: ' . $e->getMessage());
        }
    }

    /**
     * Assign table to match
     */
    public function assignTable(Request $request, Matches $match)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id'
        ]);

        try {
            $match->assignTable($validated['table_id']);

            return back()->with('success', 'Meja berhasil ditugaskan ke match.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menugaskan meja: ' . $e->getMessage());
        }
    }

    /**
     * Show score recording form
     */
    public function showScoreForm(Matches $match)
    {
        $match->load(['player1', 'player2', 'team1', 'team2', 'sets']);
        
        return view('matches.record-score', compact('match'));
    }

    /**
     * Cancel a match
     */
    public function cancelMatch(Matches $match)
    {
        try {
            DB::beginTransaction();

            $match->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            // Free table if assigned
            if ($match->table_id) {
                $table = Table::find($match->table_id);
                if ($table) {
                    $table->update(['status' => 'available']);
                }
            }

            DB::commit();

            return back()->with('success', 'Match berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan match: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint for matches by tournament
     */
    public function byTournament(Tournament $tournament)
    {
        $matches = Matches::with(['player1', 'player2', 'team1', 'team2', 'table'])
            ->where('tournament_id', $tournament->id)
            ->orderBy('round_number')
            ->orderBy('match_number')
            ->get();

        return response()->json($matches);
    }

    /**
     * Update match status
     */
    public function updateStatus(Request $request, Matches $match)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,ongoing,completed,cancelled'
        ]);

        try {
            $match->update($validated);

            // Handle table status based on match status
            if ($match->table_id) {
                $table = Table::find($match->table_id);
                if ($table) {
                    if (in_array($validated['status'], ['completed', 'cancelled'])) {
                        $table->update(['status' => 'available']);
                    } elseif ($validated['status'] === 'ongoing') {
                        $table->update(['status' => 'occupied']);
                    }
                }
            }

            return back()->with('success', 'Status match berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}