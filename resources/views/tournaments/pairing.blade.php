@extends('layouts.app')

@section('title', 'Player Pairing - ' . $tournament->name)
<style>
    .collapse {
    transition: height 0.25s ease;
}
</style>
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
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Draw
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('tournaments.generate-pairs', $tournament) }}" method="POST" id="pairing-form">
                        @csrf
                        
                        <div class="row">
                            <!-- Auto Pairing Options -->
                            <div class="col-md-4">
                                <div class="card">
                            
                                    <div class="card-body">
                                        <!-- Drag & Drop Instructions -->
                                        <div class="mt-3 p-3 bg-light rounded">
                                            <h6 class="mb-2">Drag & Drop Instructions:</h6>
                                            <p class="small mb-1">1. Drag player from available list</p>
                                            <p class="small mb-1">2. Drop to pair slot</p>
                                            <p class="small mb-0">3. Or drag between pairs to swap</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Available Players for Drag & Drop -->
                                <div class="card mt-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Available Players</h6>
                                        <span class="badge bg-primary" id="available-count">{{ $availableForPairing->count() }}</span>
                                    </div>
                                    <div class="card-body" id="available-players">
                                        @foreach($availableForPairing as $player)
                                            @php //dd($player); @endphp
                                            <div class="player-card draggable" 
                                                 draggable="true" 
                                                 data-player-id="{{ $player->id }}"
                                                 data-player-name="{{ $player->name }}"
                                                 data-player-ptm="{{ $player->pivot->representing_ptm_club_id ??  $player->ptm_club_id }}"
                                                 data-player-nickname="{{ $player->nickname }}"
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
                                            <span class="badge bg-success me-2" id="paired-count">0 Pairs Complete</span>
                                            
                                            <button type="button" class="btn btn-success btn-sm" onclick="savePairingProgress()">
                                                <i class="fas fa-save me-1"></i>Save Progress
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm" onclick="clearAllPairs()">
                                                <i class="fas fa-broom me-1"></i>Clear All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="manual-pairing-container">
                                             <!-- Auto-save notification -->
                                        <div id="auto-save-notification" class="alert alert-success mt-3" style="display: none;">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span id="save-message">Progress saved successfully!</span>
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
                                            @php $idx=$tournament->duoPairs?count($tournament->duoPairs):0; @endphp
                                            @foreach($playerChunks as $index => $chunk)
                                                <div class="pair-row mb-3 p-3 border rounded drop-zone" data-pair-index="{{ $idx + 1 }}" id="pairCard{{ $idx }}">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <strong>Double {{ $idx + 1 }}</strong>
                                                        <span class="badge bg-warning pair-status-badge">empty</span>
                                                        <div class="pair-status"  id="buttonaction{{ $idx }}" style="display: none;" >
                                                            <button type="button" class="btn btn-sm btn-outline-secondary " onclick="swapPlayers(this)">
                                                                <i class="fas fa-exchange-alt"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removePair(this)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-secondary toggle-btn" 
                                                                    type="button" 
                                                                    data-bs-toggle="collapse" 
                                                                    data-bs-target="#pairCollapse{{ $idx }}">
                                                                🔽
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div id="pairCollapse{{ $idx }}" class="collapse show">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="player-slot drop-slot" data-slot="player1" 
                                                                        ondrop="dropPlayer(event)" 
                                                                        ondragover="allowDrop(event)"
                                                                        ondragleave="dragLeave(event)">
                                                                        <div class="empty-slot">
                                                                            Drop Player 1 Here
                                                                            <input type="hidden" name="pairs[{{ $idx + 1 }}][player1]" value="">
                                                                            <input type="hidden" ptmid="pairs[{{ $idx + 1 }}][player1]" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="player-slot drop-slot" data-slot="player2"
                                                                        ondrop="dropPlayer(event)" 
                                                                        ondragover="allowDrop(event)"
                                                                        ondragleave="dragLeave(event)">
                                                                        <div class="empty-slot">
                                                                            Drop Player 2 Here
                                                                            <input type="hidden" name="pairs[{{ $idx + 1 }}][player2]" value="">
                                                                            <input type="hidden" ptmid="pairs[{{ $idx + 1 }}][player2]" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php $idx++; @endphp
                                            @endforeach
                                        </div>

                                       
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data players untuk JavaScript -->
<script>
// Data players dari PHP ke JavaScript
const allPlayers = <?php
    echo json_encode($availableForPairing->map(function($player) {
        return [
            'id' => $player->id, 
            'name' => $player->display_name,
            'nickname' => $player->nickname,
            'division' => $player->division_ranking
        ];
    })->toArray());
?>;
</script>
@endsection

<!-- JavaScript untuk drag & drop pairing dan save data -->
<script>
// Global Variables
let pairCounter = {{ $playerChunks->count() + 1 }};
let draggedPlayer = null;
let draggedFromSlot = null;
let autoSaveTimeout = null;

// ==================== DRAG & DROP FUNCTIONS ====================
function toggleCollapseIfFilled(index) {
    const collapseElement = document.getElementById(`pairCollapse${index}`);
    const player1Value = document.querySelector(`[name="pairs[${index + 1}][player1]"]`)?.value;
    const player2Value = document.querySelector(`[name="pairs[${index + 1}][player2]"]`)?.value;

    const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: false });
    const toggleBtn = document.querySelector(`#pairCard${index} .toggle-btn`);

    if (player1Value && player2Value) {
        bsCollapse.hide();
        toggleBtn.innerHTML = '🔼'; // collapsed
    } else {
        bsCollapse.show();
        toggleBtn.innerHTML = '🔽'; // expanded
    }
}
function allowDrop(ev) {
    ev.preventDefault();
    ev.currentTarget.classList.add('drag-over');
}

function dragLeave(ev) {
    ev.currentTarget.classList.remove('drag-over');
}

function dragStart(ev) {
    draggedPlayer = {
        id: ev.target.dataset.playerId,
        name: ev.target.dataset.playerName,
        ptmid: ev.target.dataset.playerPtm,
        nickname: ev.target.dataset.playerNickname || ev.target.dataset.playerName,
        division: ev.target.dataset.playerDivision,
        element: ev.target
    };
    draggedFromSlot = ev.target.closest('.player-slot');
    ev.target.classList.add('dragging');
    ev.dataTransfer.setData('text/plain', ev.target.dataset);
    // ev.dataTransfer.setData('text/plain', ev.target.dataset.playerId);
}

function dragEnd(ev) {
    ev.target.classList.remove('dragging');
    document.querySelectorAll('.drag-over').forEach(el => {
        el.classList.remove('drag-over');
    });
}

function dropPlayer(ev) {
    ev.preventDefault();
    const dropSlot = ev.target.closest('.player-slot');
    
    if (!draggedPlayer || !dropSlot) return;

    // Remove dragging class
    document.querySelector('.dragging')?.classList.remove('dragging');
    dropSlot.classList.remove('drag-over');
    
    // If dropping in the same slot, do nothing
    if (draggedFromSlot === dropSlot) {
        draggedPlayer = null;
        draggedFromSlot = null;
        return;
    }
    // console.log(draggedPlayer,'dropPlayer',dropSlot);
    // Get the pair row and slot type
    const pairRow = dropSlot.closest('.pair-row');
    const slotType = dropSlot.dataset.slot;
    //  console.log(pairRow,'pairRow');

    // Clear the drop slot first
    clearSlot(dropSlot);
    // Create new player card in drop slot
    const playerCard = createPlayerCard(draggedPlayer, true);
    dropSlot.appendChild(playerCard);
    
    // Update hidden input
    const hiddenInput = dropSlot.querySelector('input[type="hidden"]');
    // const player1Input = dropSlot.querySelector('input[name*="[player1]"]');
    const player1Inputptm = dropSlot.querySelector('input[ptmid]');
    // const player2Input = dropSlot.querySelector('input[name*="[player2]"]');
    // const player2Inputptm = dropSlot.querySelector('input[ptmid*="[player2]"]');
    // console.log(hiddenInput,'dropPlayer hiddenInput',player1Inputptm);
    if (hiddenInput) {
        hiddenInput.value = draggedPlayer.id;
        player1Inputptm.value = draggedPlayer.ptmid;
    }
    
    // If dragged from another slot, clear that slot
    if (draggedFromSlot && draggedFromSlot !== dropSlot) {
        // console.log(draggedFromSlot,'draggedFromSlotdraggedFromSlotdraggedFromSlot');
        clearSlot(draggedFromSlot);
    }
    
    // If dragged from available players, remove from there
    if (!draggedFromSlot) {
        draggedPlayer.element.remove();
        updateAvailableCount();
    }
    
    // UPDATE STATUS SETELAH DROP
    updatePairingStatus();
    
    
    draggedPlayer = null;
    draggedFromSlot = null;

}

function createPlayerCard(player, isDraggable = true) {
    const card = document.createElement('div');
    card.className = 'player-card selected';
    // console.log('createPlayerCard',player);
    card.setAttribute('data-player-id', player.id);
    card.setAttribute('data-player-name', player.name);
    card.setAttribute('data-player-ptmid', player.ptmid);
    card.setAttribute('data-player-nickname', player.nickname);
    card.setAttribute('data-player-division', player.division);
    
    if (isDraggable) {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', dragStart);
        card.addEventListener('dragend', dragEnd);
    }
    
    card.innerHTML = `
        <strong>${player.nickname}</strong>
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
    
    // Clear hidden input
    const hiddenInput = slot.querySelector('input[type="hidden"]');
    if (hiddenInput) {
        hiddenInput.value = '';
    }
    
    slot.appendChild(emptySlot);
}

// ==================== PLAYER MANAGEMENT FUNCTIONS ====================

function removePlayerFromPair(button) {
    const playerCard = button.closest('.player-card');
    const slot = playerCard.closest('.player-slot');
    const playerData = {
        id: playerCard.dataset.playerId,
        name: playerCard.dataset.playerName,
        ptmid: playerCard.dataset.playerPtm,
        nickname: playerCard.dataset.playerNickname,
        division: playerCard.dataset.playerDivision
    };
    
    // Add back to available players
    addToAvailablePlayers(playerData);
    
    // Clear the slot
    clearSlot(slot);
    
    // UPDATE STATUS SETELAH REMOVE
    updatePairingStatus();
    updateAvailableCount();
}

function addToAvailablePlayers(player) {
    const availableContainer = document.getElementById('available-players');
    const playerCard = createPlayerCard(player, true);
    availableContainer.appendChild(playerCard);
}

function removeFromAvailablePlayers(playerId) {
    const availablePlayer = document.querySelector(`#available-players .draggable[data-player-id="${playerId}"]`);
    if (availablePlayer) {
        availablePlayer.remove();
        updateAvailableCount();
    }
}

// ==================== PAIR MANAGEMENT FUNCTIONS ====================

function removePair(button) {
    const pairRow = button.closest('.pair-row');
    const playerCards = pairRow.querySelectorAll('.player-card.selected');
    
    // Return players to available list
    playerCards.forEach(card => {
        const playerData = {
            id: card.dataset.playerId,
            name: card.dataset.playerName,
            ptmid: card.dataset.playerPtm,
            nickname: card.dataset.playerNickname,
            division: card.dataset.playerDivision
        };
        addToAvailablePlayers(playerData);
    });
    
    pairRow.remove();
    updatePairNumbers();
    
    // UPDATE STATUS SETELAH REMOVE PAIR
    updatePairingStatus();
    updateAvailableCount();
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
            ptmid: player1Card.dataset.playerPtm,
            nickname: player1Card.dataset.playerNickname,
            division: player1Card.dataset.playerDivision
        };
        const player2Data = {
            id: player2Card.dataset.playerId,
            name: player2Card.dataset.playerName,
            ptmid: player2Card.dataset.playerPtm,
            nickname: player2Card.dataset.playerNickname,
            division: player2Card.dataset.playerDivision
        };
        
        clearSlot(player1Slot);
        clearSlot(player2Slot);
        
        player1Slot.appendChild(createPlayerCard(player2Data, true));
        player2Slot.appendChild(createPlayerCard(player1Data, true));
        
        // Update hidden inputs
        // const player1Input = player1Slot.querySelector('input[type="hidden"]');
        const player1Input = player1Slot.querySelector('input[name*="[player1]"]');
        const player1Inputptm = player1Slot.querySelector('input[ptmid*="[player1]"]');
        const player2Input = player2Slot.querySelector('input[name*="[player2]"]');
         const player2Inputptm = player2Slot.querySelector('input[ptmid*="[player2]"]');
        if (player1Input){
            player1Input.value = player1Data.id;
            player1Inputptm.value = player1Data.ptmid;
        } 
        if (player2Input){
            player2Input.value = player2Data.id;
            player2Inputptm.value = player2Data.ptmid;
        } 
        // console.log(player1Inputptm,player2Inputptm,player1Inputptm.value,player2Inputptm.value,'swapPlayers');
        // UPDATE STATUS SETELAH SWAP
        updatePairingStatus();
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
                ptmid: card.dataset.playerPtm,
                nickname: card.dataset.playerNickname,
                division: card.dataset.playerDivision
            };
            addToAvailablePlayers(playerData);
        });
    });
    
    // Keep only the first pair
    container.innerHTML = '';
    pairCounter = 1;
    addNewPair();
    
    // UPDATE STATUS SETELAH CLEAR ALL
    updatePairingStatus();
    updateAvailableCount();
}

function updatePairNumbers() {
    const pairs = document.querySelectorAll('.pair-row');
    pairs.forEach((pair, index) => {
        const header = pair.querySelector('strong');
        header.textContent = `Pair #${index + 1}`;
        pair.setAttribute('data-pair-index', index + 1);
        console.log('pair updated to index',`pairs[${index + 1}]`);
        // Update input names
        const inputs = pair.querySelectorAll('input[type="hidden"]');
        inputs[0].name = `pairs[${index + 1}][player1]`;
        inputs[1].ptmid = `pairs[${index + 1}][player1]`;
        inputs[2].name = `pairs[${index + 1}][player2]`;
        inputs[3].ptmid = `pairs[${index + 1}][player2]`;
    });
    pairCounter = pairs.length + 1;
}

// ==================== STATUS & SAVE FUNCTIONS ====================

function updatePairingStatus() {
    const pairs = document.querySelectorAll('.pair-row');
    let completedPairs = 0;
    let totalSlots = 0;
    let filledSlots = 0;

    pairs.forEach(pair => {
        // console.log('updatePairingStatus for pair',pair);
        const player1Input = pair.querySelector('input[name*="[player1]"]');
        const player2Input = pair.querySelector('input[name*="[player2]"]');
        const player1 = player1Input ? player1Input.value : '';
        const player2 = player2Input ? player2Input.value : '';
        const statusBadge = pair.querySelector('.pair-status-badge');
        // console.log(' player1,player2:', player1,player2);
        // PERBAIKAN: Tambahkan null checking untuk statusBadge
        if (statusBadge) {
            if (player1 && player2) {
                completedPairs++;
                statusBadge.className = 'badge bg-success pair-status-badge';
                statusBadge.textContent = 'Complete';
               
            } else if (player1 || player2) {
                statusBadge.className = 'badge bg-warning pair-status-badge';
                statusBadge.textContent = 'Partial';
            } else {
                statusBadge.className = 'badge bg-danger pair-status-badge';
                statusBadge.textContent = 'Empty';
            }
        }
         
        
        // Hitung filled slots dengan cara yang benar
        if (player1) filledSlots++;
        if (player2) filledSlots++;
        totalSlots += 2;
    });

    // Update paired count
    const pairedCountElement = document.getElementById('paired-count');
    if (pairedCountElement) {
        pairedCountElement.textContent = `${completedPairs} Pairs Complete`;
        // console.log('Paired count updated:', pairedCountElement,completedPairs);
        // toggleCollapseIfFilled(pair.replace('pairCollapse',''));
    }
    
    // Update progress bar
    const progressBar = document.getElementById('pairing-progress');
    if (progressBar) {
        const progress = totalSlots > 0 ? (filledSlots / totalSlots) * 100 : 0;
        progressBar.style.width = `${progress}%`;
    }
    
    // Auto-save setelah kedua slot terisi
    // console.log('Scheduling auto-save after pairing update',completedPairs,'completedPairs');
    if (completedPairs > 0) {
        toggleCollapseIfFilled(completedPairs-1);
        const buttonaction = document.getElementById('buttonaction'+(completedPairs-1));
        // console.log('buttonaction',buttonaction);
        if (buttonaction) {
            buttonaction.style.display = 'block'; // atau 'inline-block' tergantung tampilannya
        }

        // scheduleAutoSave();
    }
}

function updateAvailableCount() {
    const availableCountElement = document.getElementById('available-count');
    if (availableCountElement) {
        const count = document.querySelectorAll('#available-players .draggable').length;
        availableCountElement.textContent = count;
    }
}

// Fungsi untuk auto-save
function scheduleAutoSave() {
    if (autoSaveTimeout) {
        clearTimeout(autoSaveTimeout);
    }
    
    autoSaveTimeout = setTimeout(() => {
        savePairingProgress();
    }, 2000); // Auto-save setelah 2 detik
}

// Fungsi untuk save progress pairing
function savePairingProgress() {
    const pairs = [];
    const pairRows = document.querySelectorAll('.pair-row');
    
    pairRows.forEach(row => {
        // console.log('Saving row',row);
        const pairIndex = row.dataset.pairIndex;
        const player1Input = row.querySelector('input[name*="[player1]"]');
        const player2Input = row.querySelector('input[name*="[player2]"]');
        const player1ptm = row.querySelector('input[ptmid*="[player1]"]');
        const player2ptm = row.querySelector('input[ptmid*="[player2]"]');
        // console.log(player1ptm,player2ptm,player1ptm.value,player2ptm.value);
        if (player1Input && player2Input && (player1Input.value || player2Input.value)) {
            pairs.push({
                pair_index: pairIndex,
                player1_id: player1Input.value,
                player2_id: player2Input.value,
                player1_club_id: player1ptm.value,
                player2_club_id: player2ptm.value
            });
        }
    });

    // Simpan ke localStorage untuk sementara
    localStorage.setItem('pairing_progress_{{ $tournament->id }}', JSON.stringify({
        pairs: pairs,
        saved_at: new Date().toISOString()
    }));

    // Tampilkan notifikasi
    showSaveNotification('Progress saved successfully!');
    
    // Optional: Simpan ke server via AJAX
    saveToServer(pairs);
}

// Fungsi untuk save ke server
function saveToServer(pairs) {
    // Pastikan route ini ada di web.php
    const saveUrl = '{{ route("tournaments.save-pairing-progress", $tournament) }}';
    
    fetch(saveUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            pairs: pairs,
            tournament_id: {{ $tournament->id }}
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Progress saved to server');
            // OPTIONAL: Refresh halaman setelah save berhasil
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            console.error('Server returned error:', data.message);
        }
    })
    .catch(error => {
        console.error('Error saving to server:', error);
        // Fallback: hanya menggunakan localStorage
        console.log('Using localStorage as fallback');
    });
}

// Fungsi untuk load progress yang disimpan
function loadPairingProgress() {
    const saved = localStorage.getItem('pairing_progress_{{ $tournament->id }}');
    
    if (!saved) {
        showSaveNotification('No saved progress found!', 'warning');
        return;
    }
    
    if (!confirm('Load saved pairing progress? This will replace current pairs.')) {
        return;
    }

    const progressData = JSON.parse(saved);
    
    // Clear existing pairs first
    clearAllPairsWithoutConfirmation();
    
    // Load saved pairs
    progressData.pairs.forEach(pairData => {
        // Find or create pair row
        let pairRow = document.querySelector(`[data-pair-index="${pairData.pair_index}"]`);
        
        if (!pairRow) {
            addNewPair();
            pairRow = document.querySelector(`[data-pair-index="${pairData.pair_index}"]`);
        }
        
        // Fill player slots
        if (pairData.player1) {
            const player1 = allPlayers.find(p => p.id == pairData.player1);
            if (player1) {
                fillPlayerSlot(pairRow, 'player1', player1);
            }
        }
        
        if (pairData.player2) {
            const player2 = allPlayers.find(p => p.id == pairData.player2);
            if (player2) {
                fillPlayerSlot(pairRow, 'player2', player2);
            }
        }
    });
    
    updatePairingStatus();
    updateAvailableCount();
    showSaveNotification('Progress loaded successfully!');
}

// Helper function untuk fill player slot
function fillPlayerSlot(pairRow, slotType, player) {
    const slot = pairRow.querySelector(`[data-slot="${slotType}"]`);
    const hiddenInput = slot.querySelector('input[type="hidden"]');
    
    // Clear slot
    // clearSlot(slot);
    
    // Add player card
    const playerCard = createPlayerCard(player, true);
    slot.appendChild(playerCard);
    if (hiddenInput) {
        hiddenInput.value = player.id;
    }
    
    // Remove from available players
    removeFromAvailablePlayers(player.id);
}

// Clear pairs tanpa konfirmasi (untuk load progress)
function clearAllPairsWithoutConfirmation() {
    const container = document.getElementById('manual-pairing-container');
    const allPairs = container.querySelectorAll('.pair-row');
    
    // Return all players to available list
    allPairs.forEach(pair => {
        const playerCards = pair.querySelectorAll('.player-card.selected');
        playerCards.forEach(card => {
            const playerData = {
                id: card.dataset.playerId,
                name: card.dataset.playerName,
                ptmid: card.dataset.playerPtm,
                nickname: card.dataset.playerNickname,
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

// Show save notification
function showSaveNotification(message, type = 'success') {
    const notification = document.getElementById('auto-save-notification');
    const messageEl = document.getElementById('save-message');
    
    if (notification && messageEl) {
        notification.className = `alert alert-${type} mt-3`;
        messageEl.textContent = message;
        notification.style.display = 'block';
        
        setTimeout(() => {
            notification.style.display = 'none';
        }, 3000);
    }
}

// Auto Pairing Function (placeholder)
function generateAutoPairs() {
    alert('Auto pairing feature will be implemented soon!');
    // Implement auto pairing logic here
}

// ==================== INITIALIZATION ====================

// Initialize dengan update status
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag events for existing players
    document.querySelectorAll('[id^="pairCollapse"]').forEach((el, idx) => {
        toggleCollapseIfFilled(idx);
    });

    // event listener manual toggle
    document.querySelectorAll('.toggle-btn').forEach((btn, idx) => {
        btn.addEventListener('click', () => {
            const collapseEl = document.getElementById(`pairCollapse${idx}`);
            const isCollapsed = collapseEl.classList.contains('show');
            btn.innerHTML = isCollapsed ? '🔼' : '🔽';
        });
    });

    document.querySelectorAll('.player-card.draggable').forEach(card => {
        card.addEventListener('dragstart', dragStart);
        card.addEventListener('dragend', dragEnd);
    });
    
    // Initialize drag events for slots
    document.querySelectorAll('.player-slot').forEach(slot => {
        slot.addEventListener('dragover', allowDrop);
        slot.addEventListener('dragleave', dragLeave);
    });
    
    // Initial status update
    updatePairingStatus();
    updateAvailableCount();
    
    // Cek jika ada saved progress
    const saved = localStorage.getItem('pairing_progress_{{ $tournament->id }}');
    if (saved) {
        console.log('Saved progress available');
    }
    document.addEventListener('dragover', function (e) {
        const scrollMargin = 100;
        const scrollSpeed = 20;
        const y = e.clientY;

        if (y > window.innerHeight - scrollMargin) {
            window.scrollBy(0, scrollSpeed);
        } else if (y < scrollMargin) {
            window.scrollBy(0, -scrollSpeed);
        }
    });
});
</script>

<!-- Tambahkan CSS untuk status badges -->
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

.player-slot:hover, .player-slot.drag-over {
    border-color: #007bff;
    background: #f8f9fa;
}

.empty-slot {
    color: #6c757d;
    text-align: center;
    padding: 20px;
    font-style: italic;
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

.pair-status-badge {
    font-size: 0.7em;
    margin-right: 8px;
}

#auto-save-notification {
    transition: all 0.3s ease;
}

.progress {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.5s ease;
}
</style>