<?php

namespace App\Http\Controllers;

use App\Models\Matches;
use App\Models\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetController extends Controller
{
    /**
     * Store a new set for a match
     */
    public function store(Request $request, Matches $match)
    {
        $validated = $request->validate([
            'set_number' => 'required|integer|min:1',
            'player1_score' => 'required|integer',
            'player2_score' => 'required|integer',
            'winner' => 'required|in:player1,player2'
        ]);

        try {
            DB::beginTransaction();

            $set = $match->sets()->create($validated);

            // Update match scores based on sets
            $match->determineWinnerFromSets();

            DB::commit();

            return back()->with('success', 'Set berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan set: ' . $e->getMessage());
        }
    }

    /**
     * Update a set
     */
    public function update(Request $request, Set $set)
    {
        $validated = $request->validate([
            'player1_score' => 'required|integer',
            'player2_score' => 'required|integer',
            'winner' => 'required|in:player1,player2'
        ]);

        try {
            DB::beginTransaction();

            $set->update($validated);

            // Update match scores based on sets
            $set->match->determineWinnerFromSets();

            DB::commit();

            return back()->with('success', 'Set berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui set: ' . $e->getMessage());
        }
    }

    /**
     * Delete a set
     */
    public function destroy(Set $set)
    {
        try {
            DB::beginTransaction();

            $match = $set->match;
            $set->delete();

            // Update match scores based on remaining sets
            $match->determineWinnerFromSets();

            DB::commit();

            return back()->with('success', 'Set berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus set: ' . $e->getMessage());
        }
    }
}