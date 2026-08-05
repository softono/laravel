@extends('layouts.main')
@section('title')
Create Bucket
@endsection
@section('content')
<h4 class="fw-bold py-3 mb-4">Create Bucket</h4>
<div class="card">
    <div class="card-body">
        <?= view('bucket._form') ?>
    </div>
</div>
@endsection
