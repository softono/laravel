{{-- Page title with a breadcrumb trail: :crumbs="[['Dashboard', route('admin/dashboard')], ['Users']]" (last item has no link). --}}
@props(['title', 'crumbs' => []])
<div data-slot="page-header" class="mb-6 flex flex-wrap items-center justify-between gap-2">
    <h1 class="text-3xl font-bold tracking-tight">{{ $title }}</h1>
    @if ($crumbs)
        <nav aria-label="breadcrumb">
            <ol class="text-muted-foreground flex items-center gap-1.5 text-sm">
                @foreach ($crumbs as $crumb)
                    @php([$label, $href] = [$crumb[0], $crumb[1] ?? null])
                    @if (! $loop->first)
                        <li aria-hidden="true">/</li>
                    @endif
                    <li @if ($loop->last) class="text-foreground" aria-current="page" @endif>
                        @if (! $loop->last && $href)
                            <a href="{{ $href }}" class="pjax hover:text-foreground">{{ $label }}</a>
                        @else
                            {{ $label }}
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif
</div>
