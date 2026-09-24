@extends('modules.admin.layouts.main')
@section('title')
    Blog Update
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Blog</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/blog') }}" class="pjax hover:text-primary-600">Blog</a>
                </li>
                <li class="breadcrumb-item active">Blog Update</li>
            </ol>
        </nav>
    </div>
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Blog Update</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.blog.partials.form')
        </x-ui.card-content>
    </x-ui.card>
@endsection
