@extends('layouts.main')
@section('title')
Buckets
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center py-3 mb-4">
    <h4 class="fw-bold mb-0">My Buckets</h4>
    <a href="{{ route('buckets/create') }}" class="btn btn-primary pjax">
        <i class="icon-base bx bx-plus icon-sm"></i> Create Bucket
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="bucket-data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Visibility</th>
                    <th>Objects</th>
                    <th>Storage Used</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    documentReady(function () {
        datatableObj = $('#bucket-data-table').DataTable({
            ajax: dataTableAjax({
                url: '{{ route("buckets/list") }}',
                method: 'post'
            }),
            columns: [
                { data: "name" },
                { data: "visibility" },
                { data: "object_count" },
                { data: "storage_used" },
                { data: "created_at" },
                { data: "action", sortable: false }
            ],
            paging: true,
            searching: true,
        });
    });
</script>
@endpush
