@props(['variant' => 'default'])
@php
    $variants = [
        'default' => 'border-transparent bg-primary text-primary-foreground [a&]:hover:bg-primary/90',
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground [a&]:hover:bg-secondary/90',
        'destructive' => 'border-destructive text-destructive bg-transparent [a&]:hover:bg-destructive/10 focus-visible:ring-destructive/20',
        'success' => 'border-success text-[var(--color-success)] bg-transparent [a&]:hover:bg-success/10 focus-visible:ring-success/20',
        'outline' => 'text-foreground [a&]:hover:bg-accent [a&]:hover:text-accent-foreground',
    ];
@endphp
<span data-slot="badge" {{ $attributes->class(['inline-flex font-semibold items-center justify-center rounded-full border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 gap-1 transition-[color,box-shadow] overflow-hidden', $variants[$variant]]) }}>{{ $slot }}</span>
