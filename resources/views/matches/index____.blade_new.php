@extends('layouts.app')
<style>
body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background-color: #101010;
  color: #fff;
  overflow-x: auto;
}

.tournament {
  display: flex;
  padding: 40px;
  gap: 100px;
}

.round {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.round h3 {
  margin-bottom: 20px;
  font-size: 14px;
  color: #ccc;
}

.match {
  position: relative;
  background: #7d6565ff;
  border-radius: 8px;
  width: 320px;
  /* padding-left: 8px; */
  margin: 20px 0;
  border: 1px solid #333;
  z-index: 1;
  display: grid;
  grid-template-columns: 2fr 1fr; /* kiri 2 bagian, kanan 1 bagian */
  gap: 8px;
  align-items: center;
}

.match-info {
  display: flex;
  flex-direction: column;
  background: #1f1f1f;
  border-right: 1px solid #333;
  padding-right: 10px;
  padding-left: 10px;
  
}

.player {
  display: flex;
  justify-content: space-between;
  padding: 6px 0;
  font-size: 13px;
  color: #e0e0e0;
}

.player:first-child {
  border-bottom: 1px solid #333;
}
.winner {
  font-weight: bold;
  color: #00d26a;
}

.connector-line {
  position: absolute;
  width: 100px;
  height: 2px;
  background: #ff0000ff;
  right: -100px;
  top: 50%;
  transform: translateY(-50%);
}

.connector-curve {
  position: absolute;
  width: 100px;
  height: 100%;
  overflow: visible;
}

.connector-curve path {
  stroke: #ff0000ff;
  stroke-width: 2;
  fill: none;
}
.flag {
  width: 18px;
  height: 12px;
  border-radius: 2px;
  margin-right: 8px;
  object-fit: cover;
}

.team {
  display: flex;
  align-items: center;
}

.schedule {
  background-color: #1a1a1a;
  color: #aaa;
  text-align: right;
  font-size: 12px;
  padding: 4px 12px 6px;
  border-top: 1px solid #333;
}

.connector {
  position: absolute;
  right: -60px;
  top: 50%;
  width: 60px;
  height: 2px;
  background-color: transparent;
  overflow: visible;
}

.connector svg {
  position: absolute;
  top: -20px;
  left: 0;
  width: 60px;
  height: 40px;
}

.connector path {
  fill: none;
  stroke: #fff;
  stroke-width: 2;
}

/* Atur jarak antar pertandingan untuk ronde berbeda */
.round[data-round="32"] .match { margin: 30px 0; }
.round[data-round="16"] .match { margin: 60px 0; }
.round[data-round="8"] .match { margin: 120px 0; }
.round[data-round="4"] .match { margin: 240px 0; }
.round[data-round="2"] .match { margin: 480px 0; }
</style>
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Matches Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matches.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Schedule Match
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="tournament">
    <div class="round">
        @foreach($matches  as $round => $match)
        
        <div class="match" >
            <div class="match-info">
            <div class="player {{ isset($match->winner_id) && $match->winner_id == $match->player1_id ? 'winner' : '' }}">
            {{ $match->player1->name  }} {{ $match->player1->division_ranking  }}
            </div>
            <div class="player {{ isset($match->winner_id) && $match->winner_id == $match->player1_id ? 'winner' : '' }}"">
            {{ $match->player2->name }} {{ $match->player2->division_ranking  }}
            </div>
            </div>
             <div class="schedule">
                <div class="datetime">
              12 Okt 2025<br>15:30 WIB 
                </div>
            </div>
        </div>
    @endforeach
   
  </div>
</div>
<script>
// Ambil semua round dalam urutan (misal: 16 → 8 → 4)
const rounds = Array.from(document.querySelectorAll('.round'));
for (let i = 0; i < rounds.length - 1; i++) {
  const currentRound = rounds[i];
  const nextRound = rounds[i + 1];

  const matches = currentRound.querySelectorAll('.match');
  const nextMatches = nextRound.querySelectorAll('.match');

  matches.forEach((match, index) => {
    const targetIndex = Math.floor(index / 2);
    const nextMatch = nextMatches[targetIndex];
    if (!nextMatch) return;

    const startRect = match.getBoundingClientRect();
    const endRect = nextMatch.getBoundingClientRect();

    // Hitung posisi relatif
    const startY = match.offsetTop + match.offsetHeight / 2;
    const endY = nextMatch.offsetTop + nextMatch.offsetHeight / 2;
    const diffY = endY - startY;

    // Buat elemen SVG untuk konektor
    const connector = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    connector.setAttribute("class", "connector-curve");
    connector.setAttribute("width", "100");
    connector.setAttribute("height", Math.abs(diffY) + 40);
    connector.style.position = "absolute";
    connector.style.right = "-100px";
    connector.style.top = diffY > 0 ? "50%" : "auto";
    connector.style.bottom = diffY < 0 ? "50%" : "auto";

    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    const curveY = diffY > 0 ? diffY + 20 : -diffY + 20;
    path.setAttribute("d", `M0,20 C50,20 50,${curveY} 100,${curveY}`);
    path.setAttribute("stroke", "#fc0000ff");
    path.setAttribute("stroke-width", "2");
    path.setAttribute("fill", "none");

    connector.appendChild(path);
    match.appendChild(connector);
  });
}
</script>
@endsection