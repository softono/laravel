@extends('modules.admin.layouts.main')
@section('title')
    User View
@endsection
@section('content')
    <x-ui.page-header title="User" :crumbs="[['Dashboard', route('admin/dashboard')], ['Users', route('admin/user')], ['User View']]" />

    <div class="grid gap-6 lg:grid-cols-3">
        <div>
            @include('modules.admin.partials.account-profile', ['base' => 'admin/user', 'actions' => new \Illuminate\Support\HtmlString(view('modules.admin.user.partials.mail-button')->render())])
        </div>
        <div class="space-y-6 lg:col-span-2">
            @include('modules.admin.partials.recent-sessions')
            @include('modules.admin.partials.recent-activity')
            @include('modules.admin.user.partials.mails')
        </div>
    </div>
    @include('modules.admin.user.partials.mail-modal')
@endsection
