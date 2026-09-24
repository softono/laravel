@extends('modules.admin.layouts.main')
@section('title')
    Admin Update
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
                <li class="breadcrumb-item active">Admin Update</li>
            </ol>
        </nav>
    </div>
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Admin Update</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.partials.account-form', ['action' => 'admin/admin/save', 'back' => 'admin/admin', 'countries' => $countries, 'permissionGroups' => app(\App\Services\PermissionService::class)->getPermissionListData()])
        </x-ui.card-content>
    </x-ui.card>
@endsection
