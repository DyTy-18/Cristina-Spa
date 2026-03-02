@if ($paginator->hasPages())
    <nav style="display:flex; align-items:center; gap:0.4rem; flex-wrap:wrap;">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm" style="opacity:0.4; cursor:default;" aria-disabled="true">&lsaquo; Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline btn-sm" rel="prev">&lsaquo; Anterior</a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="btn btn-outline btn-sm" style="opacity:0.5; cursor:default;">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-primary btn-sm" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-outline btn-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline btn-sm" rel="next">Siguiente &rsaquo;</a>
        @else
            <span class="btn btn-outline btn-sm" style="opacity:0.4; cursor:default;" aria-disabled="true">Siguiente &rsaquo;</span>
        @endif

    </nav>
@endif
