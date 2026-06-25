@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit PTM Club</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('ptm-clubs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Clubs
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('ptm-clubs.update', $ptmClub) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Club Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $ptmClub->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Club Code</label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               id="code" name="code" value="{{ old('code', $ptmClub->code) }}" 
                               placeholder="e.g., PTM-DKI, PTM-JABAR">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Unique code untuk identifikasi club</div>
                    </div>

                    <div class="mb-3">
                        <label for="city" class="form-label">City *</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                               id="city" name="city" value="{{ old('city', $ptmClub->city) }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="province" class="form-label">Province *</label>
                        <input type="text" class="form-control @error('province') is-invalid @enderror" 
                               id="province" name="province" value="{{ old('province', $ptmClub->province) }}" required>
                        @error('province')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" required>{{ old('address', $ptmClub->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', $ptmClub->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $ptmClub->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control @error('website') is-invalid @enderror" 
                               id="website" name="website" value="{{ old('website', $ptmClub->website) }}" 
                               placeholder="https://example.com">
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="4">{{ old('description', $ptmClub->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', $ptmClub->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Club Active
                    </label>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Club
                </button>
                <a href="{{ route('ptm-clubs.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-format phone number
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0) {
        value = value.replace(/(\d{4})(\d{4})(\d{0,4})/, '$1-$2-$3');
    }
    e.target.value = value;
});

// Auto-generate code from name (only if code is empty)
document.getElementById('name').addEventListener('blur', function(e) {
    const name = e.target.value;
    const codeField = document.getElementById('code');
    
    // Only generate if code is empty or contains only old value
    if (name && (!codeField.value || codeField.value === '{{ $ptmClub->code }}')) {
        // Generate simple code from name (first 3 letters of each word)
        const words = name.split(' ');
        let generatedCode = '';
        
        for (let word of words) {
            if (word.length > 0) {
                generatedCode += word.substring(0, 3).toUpperCase();
            }
        }
        
        codeField.value = generatedCode;
    }
});
</script>
@endsection