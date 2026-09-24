@extends('modules.admin.layouts.main')
@section('title')
    SEO Create
@endsection
@section('content')
    <x-ui.page-header title="SEO Meta" :crumbs="[['Dashboard', route('admin/dashboard')], ['SEO Meta', route('admin/seo/meta')], ['SEO Create']]" />
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">SEO Create</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            @include('modules.admin.seo._form')
        </x-ui.card-content>
    </x-ui.card>
@endsection
