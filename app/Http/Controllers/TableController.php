<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::with(['matches' => function($query) {
            $query->where('status', 'live')->with(['player1', 'player2']);
        }])->get();

        return view('tables.index', compact('tables'));
    }

    public function create()
    {
        return view('tables.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance'
        ]);

        Table::create($validated);

        return redirect()->route('tables.index')->with('success', 'Table created successfully.');
    }

    public function show(Table $table)
    {
        $table->load(['matches' => function($query) {
            $query->with(['player1', 'player2', 'tournament'])->latest();
        }]);

        $todayUsage = $table->today_usage_minutes;
        $totalUsage = $table->total_usage_minutes;

        return view('tables.show', compact('table', 'todayUsage', 'totalUsage'));
    }

    public function edit(Table $table)
    {
        return view('tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance'
        ]);

        $table->update($validated);

        return redirect()->route('tables.index')->with('success', 'Table updated successfully.');
    }

    public function destroy(Table $table)
    {
        // Cek apakah meja sedang digunakan
        if ($table->matches()->where('status', 'live')->exists()) {
            return redirect()->back()->with('error', 'Cannot delete table that is currently in use.');
        }

        $table->delete();

        return redirect()->route('tables.index')->with('success', 'Table deleted successfully.');
    }

    public function usageReport()
    {
        $tables = Table::withCount(['matches as completed_matches_count' => function($query) {
            $query->where('status', 'completed');
        }])->get();

        $totalUsage = $tables->sum('total_usage_minutes');

        return view('tables.usage-report', compact('tables', 'totalUsage'));
    }
}