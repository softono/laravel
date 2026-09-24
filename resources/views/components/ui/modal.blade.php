{{-- Data-attribute modal: open with [data-modal-open="#id"] or app.openModal(); close with [data-modal-dismiss], the backdrop or Escape. --}}
@props(['id', 'size' => 'lg', 'contentId' => null])
@php
    $sizes = ['md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-2xl'];
@endphp
<div id="{{ $id }}" data-modal role="dialog" aria-modal="true" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" data-modal-dismiss></div>
    <div {{ $attributes->class(['bg-background relative z-10 max-h-full w-full overflow-y-auto rounded-lg border p-6 shadow-lg', $sizes[$size]]) }}>
        <div @if ($contentId) id="{{ $contentId }}" @endif class="grid gap-4">{{ $slot }}</div>
        <button type="button" data-modal-dismiss aria-label="Close"
            class="text-muted-foreground hover:text-foreground focus-visible:ring-ring absolute top-4 right-4 cursor-pointer rounded-xs opacity-70 transition-opacity outline-none hover:opacity-100 focus-visible:ring-2">
            <i class="bx bx-x text-xl"></i>
        </button>
    </div>
</div>
