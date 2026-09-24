@extends('modules.admin.layouts.main')
@section('title')
Dashboard
@endsection
@section('content')
<?php $sessionUser = auth()->user();?>

<div class="breadcrumb-box">
  <h4 class="text-xl font-bold text-slate-800">Dashboard</h4>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
      </li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol>
  </nav>
</div>

@if($sessionUser->hasPermission('admin_setting'))

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div>
    <div class="card h-full border-l-4 border-l-primary-500">
      <div class="card-body">
        <div class="flex items-center gap-4 mb-2">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded bg-primary-50 text-primary-600"><i class="bx bx-user text-lg"></i></span>
          <h4 class="mb-0 text-lg font-semibold text-slate-800">{{ $totalUser }}</h4>
        </div>
        <p class="mb-2 text-sm text-slate-500">Total Users</p>
      </div>
    </div>
  </div>
  <div>
    <div class="card h-full border-l-4 border-l-emerald-500">
      <div class="card-body">
        <div class="flex items-center gap-4 mb-2">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded bg-emerald-50 text-emerald-600"><i class="bx bx-user-check text-lg"></i></span>
          <h4 class="mb-0 text-lg font-semibold text-slate-800">{{ $activeUser }}</h4>
        </div>
        <p class="mb-2 text-sm text-slate-500">Active Users</p>
      </div>
    </div>
  </div>
  <div>
    <div class="card h-full border-l-4 border-l-rose-500">
      <div class="card-body">
        <div class="flex items-center gap-4 mb-2">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded bg-rose-50 text-rose-600"><i class="bx bx-user-x text-lg"></i></span>
          <h4 class="mb-0 text-lg font-semibold text-slate-800">{{ $deactiveUser }}</h4>
        </div>
        <p class="mb-2 text-sm text-slate-500">Inactive Users</p>
      </div>
    </div>
  </div>
</div>
@endif
{{view('admin/site/userchart',['activeUser'=>$activeUser,'deactiveUser'=>$deactiveUser])}}
@endsection