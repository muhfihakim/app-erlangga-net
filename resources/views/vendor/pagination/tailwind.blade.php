@if ($paginator->hasPages())
    <div class="flex items-center justify-between p-4 md:p-5 border-t border-slate-100 flex-wrap gap-4">
        <!-- Text info -->
        <div>
            <p class="text-xs text-slate-500 font-medium m-0">
                Menampilkan <span class="font-bold text-slate-800">{{ $paginator->firstItem() }}</span>
                sampai <span class="font-bold text-slate-800">{{ $paginator->lastItem() }}</span>
                dari <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> data
            </p>
        </div>

        <!-- Pagination buttons -->
        <div class="flex items-center gap-1.5 ml-auto">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="w-9 h-9 rounded-lg border border-slate-200 bg-slate-50 text-slate-300 inline-flex items-center justify-center text-xs cursor-not-allowed">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-sky-500 hover:text-sky-600 hover:bg-sky-50 inline-flex items-center justify-center text-xs font-semibold transition-all no-underline">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="w-9 h-9 inline-flex items-center justify-center text-xs text-slate-400 font-bold">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-9 h-9 rounded-lg bg-sky-500 text-white font-bold inline-flex items-center justify-center text-xs shadow-md shadow-sky-500/20">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-sky-500 hover:text-sky-600 hover:bg-sky-50 inline-flex items-center justify-center text-xs font-semibold transition-all no-underline">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:border-sky-500 hover:text-sky-600 hover:bg-sky-50 inline-flex items-center justify-center text-xs font-semibold transition-all no-underline">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="w-9 h-9 rounded-lg border border-slate-200 bg-slate-50 text-slate-300 inline-flex items-center justify-center text-xs cursor-not-allowed">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
@endif
