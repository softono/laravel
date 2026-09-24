@extends('layouts.main')
@section('title')
{{ $page->title }}
@endsection
@section('content')
<div>
    <h4 class="text-xl font-bold mb-4">{{ $page->title }}</h4>
    <div class="mb-5">
        <div class="card mb-4">
            <div class="card-body">
                {!! $page->body !!}
            </div>
        </div>
    </div>
</div>
@endsection
