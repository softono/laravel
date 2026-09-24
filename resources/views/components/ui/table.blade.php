<div data-slot="table-container" class="relative w-full overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-muted-foreground/20 [&:hover::-webkit-scrollbar-thumb]:bg-muted-foreground/40">
    <table data-slot="table" {{ $attributes->class(['w-full caption-bottom text-sm border-separate border-spacing-0']) }}>{{ $slot }}</table>
</div>
