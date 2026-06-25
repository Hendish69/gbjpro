@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Record Match Score</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('matches.show', $match) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Match
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6 text-center">
                <h4>{{ $match->player1->name }}</h4>
                <div class="rating">Rating: {{ $match->player1->rating }}</div>
            </div>
            <div class="col-md-6 text-center">
                <h4>{{ $match->player2->name }}</h4>
                <div class="rating">Rating: {{ $match->player2->rating }}</div>
            </div>
        </div>

        <form action="{{ route('matches.record-score', $match) }}" method="POST">
            @csrf
            
            <div id="sets-container">
                <div class="set-row mb-3">
                    <div class="row align-items-center">
                        <div class="col-2">
                            <label class="form-label">Set 1</label>
                        </div>
                        <div class="col-4">
                            <input type="number" class="form-control" name="sets[0][player1_score]" 
                                   min="0" max="11" value="{{ old('sets.0.player1_score', 0) }}" required>
                        </div>
                        <div class="col-4">
                            <input type="number" class="form-control" name="sets[0][player2_score]" 
                                   min="0" max="11" value="{{ old('sets.0.player2_score', 0) }}" required>
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-danger btn-sm remove-set" disabled>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="button" id="add-set" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Set
                </button>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Table tennis sets are typically played to 11 points. The winner must win by 2 points.
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Score
                </button>
                <a href="{{ route('matches.show', $match) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    let setCount = 1;

    document.getElementById('add-set').addEventListener('click', function() {
        const container = document.getElementById('sets-container');
        const newSet = document.createElement('div');
        newSet.className = 'set-row mb-3';
        newSet.innerHTML = `
            <div class="row align-items-center">
                <div class="col-2">
                    <label class="form-label">Set ${setCount + 1}</label>
                </div>
                <div class="col-4">
                    <input type="number" class="form-control" name="sets[${setCount}][player1_score]" 
                           min="0" max="11" value="0" required>
                </div>
                <div class="col-4">
                    <input type="number" class="form-control" name="sets[${setCount}][player2_score]" 
                           min="0" max="11" value="0" required>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm remove-set">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newSet);
        setCount++;

        // Enable remove buttons for all sets except the first one
        document.querySelectorAll('.remove-set').forEach(button => {
            button.disabled = false;
        });
    });

    // Remove set functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-set')) {
            const setRow = e.target.closest('.set-row');
            setRow.remove();
            
            // Renumber remaining sets
            document.querySelectorAll('.set-row').forEach((row, index) => {
                row.querySelector('label').textContent = `Set ${index + 1}`;
                const inputs = row.querySelectorAll('input');
                inputs[0].name = `sets[${index}][player1_score]`;
                inputs[1].name = `sets[${index}][player2_score]`;
            });
            
            setCount = document.querySelectorAll('.set-row').length;
            
            // Disable remove button if only one set remains
            if (setCount === 1) {
                document.querySelector('.remove-set').disabled = true;
            }
        }
    });
</script>

<style>
.set-row {
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    background-color: #f8f9fa;
}
.rating {
    color: #6c757d;
    font-size: 0.9rem;
}
</style>
@endsection