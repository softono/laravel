@extends('modules.admin.layouts.main')
@section('title')
Seo Meta Update
@endsection
@section('content')

<div class="breadcrumb-box">
    <h4 class="text-xl font-bold text-slate-800">Seo Meta</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="admin/seo/meta" class="pjax hover:text-primary-600">Seo Meta</a>
            </li>
            <li class="breadcrumb-item active">Seo Meta Update</li>
        </ol>
    </nav>
</div>
<div class="card">
<div class="card-header">
        <h4 class="card-title">Seo Meta Update</h4>
    </div>
  <div class="card-body">
    <?= view('admin/seo/_form', compact('model')) ?>
  </div>
</div>

@endsection