@extends('admin.layouts.main')
@section('title')
    User Update
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="fw-bold py-3 mb-4">User</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="admin/user" class="pjax">Users</a>
                </li>
                <li class="breadcrumb-item active">User Update</li>
            </ol>
        </nav>
    </div>
    <div class="card card-default color-palette-box">
        <div class="card-header justify-content-between">
            <h4 class="align-middle d-sm-inline-block d-none">Users Update</h4>
        </div>
        <div class="card-body">
            <?= view('admin/user/_form', compact('model')) ?>
        </div>
    </div>
@endsection
