@if ($paginator->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">

                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}">Previous</a></li>
                @endif

                {{-- First Page --}}
                @if ($paginator->currentPage() > 3)
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                    @if ($paginator->currentPage() > 4)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                {{-- Page Links --}}
                @for ($page = max(1, $paginator->currentPage() - 2); $page <= min($paginator->lastPage(), $paginator->currentPage() + 2); $page++)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                    @endif
                @endfor

                {{-- Last Page --}}
                @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                    @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}">Next</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                @endif

            </ul>
        </nav>
    </div>
@endif
