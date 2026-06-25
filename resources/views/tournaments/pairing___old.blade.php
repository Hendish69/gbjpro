@extends('layouts.app')

@section('title', 'Player Pairing - ' . $tournament->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-handshake me-2"></i>
                        Player Pairing - {{ $tournament->name }}
                    </h4>
                    <div>
                        <a href="{{ route('tournaments.draw', $tournament) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Draw
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tempatkan seluruh kode pairing form yang tadi di sini -->
                     <!-- resources/views/tournaments/draw.blade.php - Tambahkan section pairing -->
<div class="col-md-12">
    <div class="card mt-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fas fa-handshake me-2"></i>
                Player Pairing - Double/Double Duo
            </h5>
        </div>
        <div class="card-body">
            @if($tournament->type === 'double' || $tournament->type === 'doubleduo')
                @if($tournament->players->count() >= 2)
                    <form action="{{ route('tournaments.generate-pairs', $tournament) }}" method="POST" id="pairing-form">
                        @csrf
                        
                        <div class="row">
                            <!-- Auto Pairing Options -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Auto Pairing</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Pairing Method</label>
                                            <select name="pairing_method" class="form-select" id="pairing-method">
                                                <option value="random">Random Pairs</option>
                                                <option value="balanced">Balanced (Strong + Weak)</option>
                                                <option value="similar">Similar Division</option>
                                                <option value="club">Same Club</option>
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary w-100" onclick="generateAutoPairs()">
                                            <i class="fas fa-magic me-2"></i>Generate Auto Pairs
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Manual Pairing -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Manual Pairing</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="manual-pairing-container">
                                           @php
    $players = $tournament->players;
    $playerChunks = $players->chunk(2);
    $pairNumber = 1;
    $pairedPlayers = [];
@endphp

@foreach($playerChunks as $chunk)
    <div class="pair-row mb-3 p-3 border rounded">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Pair #{{ $pairNumber }}</strong>
            @if($pairNumber > 1)
                <button type="button" class="btn btn-sm btn-danger" onclick="removePair(this)">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
        <div class="row">
            <div class="col-md-6">
                <select name="pairs[{{ $pairNumber }}][player1]" class="form-select pair-player" required>
                    <option value="">Select Player 1</option>
                    @foreach($players as $player)
                        <option value="{{ $player->id }}" 
                                {{ $chunk->first() && $chunk->first()->id == $player->id ? 'selected' : '' }}>
                            {{ $player->display_name }} - Div {{ $player->division_ranking }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <select name="pairs[{{ $pairNumber }}][player2]" class="form-select pair-player" required>
                    <option value="">Select Player 2</option>
                    @foreach($players as $player)
                        <option value="{{ $player->id }}"
                                {{ $chunk->get(1) && $chunk->get(1)->id == $player->id ? 'selected' : '' }}>
                            {{ $player->display_name }} - Div {{ $player->division_ranking }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @php 
        $pairNumber++;
        if($chunk->first()) $pairedPlayers[] = $chunk->first()->id;
        if($chunk->get(1)) $pairedPlayers[] = $chunk->get(1)->id;
    @endphp
@endforeach

                                            <!-- Unpaired players -->
                                            @php
                                                $unpairedPlayers = $tournament->players->whereNotIn('id', $pairedPlayers);
                                            @endphp
                                            
                                            @if($unpairedPlayers->count() > 0)
                                                <div class="alert alert-info">
                                                    <strong>Unpaired Players:</strong>
                                                    @foreach($unpairedPlayers as $player)
                                                        <span class="badge bg-secondary ms-1">{{ $player->display_name }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <button type="button" class="btn btn-success mt-3" onclick="addNewPair()">
                                            <i class="fas fa-plus me-2"></i>Add New Pair
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-handshake me-2"></i>Confirm Pairs & Generate Bracket
                            </button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Need at least 2 players for pairing.
                    </div>
                @endif
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Pairing is only available for Double and Double Duo tournaments.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- JavaScript untuk pairing -->
<script>
let pairCounter = {{ $playerChunks->count() + 1 }};

const allPlayers = @json($tournament->players->map(function($player) {
    return ['id' => $player->id, 'name' => $player->display_name, 'division' => $player->division_ranking];
}));

function addNewPair() {
    const container = document.getElementById('manual-pairing-container');
    
    const pairDiv = document.createElement('div');
    pairDiv.className = 'pair-row mb-3 p-3 border rounded';
    pairDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Pair #${pairCounter}</strong>
            <button type="button" class="btn btn-sm btn-danger" onclick="removePair(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="row">
            <div class="col-md-6">
                <select name="pairs[${pairCounter}][player1]" class="form-select pair-player" required>
                    <option value="">Select Player 1</option>
                    ${generatePlayerOptions()}
                </select>
            </div>
            <div class="col-md-6">
                <select name="pairs[${pairCounter}][player2]" class="form-select pair-player" required>
                    <option value="">Select Player 2</option>
                    ${generatePlayerOptions()}
                </select>
            </div>
        </div>
    `;
    
    container.appendChild(pairDiv);
    pairCounter++;
}

function removePair(button) {
    button.closest('.pair-row').remove();
    updatePairNumbers();
}

function updatePairNumbers() {
    const pairs = document.querySelectorAll('.pair-row');
    pairs.forEach((pair, index) => {
        const header = pair.querySelector('strong');
        header.textContent = `Pair #${index + 1}`;
        
        // Update input names
        const selects = pair.querySelectorAll('select');
        selects[0].name = `pairs[${index + 1}][player1]`;
        selects[1].name = `pairs[${index + 1}][player2]`;
    });
    pairCounter = pairs.length + 1;
}

function generatePlayerOptions() {
    return allPlayers.map(player => 
        `<option value="${player.id}">${player.name} - Div ${player.division}</option>`
    ).join('');
}

function generateAutoPairs() {
    const method = document.getElementById('pairing-method').value;
    
    // Clear existing pairs except first one
    const pairs = document.querySelectorAll('.pair-row');
    for (let i = 1; i < pairs.length; i++) {
        pairs[i].remove();
    }
    
    // Reset first pair
    const firstPair = pairs[0];
    const selects = firstPair.querySelectorAll('select');
    selects[0].selectedIndex = 0;
    selects[1].selectedIndex = 0;
    
    pairCounter = 2;
    
    // Simple auto-pairing logic (bisa dikembangkan lebih kompleks)
    const shuffledPlayers = [...allPlayers].sort(() => Math.random() - 0.5);
    
    if (method === 'balanced') {
        // Sort by division untuk balanced pairing
        shuffledPlayers.sort((a, b) => a.division - b.division);
    } else if (method === 'similar') {
        // Sort by division untuk similar pairing
        shuffledPlayers.sort((a, b) => a.division - b.division);
    }
    
    // Create pairs
    for (let i = 0; i < shuffledPlayers.length; i += 2) {
        if (i + 1 < shuffledPlayers.length) {
            if (i === 0) {
                // Use first pair
                const selects = document.querySelectorAll('.pair-row:first-child select');
                selects[0].value = shuffledPlayers[i].id;
                selects[1].value = shuffledPlayers[i + 1].id;
            } else {
                // Add new pair
                addNewPair();
                const newPair = document.querySelector('.pair-row:last-child');
                const selects = newPair.querySelectorAll('select');
                selects[0].value = shuffledPlayers[i].id;
                selects[1].value = shuffledPlayers[i + 1].id;
            }
        }
    }
}

// Prevent duplicate player selection
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('pair-player')) {
        const allSelects = document.querySelectorAll('.pair-player');
        const selectedValues = Array.from(allSelects).map(select => select.value).filter(val => val);
        
        allSelects.forEach(select => {
            Array.from(select.options).forEach(option => {
                if (option.value && option.value !== select.value) {
                    option.disabled = selectedValues.includes(option.value) && selectedValues.filter(v => v === option.value).length > 1;
                }
            });
        });
    }
});
</script>

<style>
.pair-row {
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.pair-row:hover {
    background: #e9ecef;
}

.pair-player option:disabled {
    color: #6c757d;
    background-color: #e9ecef;
}
</style>
                    <!-- @include('tournaments.partials.pairing-form') -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection