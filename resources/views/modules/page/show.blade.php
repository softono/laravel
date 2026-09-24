@extends('layouts.main')
@section('title')
    {{ $page->meta_title ?: $page->title }}
@endsection
@section('content')
    <div class="px-3 py-6 sm:px-6 lg:px-15">
        <div class="mx-auto max-w-3xl">
            <h1 class="mb-6 text-2xl font-bold tracking-tight sm:text-3xl">{{ $page->title }}</h1>
            <div class="prose prose-neutral max-w-none">
                {!! $page->body !!}
            </div>
        </div>
    </div>
@endsection
