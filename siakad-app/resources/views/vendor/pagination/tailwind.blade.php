<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-end gap-4">
        {{-- Mobile view --}}
        <div class="flex items-center gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-800 transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            <span class="text-xs text-slate-500 px-2">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-800 transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- Desktop view --}}
        <div class="hidden sm:flex items-center gap-2">
            {{-- Info text --}}
            <p class="text-sm text-slate-500 mr-3">
                @if ($paginator->firstItem())
                    <span class="font-medium text-slate-700">{{ $paginator->firstItem() }}</span>
                    –
                    <span class="font-medium text-slate-700">{{ $paginator->lastItem() }}</span>
                    dari
                    <span class="font-medium text-slate-700">{{ $paginator->total() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
            </p>

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-800 transition-all duration-150" aria-label="{{ __('pagination.previous') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-bold text-white bg-slate-700 border border-slate-700 shadow-sm shadow-slate-200">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-800 transition-all duration-150" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-800 transition-all duration-150" aria-label="{{ __('pagination.next') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-sm font-medium text-slate-300 bg-slate-50 border border-slate-100 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
