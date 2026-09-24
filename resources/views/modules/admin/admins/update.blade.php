@extends('modules.admin.layouts.main')
@section('title')
    Admin Update
@endsection
@section('content')
    <x-ui.page-header title="Admin" :crumbs="[['Dashboard', route('admin/dashboard')], ['Admins', route('admin/admin')], ['Admin Update']]" />
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Admin Update</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.partials.account-form', ['action' => 'admin/admin/save', 'back' => 'admin/admin', 'countries' => $countries, 'permissionGroups' => app(\App\Services\PermissionService::class)->getPermissionListData()])
        </x-ui.card-content>
    </x-ui.card>
@endsection
