@extends('modules.admin.layouts.main')
@section('title')
    User Create
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">User</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="admin/user" class="pjax hover:text-primary-600">Users</a>
                </li>
                <li class="breadcrumb-item active">User Create</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">User Create</h4>
        </div>
        <div class="card-body">
            <?= view('admin/user/_form', compact('countrilist')) ?>
        </div>
    </div>
@endsection
