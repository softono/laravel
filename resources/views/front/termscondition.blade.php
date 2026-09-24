@extends('layouts.main')
@section('title')
Terms & Condition
@endsection
@section('content')
<style>
    p a {
        color: #6516c2;
        text-decoration: underline;
    }
</style>

<div class="card card-default color-palette-box">
    <div class="card-header">
    </div>
    <div class="card-body">
        <section>
            <div>
                <h2 class="text-center text-2xl font-bold">{{$page->title}}</h2>
                <br>
                <br>
                {!! $page->body !!}
            </div>
        </section>
    </div>
</div>
@endsection
