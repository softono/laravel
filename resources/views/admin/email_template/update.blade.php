@extends('admin.layouts.main')
@section('title')
Email Template Update
@endsection
@section('content')

<div class="breadcrumb-box">
    <h4 class="text-xl font-bold text-slate-800">Email</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="admin/email-template" class="pjax hover:text-primary-600">Email</a>
            </li>
            <li class="breadcrumb-item active">Email Update</li>
        </ol>
    </nav>
</div>
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Email Update</h4>
    </div>
    <div class="card-body">
        <?= view('admin/email_template/_form', compact('model','example')) ?>
    </div>
</div>
@endsection