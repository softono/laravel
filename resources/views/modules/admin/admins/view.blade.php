@extends('modules.admin.layouts.main')
@section('title')
    Admin View
@endsection
@section('content')
    <x-ui.page-header title="Admin" :crumbs="[['Dashboard', route('admin/dashboard')], ['Admins', route('admin/admin')], ['Admin View']]" />

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
