@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Player</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Players
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('players.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ request('redirect_to', url()->previous()) }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="ptm_club_id" class="form-label">PTM Club</label>
                       
                            <select class="form-control @error('ptm_club_id') is-invalid @enderror" 
                                    id="ptm_club_id" name="ptm_club_id" {{ $selectedClubId ? 'disabled' : '' }}>
                              
                                <option value="">Select PTM Club</option>
                                @foreach($ptmClubs as $club)
                                <option data-club-name="{{ $club->name }}" value="{{ $club->id }}" {{ old('ptm_club_id', $selectedClubId) == $club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('ptm_club_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($selectedClubId)
                                <input type="hidden" name="ptm_club_id" value="{{ $selectedClubId }}">
                            @endif
                        
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div id="duplicate-alert" style="display:none;"></div>
                    
                    <div class="mb-3">
                        <label for="nickname" class="form-label">Nickname</label>
                        <input type="text" class="form-control @error('nickname') is-invalid @enderror" 
                               id="nickname" name="nickname" value="{{ old('nickname') }}">
                        @error('nickname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="division_ranking" class="form-label">Divisi Ranking *
                            <i class="fas fa-info-circle text-primary ms-1"
                            data-bs-toggle="tooltip"
                            data-bs-placement="right"
                            title="1 = Elite (Highest), 11 = Beginner (Lowest)">
                            </i>
                        </label>
                        <select class="form-control @error('division_ranking') is-invalid @enderror" 
                                id="division_ranking" name="division_ranking" required>
                            <option value="">Select Division ...</option>
                            @for($i = 1; $i <= 11; $i++)
                            <option value="{{ $i }}" {{ old('division_ranking', 10) == $i ? 'selected' : '' }}>
                                Divisi {{ $i }} 
                                @if($i == 1) (Elite) 
                                @elseif($i == 2) (Advanced)
                                @elseif($i == 3) (Intermediate)  
                                @elseif($i == 11) (Beginner)
                                @endif
                            </option>
                            @endfor
                        </select>
                        @error('division_ranking')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                   
                </div>

                <div class="col-md-6">
                     <div class="mb-3">
                        <label for="photo" class="form-label">Photo Profil</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                               id="photo" name="photo" accept="image/*">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror>
                        <div class="form-text">Format: JPG, PNG, GIF. Max. 2MB.</div>
                    </div>

                     <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                   
                    
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-control @error('gender') is-invalid @enderror" 
                                id="gender" name="gender">
                            <option value="">Select gender ...</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of birth</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                   
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="playing_style" class="form-label">Playing Style</label>
                        <select class="form-control @error('playing_style') is-invalid @enderror" 
                                id="playing_style" name="playing_style">
                            <option value="">Select Playing Style ...</option>
                            <option value="offensive" {{ old('playing_style') == 'offensive' ? 'selected' : '' }}>Offensive</option>
                            <option value="defensive" {{ old('playing_style') == 'defensive' ? 'selected' : '' }}>Defensive</option>
                            <option value="all_round" {{ old('playing_style') == 'all_round' ? 'selected' : '' }}>All-Round</option>
                        </select>
                        @error('playing_style')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="grip_style" class="form-label">Grip style</label>
                        <select class="form-control @error('grip_style') is-invalid @enderror" 
                                id="grip_style" name="grip_style">
                            <option value="">Select Grip style ...</option>
                            <option value="shakehand" {{ old('grip_style') == 'shakehand' ? 'selected' : '' }}>Shakehand</option>
                            <option value="penhold" {{ old('grip_style') == 'penhold' ? 'selected' : '' }}>Penhold</option>
                        </select>
                        @error('grip_style')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

          

            <div class="mb-3">
                <label for="bio" class="form-label">Bio / Description</label>
                <textarea class="form-control @error('bio') is-invalid @enderror" 
                          id="bio" name="bio" rows="4">{{ old('bio') }}</textarea>
                @error('bio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Player
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
// Auto-format phone number
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0) {
        value = value.replace(/(\d{4})(\d{4})(\d{0,4})/, '$1-$2-$3');
    }
    e.target.value = value;
});

// Calculate age from date of birth
document.getElementById('date_of_birth').addEventListener('change', function(e) {
    const dob = new Date(e.target.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const monthDiff = today.getMonth() - dob.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    
    // Bisa ditampilkan di suatu tempat jika diperlukan
    console.log('Age:', age);
});
document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('name');
    const clubSelect = document.getElementById('ptm_club_id');
    const emailInput = document.getElementById('email');
    const alertBox = document.getElementById('duplicate-alert');
    //otomatis mengisi nickname
    const nicknameinput = document.getElementById('nickname');
    function sanitize(str) {
        return str.trim().toLowerCase().replace(/.\s+/g, '.').replace(/[^\w.]/g, '');
    }
    function generateEmail() {
        nicknameinput.value=nameInput.value;
        // console.log(nameInput.value,'nameInput.value',nicknameinput,'nicknameinput')
        const name =sanitize(nameInput.value); // hapus karakter aneh

        const selectedClub = clubSelect.options[clubSelect.selectedIndex];
        const clubName = selectedClub?.dataset.clubName ? sanitize(selectedClub.dataset.clubName) : '';

        if (name && clubName) {
            emailInput.value = `${name}@${clubName}.com`;
            checkDuplicate();
        } else {
            emailInput.value = '';
            alertBox.style.display = 'none';
        }
    }

    function checkDuplicate() {
        const playerName = nameInput.value.trim().toUpperCase();
        const clubId = clubSelect.value || document.querySelector('input[name="ptm_club_id"]').value;

        if (!playerName || !clubId) return;

        fetch(`/players/check-duplicate?name=${encodeURIComponent(playerName)}&ptm_club_id=${clubId}`)
            .then(res => res.json())
            .then(data => {
                alertBox.className = data.exists ? 'alert alert-warning mt-2' : 'alert alert-success mt-2';
                alertBox.innerHTML = data.message;
                alertBox.style.display = 'block';
            })
            .catch(() => {
                alertBox.className = 'alert alert-danger mt-2';
                alertBox.textContent = 'Error checking duplicate.';
                alertBox.style.display = 'block';
            });
    }

    nameInput.addEventListener('input', generateEmail);
    clubSelect.addEventListener('change', generateEmail);

    generateEmail(); // untuk inisialisasi awal jika dari halaman club
});
</script>

@endpush
@endsection