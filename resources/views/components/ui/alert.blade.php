@props(['variant' => 'default'])
<div data-slot="alert" role="alert" {{ $attributes->class([
    'relative w-full rounded-lg border px-4 py-3 text-sm grid grid-cols-[0_1fr] gap-y-0.5 items-start',
    'bg-card text-card-foreground' => $variant === 'default',
    'text-destructive bg-card' => $variant === 'destructive',
]) }}>{{ $slot }}</div>
