{{--
    Next's TsGrid pagination for a Laravel LengthAwarePaginator: Rows select, "Showing a-b of n items", round
    Previous / numbered / Next buttons. Every button is a real link, so it works without JS; inside a
    [data-ajax-grid] container app.pagination (assets/js/app.js) loads the target URL and swaps the grid instead.
    Pass :page-sizes="[12, 24, 48]" to show the Rows select (the controller must honour `limit`).
--}}
@props(['paginator', 'pageSizes' => []])
@php
    $page = $paginator->currentPage();
    $last = $paginator->lastPage();
    // Same window as Next: up to 3 pages either side of the current one, with the first/last page and an ellipsis.
    $window = [];
    if ($page > 4) {
        array_push($window, 1, '…');
    }
    for ($i = max(1, $page - 3); $i < $page; $i++) {
        $window[] = $i;
    }
    $window[] = $page;
    for ($i = $page + 1; $i <= min($last, $page + 3); $i++) {
        $window[] = $i;
    }
    if ($last > $page + 3) {
        array_push($window, '…', $last);
    }
    $query = \Illuminate\Support\Arr::except($paginator->getOptions()['query'] ?? [], ['page', 'limit']);
    $limitUrl = fn (int $n) => $paginator->path().'?'.http_build_query($query + ['limit' => $n]);
    $circle = 'flex size-10 items-center justify-center rounded-full text-sm font-medium transition-colors duration-300';
    $arrow = 'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';
@endphp
@if ($paginator->total())
    <div data-pagination class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-1.5">
            @if ($pageSizes)
                <span class="text-muted-foreground text-sm">Rows</span>
                <x-ui.select data-pagination-limit aria-label="Rows per page" class="!h-8 !w-[70px] cursor-pointer">
                    @foreach ($pageSizes as $size)
                        <option value="{{ $limitUrl($size) }}" @selected($paginator->perPage() === $size)>{{ $size }}</option>
                    @endforeach
                </x-ui.select>
            @endif
        </div>

        <p class="text-muted-foreground text-sm">Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }} items</p>

        <nav aria-label="Page navigation">
            <ul class="flex flex-wrap items-center justify-center gap-2">
                <li>
                    @if ($paginator->onFirstPage())
                        <span aria-label="Previous" aria-disabled="true" class="{{ $circle }} border-border pointer-events-none border opacity-50">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" {!! $arrow !!}><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" data-page-link aria-label="Previous"
                            class="{{ $circle }} border-border hover:bg-primary hover:text-primary-foreground border">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" {!! $arrow !!}><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                        </a>
                    @endif
                </li>

                @foreach ($window as $item)
                    <li>
                        @if ($item === '…')
                            <span class="text-muted-foreground flex size-10 items-center justify-center">…</span>
                        @elseif ($item === $page)
                            <span aria-current="page" class="{{ $circle }} bg-primary text-primary-foreground">{{ $item }}</span>
                        @else
                            <a href="{{ $paginator->url($item) }}" data-page-link
                                class="{{ $circle }} text-foreground hover:bg-primary hover:text-primary-foreground">{{ $item }}</a>
                        @endif
                    </li>
                @endforeach

                <li>
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" data-page-link aria-label="Next"
                            class="{{ $circle }} border-border hover:bg-primary hover:text-primary-foreground border">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" {!! $arrow !!}><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </a>
                    @else
                        <span aria-label="Next" aria-disabled="true" class="{{ $circle }} border-border pointer-events-none border opacity-50">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" {!! $arrow !!}><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                        </span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
@endif
