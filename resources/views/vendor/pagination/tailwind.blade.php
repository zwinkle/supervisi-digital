@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default leading-5 rounded-xl">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 leading-5 rounded-xl hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300">Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-600 bg-white border border-slate-200 leading-5 rounded-xl hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300">Berikutnya</a>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default leading-5 rounded-xl">Berikutnya</span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">
                    Menampilkan
                    <span class="font-medium text-slate-700">{{ $paginator->firstItem() }}</span>
                    hingga
                    <span class="font-medium text-slate-700">{{ $paginator->lastItem() }}</span>
                    dari
                    <span class="font-medium text-slate-700">{{ $paginator->total() }}</span>
                    hasil
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-xl">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Previous" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default rounded-l-xl">
                            <span class="sr-only">Sebelumnya</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-l-xl hover:text-slate-900 focus:z-10 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300" aria-label="Previous">
                            <span class="sr-only">Sebelumnya</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-slate-400 bg-white border border-slate-200">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-semibold text-white bg-indigo-500 border border-indigo-500">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:text-slate-900 focus:z-10 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300" aria-label="Go to page {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-r-xl hover:text-slate-900 focus:z-10 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-300" aria-label="Next">
                            <span class="sr-only">Berikutnya</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Next" class="relative inline-flex items-center px-3 py-2 -ml-px text-sm font-medium text-slate-400 bg-white border border-slate-200 cursor-default rounded-r-xl">
                            <span class="sr-only">Berikutnya</span>
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
