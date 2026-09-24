@extends('layouts.main')
@section('title')
Privacy Policy
@endsection
@section('content')

<style>
    p a {
        color: #6516C2;
        text-decoration: underline;
    }
</style>
<div class="card card-default color-palette-box">
    <div class="card-header">
    </div>
    <div class="card-body">
        <section>
            <div>
                <h2 class="text-center text-2xl font-bold">{{$privacy->title}}</h2>
                <br>
                <br>
                {!! $privacy->body !!}
            </div>
        </section>
    </div>
</div>

@endsection
