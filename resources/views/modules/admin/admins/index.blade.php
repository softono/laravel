@extends('modules.admin.layouts.main')
@section('title')
    Admin
@endsection
@section('content')
    @php($sessionUser = auth()->user())
    <!-- Content -->

    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Admin</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Admin</li>
            </ol>
        </nav>
    </div>

    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Admin</h5>
            @if ($sessionUser->hasPermission('admin/admin/create'))
                <a href="admin/admin/create" class="btn-primary pjax">
                    <i class="bx bx-plus"></i>
                    <span>Create</span>
                </a>
            @endif
        </div>
        <div class="card-body overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Country</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- / Content -->
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.dataTable('#data-table', {
                url: '{{ route('admin/admin/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "first_name"},
                    {data: "email"},
                    {data: "country"},
                    {data: "phone"},
                    {data: "status"},
                    {data: "action", orderable: false}
                ],
                order: [1, "asc"],
            });
        });
    </script>
@endpush
