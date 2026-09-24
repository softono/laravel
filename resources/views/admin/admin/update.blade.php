@extends('admin.layouts.main')
@section('title')
Admin Update
@endsection
@section('content')
<div class="breadcrumb-box">
    <h4 class="text-xl font-bold text-slate-800">Admin</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="admin/admin" class="pjax hover:text-primary-600">Admin</a>
            </li>
            <li class="breadcrumb-item active">Admin Update</li>
        </ol>
    </nav>
</div>
<div class="card">
<div class="card-header">
        <h4 class="card-title">Admin Update</h4>
    </div>
  <div class="card-body">
    <?= view('admin/admin/_form', compact('model','countries')) ?>
  </div>
</div>
@endsection
