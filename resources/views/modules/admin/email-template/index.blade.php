@extends('modules.admin.layouts.main')
@section('title')
    Email Templates
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Email Templates</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Email Templates</li>
            </ol>
        </nav>
    </div>

    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Email Templates</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Updated</th>
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
                url: '{{ route('admin/email-template/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "title"},
                    {data: "subject"},
                    {data: "updated_at"},
                    {data: "action", orderable: false},
                ],
                order: [[1, "asc"]],
            });
        });
    </script>
@endpush
