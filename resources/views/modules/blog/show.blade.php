@extends('layouts.main')
@section('title')
    {{ $post->meta_title ?: $post->title }}
@endsection
@section('content')
    <div class="px-3 py-6 sm:px-6 lg:px-15">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('blog') }}" class="text-muted-foreground hover:text-foreground mb-6 inline-flex items-center gap-1 text-sm">
                <i class="bx bx-arrow-back"></i> Back to blog
            </a>

            <article>
                <div class="mb-4 flex items-center gap-3">
                    <span class="bg-primary/10 text-primary rounded-full px-3 py-1 text-xs font-medium">
                        {{ \App\Constants\BlogCategory::label($post->category) }}
                    </span>
                    <span class="text-muted-foreground text-sm">{{ $general->dateFormat($post->created_at) }}</span>
                </div>

                <h1 class="mb-6 text-2xl font-bold tracking-tight sm:text-3xl">{{ $post->title }}</h1>

                @if ($post->image)
                    <img src="{{ $general->getFileUrl($post->image, 'blog') }}" alt="{{ $post->title }}" class="mb-6 w-full rounded-lg border object-cover">
                @endif

                <div class="prose max-w-none">
                    {!! $post->body !!}
                </div>
            </article>
        </div>
    </div>
@endsection
