@extends('modules.admin.layouts.main')
@section('title')
Seo Meta Create
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
            <li class="breadcrumb-item active">Seo Meta Create</li>
        </ol>
    </nav>
</div>
<div class="card">
<div class="card-header">
        <div class="flex flex-wrap gap-4">
        <div class="w-full sm:w-1/2">
            <div class="mb-3">
               <h4 class="card-title">Seo Meta Create</h4>
            </div>
        </div>
        <div class="w-full sm:w-1/2">
            <div class="mb-3">
               <h4 class="card-title">Site Map</h4>
            </div>
        </div>
    </div>
    </div>
    <div class="card-body">
        <?= view('admin/seo/_form') ?>
    </div>
</div>

@endsection