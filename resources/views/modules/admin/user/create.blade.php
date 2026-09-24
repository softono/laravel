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
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/user') }}" class="pjax hover:text-primary-600">Users</a>
                </li>
                <li class="breadcrumb-item active">User Create</li>
            </ol>
        </nav>
    </div>
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">User Create</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.partials.account-form', ['action' => 'admin/user/save', 'back' => 'admin/user', 'countries' => $countries])
        </x-ui.card-content>
    </x-ui.card>
@endsection
