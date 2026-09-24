@extends('modules.admin.layouts.main')
@section('title')
    Blog Update
@endsection
@section('content')
    <x-ui.page-header title="Blog" :crumbs="[['Dashboard', route('admin/dashboard')], ['Blog', route('admin/blog')], ['Blog Update']]" />
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Blog Update</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.blog.partials.form')
        </x-ui.card-content>
    </x-ui.card>
@endsection
