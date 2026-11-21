@if($records->hasPages())
    <nav class="form-actions" aria-label="{{ $section }} pagination">
        @if($records->previousPageUrl())
            <a class="button button-quiet" href="{{ $records->previousPageUrl() }}" rel="prev">Previous</a>
        @endif
        <span class="button button-quiet" aria-current="page">Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>
        @if($records->nextPageUrl())
            <a class="button button-quiet" href="{{ $records->nextPageUrl() }}" rel="next">Next</a>
        @endif
    </nav>
@endif
