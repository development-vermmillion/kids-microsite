@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        <span>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        <div class="pages">
            @if ($paginator->onFirstPage())
                <span class="btn btn-light btn-sm" aria-disabled="true" style="opacity:.5">
                    <span class="material-symbols-outlined">chevron_left</span> Previous</span>
            @else
                <a class="btn btn-light btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <span class="material-symbols-outlined">chevron_left</span> Previous</a>
            @endif

            <span class="btn btn-light btn-sm" style="cursor:default">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a class="btn btn-light btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Next <span class="material-symbols-outlined">chevron_right</span></a>
            @else
                <span class="btn btn-light btn-sm" aria-disabled="true" style="opacity:.5">
                    Next <span class="material-symbols-outlined">chevron_right</span></span>
            @endif
        </div>
    </nav>
@endif
