@extends('layouts.main')
@section('title')
    Blog
@endsection
@section('content')
    <div class="px-3 py-6 sm:px-6 lg:px-15">
        <h1 class="mb-6 text-2xl font-bold tracking-tight">Blog</h1>

        <form method="get" action="{{ route('blog') }}" class="mb-6 flex flex-col gap-3 sm:flex-row">
            <x-ui.input type="search" name="search" :value="request('search')" placeholder="Search articles…" class="sm:max-w-sm" />
            <x-ui.select name="category" class="sm:w-64">
                <option value="">All categories</option>
                @foreach (\App\Constants\BlogCategory::LABELS as $key => $label)
                    <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
        </form>

        @forelse ($posts as $post)
            @if ($loop->first)
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @endif
            <a href="{{ route('blog/show', ['slug' => $post->slug]) }}" class="group block">
                <div class="bg-card h-full rounded-lg border p-5 transition-shadow hover:shadow-md">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="bg-primary/10 text-primary rounded-full px-2.5 py-0.5 text-xs font-medium">
                            {{ \App\Constants\BlogCategory::label($post->category) }}
                        </span>
                        <span class="text-muted-foreground text-xs">{{ $general->dateFormat($post->created_at) }}</span>
                    </div>
                    <h3 class="group-hover:text-primary mb-2 text-base font-semibold leading-snug">{{ $post->title }}</h3>
                    <p class="text-muted-foreground line-clamp-3 text-sm">{{ $post->excerpt }}</p>
                </div>
            </a>
            @if ($loop->last)
                </div>
            @endif
        @empty
            <p class="text-muted-foreground py-12 text-center text-sm">No articles found.</p>
        @endforelse

        <x-ui.pagination :paginator="$posts" />
    </div>
@endsection
