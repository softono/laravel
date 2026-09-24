@extends('modules.admin.layouts.main')
@section('title')
    Admin View
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Admin</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/admin') }}" class="pjax hover:text-primary-600">Admins</a>
                </li>
                <li class="breadcrumb-item active">Admin View</li>
            </ol>
        </nav>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div>
            @include('modules.admin.partials.account-profile', ['base' => 'admin/admin'])
        </div>
        <div class="space-y-6 lg:col-span-2">
            @include('modules.admin.partials.recent-sessions')
            @include('modules.admin.partials.recent-activity')

        </div>
    </div>

@endsection
