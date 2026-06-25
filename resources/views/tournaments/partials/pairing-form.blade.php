@php
    $players = $tournament->players;
    $playerChunks = $players->chunk(2);
    $pairNumber = 1;
    $pairedPlayers = [];
@endphp

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
                        @foreach($playerChunks as $index => $chunk)
                            <div class="pair-row mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Pair #{{ $index + 1 }}</strong>
                                    @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removePair(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <select name="pairs[{{ $index + 1 }}][player1]" class="form-select pair-player" required>
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
                                        <select name="pairs[{{ $index + 1 }}][player2]" class="form-select pair-player" required>
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
                                if($chunk->first()) $pairedPlayers[] = $chunk->first()->id;
                                if($chunk->get(1)) $pairedPlayers[] = $chunk->get(1)->id;
                            @endphp
                        @endforeach

                        <!-- Unpaired players -->
                        @php
                            $unpairedPlayers = $players->whereNotIn('id', $pairedPlayers);
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

<!-- JavaScript untuk pairing -->
<script>
let pairCounter = {{ $playerChunks->count() + 1 }};
const allPlayers = @json($players->map(function($player) {
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
    
    // Clear existing pairs
    const container = document.getElementById('manual-pairing-container');
    container.innerHTML = '';
    pairCounter = 1;
    
    let shuffledPlayers = [...allPlayers];
    
    // Apply pairing method
    if (method === 'balanced') {
        // Strong + Weak pairing: sort by division dan pair first dengan last
        shuffledPlayers.sort((a, b) => a.division - b.division);
        const balancedPairs = [];
        while (shuffledPlayers.length > 1) {
            balancedPairs.push([shuffledPlayers.shift(), shuffledPlayers.pop()]);
        }
        if (shuffledPlayers.length === 1) {
            balancedPairs.push([shuffledPlayers[0]]);
        }
        shuffledPlayers = balancedPairs.flat();
    } else if (method === 'similar') {
        // Similar division pairing
        shuffledPlayers.sort((a, b) => a.division - b.division);
    } else if (method === 'random') {
        // Random pairing
        shuffledPlayers.sort(() => Math.random() - 0.5);
    }
    // Note: Club pairing butuh data club yang tidak tersedia di JavaScript
    
    // Create pairs
    for (let i = 0; i < shuffledPlayers.length; i += 2) {
        addNewPair();
        const newPair = document.querySelector('.pair-row:last-child');
        const selects = newPair.querySelectorAll('select');
        selects[0].value = shuffledPlayers[i].id;
        if (i + 1 < shuffledPlayers.length) {
            selects[1].value = shuffledPlayers[i + 1].id;
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