@extends('admin.layouts.main')
@section('title')
Page Update
@endsection
@section('content')

<div class="breadcrumb-box">
    <h4 class="text-xl font-bold text-slate-800">Page</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="admin/pages" class="pjax hover:text-primary-600">Page</a>
            </li>
            <li class="breadcrumb-item active">Page Update</li>
        </ol>
    </nav>
</div>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Page Update</h4>
    </div>
    <div class="card-body">
        <?= view('admin/page/_form', compact('model')) ?>
    </div>
</div>
@endsection