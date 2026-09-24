@extends('modules.admin.layouts.main')
@section('title')
    SEO Update
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">SEO Meta</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/seo/meta') }}" class="pjax hover:text-primary-600">SEO Meta</a>
                </li>
                <li class="breadcrumb-item active">SEO Update</li>
            </ol>
        </nav>
    </div>
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">SEO Update</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.seo._form')
        </x-ui.card-content>
    </x-ui.card>
@endsection
