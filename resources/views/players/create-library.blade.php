@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Player to Library</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('players.library') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Library
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('players.store-library') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nickname" class="form-label">Nama Panggilan</label>
                        <input type="text" class="form-control @error('nickname') is-invalid @enderror" 
                               id="nickname" name="nickname" value="{{ old('nickname') }}">
                        @error('nickname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ptm_number" class="form-label">Nomor PTM</label>
                        <input type="text" class="form-control @error('ptm_number') is-invalid @enderror" 
                               id="ptm_number" name="ptm_number" value="{{ old('ptm_number') }}">
                        @error('ptm_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ptm_club_id" class="form-label">Klub PTM</label>
                        <select class="form-control @error('ptm_club_id') is-invalid @enderror" 
                                id="ptm_club_id" name="ptm_club_id">
                            <option value="">Pilih Klub PTM</option>
                            @foreach($ptmClubs as $club)
                            <option value="{{ $club->id }}" {{ old('ptm_club_id') == $club->id ? 'selected' : '' }}>
                                {{ $club->display_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('ptm_club_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="division_ranking" class="form-label">Divisi Ranking *</label>
                        <select class="form-control @error('division_ranking') is-invalid @enderror" 
                                id="division_ranking" name="division_ranking" required>
                            <option value="">Pilih Divisi</option>
                            @for($i = 1; $i <= 11; $i++)
                            <option value="{{ $i }}" {{ old('division_ranking', 11) == $i ? 'selected' : '' }}>
                                Divisi {{ $i }} 
                                @if($i == 1) (Elite) 
                                @elseif($i == 2) (Advanced)
                                @elseif($i == 3) (Intermediate)  
                                @elseif($i == 11) (Pemula)
                                @endif
                            </option>
                            @endfor
                        </select>
                        @error('division_ranking')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">1 = Elite (Tertinggi), 11 = Pemula (Terendah)</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select class="form-control @error('gender') is-invalid @enderror" 
                                id="gender" name="gender">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto Profil</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                               id="photo" name="photo" accept="image/*">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB.</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="playing_style" class="form-label">Gaya Bermain</label>
                        <select class="form-control @error('playing_style') is-invalid @enderror" 
                                id="playing_style" name="playing_style">
                            <option value="">Pilih Gaya Bermain</option>
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
                        <label for="grip_style" class="form-label">Gaya Pegangan</label>
                        <select class="form-control @error('grip_style') is-invalid @enderror" 
                                id="grip_style" name="grip_style">
                            <option value="">Pilih Gaya Pegangan</option>
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
                <label for="category_ids" class="form-label">Kategori Player</label>
                <select class="form-control @error('category_ids') is-invalid @enderror" 
                        id="category_ids" name="category_ids[]" multiple>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', [])) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                @error('category_ids')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Hold Ctrl untuk memilih multiple kategori</div>
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">Bio / Deskripsi</label>
                <textarea class="form-control @error('bio') is-invalid @enderror" 
                          id="bio" name="bio" rows="4">{{ old('bio') }}</textarea>
                @error('bio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Player to Library
                </button>
                <a href="{{ route('players.library') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.player-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}
</style>

<script>
// Preview photo sebelum upload
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Tambah preview image jika diperlukan
            console.log('File selected:', file.name);
        }
        reader.readAsDataURL(file);
    }
});

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
</script>
@endsection