@extends('modules.admin.layouts.main')
@section('title')
    User Create
@endsection
@section('content')
    <x-ui.page-header title="User" :crumbs="[['Dashboard', route('admin/dashboard')], ['Users', route('admin/user')], ['User Create']]" />
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">User Create</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.partials.account-form', ['action' => 'admin/user/save', 'back' => 'admin/user', 'countries' => $countries])
        </x-ui.card-content>
    </x-ui.card>
@endsection
