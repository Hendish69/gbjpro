<div class="container-fluid">
    <h3>Double Elimination Bracket - {{ $tournament->name }}</h3>
    <p>Peserta: {{ $bracket['total_participants'] }} | BYE WB: {{ $bracket['wb_byes'] }} | BYE LB: {{ $bracket['lb_byes'] }}</p>

    <div class="row">
        <!-- Winner Bracket -->
        <div class="col-md-6">
            <h4>Winner Bracket</h4>
            @foreach($bracket['winner_bracket'] as $roundIndex => $matches)
                <div class="round mb-3">
                    <h6>Ronde {{ $roundIndex + 1 }}</h6>
                    @foreach($matches as $match)
                        <div class="match p-2 border mb-1">
                            <div>{{ $match['p1'] }} vs {{ $match['p2'] }}</div>
                            <small class="text-success">Pemenang: {{ $match['winner'] }}</small>
                            @if($match['loser'])<small class="text-danger"> → {{ $match['loser'] }} ke LB</small>@endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- Loser Bracket -->
        <div class="col-md-6">
            <h4>Loser Bracket</h4>
            @foreach($bracket['loser_bracket'] as $roundIndex => $matches)
                <div class="round mb-3">
                    <h6>Ronde {{ $roundIndex + 1 }}</h6>
                    @foreach($matches as $match)
                        <div class="match p-2 border mb-1 bg-light">
                            <div>{{ $match['p1'] }} vs {{ $match['p2'] }}</div>
                            <small class="text-success">Pemenang: {{ $match['winner'] }}</small>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <!-- Grand Final -->
    @if($bracket['grand_final'])
    <div class="text-center mt-4 p-4 bg-warning">
        <h4>GRAND FINAL</h4>
        <p>
            <strong>{{ $bracket['grand_final']['wb_champion'] }}</strong> (WB) 
            vs 
            <strong>{{ $bracket['grand_final']['lb_champion'] }}</strong> (LB)
        </p>
        <h5>Juara: {{ $bracket['grand_final']['champion'] }}</h5>
        @if($bracket['grand_final']['needs_second_match'])
            <p class="text-danger">LB harus menang 2x!</p>
        @endif
    </div>
    @endif
</div>