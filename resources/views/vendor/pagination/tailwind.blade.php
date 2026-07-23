@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-400">
            {{ __('Showing') }}
            <span class="font-medium text-gray-200">{{ $paginator->firstItem() }}</span>
            {{ __('to') }}
            <span class="font-medium text-gray-200">{{ $paginator->lastItem() }}</span>
            {{ __('of') }}
            <span class="font-medium text-gray-200">{{ $paginator->total() }}</span>
            {{ __('results') }}
        </p>

        <div class="flex flex-wrap gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded-xl text-sm bg-gray-800 text-gray-600 cursor-not-allowed">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="px-3 py-1.5 rounded-xl text-sm bg-gray-800 text-gray-400 hover:text-white transition">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-1.5 rounded-xl text-sm text-gray-600">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-1.5 rounded-xl text-sm bg-blue-600 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="px-3 py-1.5 rounded-xl text-sm bg-gray-800 text-gray-400 hover:text-white transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="px-3 py-1.5 rounded-xl text-sm bg-gray-800 text-gray-400 hover:text-white transition">›</a>
            @else
                <span class="px-3 py-1.5 rounded-xl text-sm bg-gray-800 text-gray-600 cursor-not-allowed">›</span>
            @endif
        </div>
    </nav>
@endif
