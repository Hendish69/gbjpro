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
                    
                    <!-- Drag & Drop Instructions -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="mb-2">Drag & Drop Instructions:</h6>
                        <p class="small mb-1">1. Drag player from available list</p>
                        <p class="small mb-1">2. Drop to pair slot</p>
                        <p class="small mb-0">3. Or drag between pairs to swap</p>
                    </div>
                </div>
            </div>
            
            <!-- Available Players for Drag & Drop -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Available Players</h6>
                </div>
                <div class="card-body" id="available-players">
                    @foreach($availableForPairing as $player)
                       
                        <div class="player-card draggable" 
                             draggable="true" 
                             data-player-id="{{ $player->id }}"
                             data-player-name="{{ $player->display_name }}"
                             data-player-division="{{ $player->division_ranking }}">
                            <strong>{{ $player->nickname }}</strong>
                            <small class="text-muted">Div {{ $player->division_ranking }}</small>
                        </div>
                       
                    @endforeach
                </div>
            </div>
        </div>
     
        <!-- Manual Pairing with Drag & Drop -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Manual Pairing</h6>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" onclick="addNewPair()">
                            <i class="fas fa-plus me-1"></i>Add Pair
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="clearAllPairs()">
                            <i class="fas fa-broom me-1"></i>Clear All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="manual-pairing-container">
                       
                        @foreach($playerChunks as $index => $chunk)
                      
                            <div class="pair-row mb-3 p-3 border rounded drop-zone" data-pair-index="{{ $index + 1 }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Pair #{{ $index + 1 }}</strong>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="swapPlayers(this)">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        @if($index > 0)
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removePair(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="player-slot drop-slot" data-slot="player1" 
                                             ondrop="dropPlayer(event)" 
                                             ondragover="allowDrop(event)">
                                           
                                                <div class="empty-slot">
                                                    Drop Player 1 Here
                                                    <input type="hidden" name="pairs[{{ $index + 1 }}][player1]" value="">
                                                </div>
                                            
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="player-slot drop-slot" data-slot="player2"
                                             ondrop="dropPlayer(event)" 
                                             ondragover="allowDrop(event)">
                                           
                                           
                                                <div class="empty-slot">
                                                    Drop Player 2 Here
                                                    <input type="hidden" name="pairs[{{ $index + 1 }}][player2]" value="">
                                                </div>
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Unpaired players section -->
                    
                    @if($availableForPairing->count() > 0)
                        <div class="alert alert-info mt-3">
                            <strong>Unpaired Players:</strong>
                            @foreach($availableForPairing as $player)
                                <span class="badge bg-secondary ms-1">{{ $player->nickname }}</span>
                            @endforeach
                        </div>
                    @endif
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
@endsection()

<!-- JavaScript untuk drag & drop pairing -->
<script>
let pairCounter = {{ $playerChunks->count() + 1 }};
let draggedPlayer = null;
let draggedFromSlot = null;

const allPlayers = @json($players->map(function($player) {
    return [
        'id' => $player->id, 
        'name' => $player->display_name, 
        'division' => $player->division_ranking
    ];
}));

// Drag & Drop Functions
function allowDrop(ev) {
    ev.preventDefault();
}

function dragStart(ev) {
    draggedPlayer = {
        id: ev.target.dataset.playerId,
        name: ev.target.dataset.playerName,
        division: ev.target.dataset.playerDivision,
        element: ev.target
    };
    draggedFromSlot = ev.target.closest('.player-slot');
    ev.target.classList.add('dragging');
}

function dropPlayer(ev) {
    ev.preventDefault();
    const dropSlot = ev.target.closest('.player-slot');
    
    if (!draggedPlayer || !dropSlot) return;

    // Remove dragging class
    document.querySelector('.dragging')?.classList.remove('dragging');
    
    // If dropping in the same slot, do nothing
    if (draggedFromSlot === dropSlot) {
        draggedPlayer = null;
        draggedFromSlot = null;
        return;
    }

    // Get the pair row and slot type
    const pairRow = dropSlot.closest('.pair-row');
    const slotType = dropSlot.dataset.slot;
    
    // Clear the drop slot first
    clearSlot(dropSlot);
    
    // Create new player card in drop slot
    const playerCard = createPlayerCard(draggedPlayer, true);
    dropSlot.appendChild(playerCard);
    
    // Update hidden input
    const hiddenInput = dropSlot.querySelector('input[type="hidden"]');
    hiddenInput.value = draggedPlayer.id;
    
    // If dragged from another slot, clear that slot
    if (draggedFromSlot && draggedFromSlot !== dropSlot) {
        clearSlot(draggedFromSlot);
    }
    
    // If dragged from available players, remove from there
    if (!draggedFromSlot) {
        draggedPlayer.element.remove();
    }
    
    updateUnpairedPlayers();
    draggedPlayer = null;
    draggedFromSlot = null;
}

function createPlayerCard(player, isDraggable = true) {
    const card = document.createElement('div');
    card.className = 'player-card selected';
    card.setAttribute('data-player-id', player.id);
    card.setAttribute('data-player-name', player.name);
    card.setAttribute('data-player-division', player.division);
    
    if (isDraggable) {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', dragStart);
    }
    
    card.innerHTML = `
        <strong>${player.name}</strong>
        <small class="text-muted">Div ${player.division}</small>
        <button type="button" class="btn-remove" onclick="removePlayerFromPair(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    return card;
}

function clearSlot(slot) {
    const existingCard = slot.querySelector('.player-card');
    if (existingCard) {
        existingCard.remove();
    }
    const emptySlot = document.createElement('div');
    emptySlot.className = 'empty-slot';
    emptySlot.textContent = `Drop ${slot.dataset.slot === 'player1' ? 'Player 1' : 'Player 2'} Here`;
    slot.appendChild(emptySlot);
}

function removePlayerFromPair(button) {
    const playerCard = button.closest('.player-card');
    const slot = playerCard.closest('.player-slot');
    const playerData = {
        id: playerCard.dataset.playerId,
        name: playerCard.dataset.playerName,
        division: playerCard.dataset.playerDivision
    };
    
    // Add back to available players
    addToAvailablePlayers(playerData);
    
    // Clear the slot
    clearSlot(slot);
    
    updateUnpairedPlayers();
}

function addToAvailablePlayers(player) {
    const availableContainer = document.getElementById('available-players');
    const playerCard = createPlayerCard(player, true);
    availableContainer.appendChild(playerCard);
}

function addNewPair() {
    const container = document.getElementById('manual-pairing-container');
    
    const pairDiv = document.createElement('div');
    pairDiv.className = 'pair-row mb-3 p-3 border rounded drop-zone';
    pairDiv.setAttribute('data-pair-index', pairCounter);
    pairDiv.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Pair #${pairCounter}</strong>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="swapPlayers(this)">
                    <i class="fas fa-exchange-alt"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="removePair(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="player-slot drop-slot" data-slot="player1" 
                     ondrop="dropPlayer(event)" 
                     ondragover="allowDrop(event)">
                    <div class="empty-slot">
                        Drop Player 1 Here
                        <input type="hidden" name="pairs[${pairCounter}][player1]" value="">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="player-slot drop-slot" data-slot="player2"
                     ondrop="dropPlayer(event)" 
                     ondragover="allowDrop(event)">
                    <div class="empty-slot">
                        Drop Player 2 Here
                        <input type="hidden" name="pairs[${pairCounter}][player2]" value="">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(pairDiv);
    pairCounter++;
    updatePairNumbers();
}

function removePair(button) {
    const pairRow = button.closest('.pair-row');
    const playerCards = pairRow.querySelectorAll('.player-card.selected');
    
    // Return players to available list
    playerCards.forEach(card => {
        const playerData = {
            id: card.dataset.playerId,
            name: card.dataset.playerName,
            division: card.dataset.playerDivision
        };
        addToAvailablePlayers(playerData);
    });
    
    pairRow.remove();
    updatePairNumbers();
    updateUnpairedPlayers();
}

function swapPlayers(button) {
    const pairRow = button.closest('.pair-row');
    const slots = pairRow.querySelectorAll('.player-slot');
    const player1Slot = slots[0];
    const player2Slot = slots[1];
    
    const player1Card = player1Slot.querySelector('.player-card');
    const player2Card = player2Slot.querySelector('.player-card');
    
    if (player1Card && player2Card) {
        // Swap the player cards
        const player1Data = {
            id: player1Card.dataset.playerId,
            name: player1Card.dataset.playerName,
            division: player1Card.dataset.playerDivision
        };
        const player2Data = {
            id: player2Card.dataset.playerId,
            name: player2Card.dataset.playerName,
            division: player2Card.dataset.playerDivision
        };
        
        clearSlot(player1Slot);
        clearSlot(player2Slot);
        
        player1Slot.appendChild(createPlayerCard(player2Data, true));
        player2Slot.appendChild(createPlayerCard(player1Data, true));
        
        // Update hidden inputs
        player1Slot.querySelector('input[type="hidden"]').value = player2Data.id;
        player2Slot.querySelector('input[type="hidden"]').value = player1Data.id;
    }
}

function clearAllPairs() {
    if (!confirm('Are you sure you want to clear all pairs?')) return;
    
    const container = document.getElementById('manual-pairing-container');
    const allPairs = container.querySelectorAll('.pair-row');
    
    // Return all players to available list
    allPairs.forEach(pair => {
        const playerCards = pair.querySelectorAll('.player-card.selected');
        playerCards.forEach(card => {
            const playerData = {
                id: card.dataset.playerId,
                name: card.dataset.playerName,
                division: card.dataset.playerDivision
            };
            addToAvailablePlayers(playerData);
        });
    });
    
    // Keep only the first pair
    container.innerHTML = '';
    pairCounter = 1;
    addNewPair();
}

function updatePairNumbers() {
    const pairs = document.querySelectorAll('.pair-row');
    pairs.forEach((pair, index) => {
        const header = pair.querySelector('strong');
        header.textContent = `Pair #${index + 1}`;
        pair.setAttribute('data-pair-index', index + 1);
        
        // Update input names
        const inputs = pair.querySelectorAll('input[type="hidden"]');
        inputs[0].name = `pairs[${index + 1}][player1]`;
        inputs[1].name = `pairs[${index + 1}][player2]`;
    });
    pairCounter = pairs.length + 1;
}

function updateUnpairedPlayers() {
    // This function can be enhanced to show unpaired players
    console.log('Update unpaired players logic here');
}

// Initialize drag events for existing players
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.player-card.draggable').forEach(card => {
        card.addEventListener('dragstart', dragStart);
    });
});
</script>

<style>
.player-card {
    padding: 10px;
    margin: 5px 0;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    cursor: move;
    position: relative;
    transition: all 0.3s ease;
}

.player-card:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.player-card.selected {
    background: #d4edda;
    border-color: #c3e6cb;
}

.player-card.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.player-slot {
    min-height: 80px;
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    padding: 10px;
    transition: all 0.3s ease;
}

.player-slot:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.empty-slot {
    color: #6c757d;
    text-align: center;
    padding: 20px;
    font-style: italic;
}

.drop-zone.drag-over {
    background: #e3f2fd;
    border-color: #007bff;
}

.btn-remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 2px 5px;
    border-radius: 3px;
}

.btn-remove:hover {
    background: #dc3545;
    color: white;
}

.pair-row {
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.pair-row:hover {
    background: #e9ecef;
    border-color: #007bff;
}
</style>