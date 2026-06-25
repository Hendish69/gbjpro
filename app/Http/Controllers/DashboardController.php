<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Tournament;
use App\Models\Matches;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_players' => Player::count(),
            'total_tournaments' => Tournament::count(),
            'ongoing_tournaments' => Tournament::where('status', 'ongoing')->count(),
            'completed_matches' => Matches::where('status', 'completed')->count(),
        ];

        $recentTournaments = Tournament::latest()->take(5)->get();
        // dd($recentTournaments);
        $tournament = $recentTournaments[0];
        $tournament->load([
            'tournamentPlayers.player.ptmClub',
            'tournamentPlayers.representingClub',
        ]);
       
        // $topPlayers = Player::where('ptm_club_id',6)->orderBy('division_ranking', 'asc') // 1 adalah tertinggi, jadi pakai asc
        //     ->take(5)
        //     ->with('ptmClub')
        //     ->get();
        // dd($topPlayers);
        $upcomingMatches = Matches::with(['player1', 'player2', 'tournament'])
            ->where('status', 'scheduled')
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->take(5)
            ->get();
        // dd($upcomingMatches);
        return view('dashboard', compact('stats', 'recentTournaments', 'tournament', 'upcomingMatches'));
    }
    public function getTournamentAnalytics(Tournament $tournament)
    {
        return [
            'table_utilization' => $tournament->getTableStatistics(),
            'match_progress' => $tournament->getProgressAttribute(),
            'participant_stats' => $tournament->getRegistrationSummary(),
            'time_estimations' => $tournament->getScheduleEstimation()
        ];
    }
}