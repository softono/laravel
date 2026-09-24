@extends('modules.admin.layouts.main')
@section('title')
    SEO Meta
@endsection
@section('content')
    @php($viewer = auth()->user())
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">SEO Meta</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">SEO Meta</li>
            </ol>
        </nav>
    </div>

    <x-ui.card>
        <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
            <x-ui.card-title class="text-lg">SEO Meta</x-ui.card-title>
            <div class="flex items-center gap-2">
                @if ($viewer->hasPermission('admin/seo/sitemap-update'))
                    <x-ui.button variant="outline" onclick="app.confirmAction(this);"
                        data-action="{{ route('admin/seo/sitemap-update') }}">Generate sitemap</x-ui.button>
                @endif
                @if ($viewer->hasPermission('admin/seo/create'))
                    <x-ui.button :href="route('admin/seo/create')" class="pjax"><i class="bx bx-plus"></i> Create</x-ui.button>
                @endif
            </div>
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Url</th>
                        <th>Title</th>
                        <th>Keyword</th>
                        <th>Sitemap</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </x-ui.card-content>
    </x-ui.card>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.dataTable('#data-table', {
                url: '{{ route('admin/seo/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "url"},
                    {data: "title"},
                    {data: "keyword", orderable: false},
                    {data: "sitemap_enable", orderable: false},
                    {data: "action", orderable: false},
                ],
                order: [[1, "asc"]],
            });
        });
    </script>
@endpush
