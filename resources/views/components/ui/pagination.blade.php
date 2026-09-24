@props(['paginator'])
@if ($paginator->hasPages())
    <nav class="mt-8 flex items-center justify-between gap-4" aria-label="Pagination">
        <p class="text-muted-foreground text-sm">
            Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            <span class="hidden sm:inline">({{ $paginator->total() }} items)</span>
        </p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <x-ui.button variant="outline" size="sm" disabled>Previous</x-ui.button>
            @else
                <x-ui.button :href="$paginator->previousPageUrl()" variant="outline" size="sm">Previous</x-ui.button>
            @endif
            @if ($paginator->hasMorePages())
                <x-ui.button :href="$paginator->nextPageUrl()" variant="outline" size="sm">Next</x-ui.button>
            @else
                <x-ui.button variant="outline" size="sm" disabled>Next</x-ui.button>
            @endif
        </div>
    </nav>
@endif
