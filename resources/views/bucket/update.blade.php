@extends('layouts.main')
@section('title')
Edit Bucket
@endsection
@section('content')
<h4 class="fw-bold py-3 mb-4">Edit Bucket</h4>
<div class="card">
    <div class="card-body">
        <?= view('bucket._form', compact('bucket')) ?>
    </div>
</div>
@endsection
