@props(['variant' => 'default'])
<div data-slot="alert" role="alert" {{ $attributes->class([
    'relative w-full rounded-lg border px-4 py-3 text-sm grid has-[>svg]:grid-cols-[calc(var(--spacing)*4)_1fr] grid-cols-[0_1fr] has-[>svg]:gap-x-3 gap-y-0.5 items-start [&>svg]:size-4 [&>svg]:translate-y-0.5 [&>svg]:text-current',
    'bg-card text-card-foreground' => $variant === 'default',
    'text-destructive bg-card *:data-[slot=alert-description]:text-destructive/90' => $variant === 'destructive',
    'text-success bg-card border-success/40 *:data-[slot=alert-description]:text-success/90' => $variant === 'success',
    'text-amber-600 dark:text-amber-400 bg-card border-amber-500/40 *:data-[slot=alert-description]:text-amber-600/90 dark:*:data-[slot=alert-description]:text-amber-400/90' => $variant === 'warning',
]) }}>{{ $slot }}</div>
