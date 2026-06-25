@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Players List</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('players.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Player
            </a>
        </div>
    </div>
    <div class="row col md-6 mb-3">
        <input type="text" id="search" class="form-control" style="width: 50%;" placeholder="🔍 Searching player...">
    </div>

    <div id="players-table">
        @include('players.partials.table', ['players' => $players])
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    let timer = null;

    function fetchData(page = 1, search = '') {
        fetch(`{{ route('players.search') }}?search=${search}&page=${page}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('players-table').innerHTML = html;
                attachPaginationEvents();
            });
    }

    searchInput.addEventListener('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(() => fetchData(1, this.value), 300);
    });

    function attachPaginationEvents() {
        document.querySelectorAll('#players-table .pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                const page = url.searchParams.get('page');
                fetchData(page, searchInput.value);
            });
        });
    }

    attachPaginationEvents();
});
</script>
@endpush
