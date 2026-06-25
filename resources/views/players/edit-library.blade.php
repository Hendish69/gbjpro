@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Player: {{ $player->display_name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('players.library') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Library
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('players.update-library', $player) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $player->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nickname" class="form-label">Nama Panggilan</label>
                        <input type="text" class="form-control @error('nickname') is-invalid @enderror" 
                               id="nickname" name="nickname" value="{{ old('nickname', $player->nickname) }}">
                        @error('nickname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ptm_number" class="form-label">Nomor PTM</label>
                        <input type="text" class="form-control @error('ptm_number') is-invalid @enderror" 
                               id="ptm_number" name="ptm_number" value="{{ old('ptm_number', $player->ptm_number) }}">
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
                            <option value="{{ $club->id }}" {{ old('ptm_club_id', $player->ptm_club_id) == $club->id ? 'selected' : '' }}>
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
                            <option value="{{ $i }}" {{ old('division_ranking', $player->division_ranking) == $i ? 'selected' : '' }}>
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
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Current Photo -->
                    <div class="mb-3">
                        <label class="form-label">Foto Saat Ini</label>
                        <div>
                            @if($player->photo)
                                <img src="{{ asset('storage/' . $player->photo) }}" 
                                     class="player-avatar mb-2" style="width: 100px; height: 100px;">
                                <br>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                                    <label class="form-check-label" for="remove_photo">
                                        Hapus foto saat ini
                                    </label>
                                </div>
                            @else
                                <div class="player-avatar bg-secondary text-white d-flex align-items-center justify-content-center" 
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </div>
                                <small class="text-muted">Tidak ada foto</small>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="photo" class="form-label">Foto Baru</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                               id="photo" name="photo" accept="image/*">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $player->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Telepon</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', $player->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select class="form-control @error('gender') is-invalid @enderror" 
                                id="gender" name="gender">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="male" {{ old('gender', $player->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender', $player->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                            <option value="other" {{ old('gender', $player->gender) == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $player->date_of_birth ? $player->date_of_birth->format('Y-m-d') : '') }}">
                        @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                            <option value="offensive" {{ old('playing_style', $player->playing_style) == 'offensive' ? 'selected' : '' }}>Offensive</option>
                            <option value="defensive" {{ old('playing_style', $player->playing_style) == 'defensive' ? 'selected' : '' }}>Defensive</option>
                            <option value="all_round" {{ old('playing_style', $player->playing_style) == 'all_round' ? 'selected' : '' }}>All-Round</option>
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
                            <option value="shakehand" {{ old('grip_style', $player->grip_style) == 'shakehand' ? 'selected' : '' }}>Shakehand</option>
                            <option value="penhold" {{ old('grip_style', $player->grip_style) == 'penhold' ? 'selected' : '' }}>Penhold</option>
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
                    <option value="{{ $category->id }}" 
                        {{ in_array($category->id, old('category_ids', $player->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                @error('category_ids')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror>
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">Bio / Deskripsi</label>
                <textarea class="form-control @error('bio') is-invalid @enderror" 
                          id="bio" name="bio" rows="4">{{ old('bio', $player->bio) }}</textarea>
                @error('bio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', $player->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Player aktif
                    </label>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Player
                </button>
                <a href="{{ route('players.library') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.player-avatar {
    border-radius: 50%;
    object-fit: cover;
}
</style>
@endsection