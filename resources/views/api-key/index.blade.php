@extends('layouts.main')
@section('title')
API Keys
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center py-3 mb-4">
    <h4 class="fw-bold mb-0">API Keys</h4>
    <button type="button" class="btn btn-primary" id="btn-create-key">
        <i class="icon-base bx bx-plus icon-sm"></i> Create Credential
    </button>
</div>

<div class="alert alert-info">
    Every credential has full access to all of your buckets - there is no per-key, per-bucket scoping.
    The secret key is only ever shown once, right after creation or regeneration.
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Access Key</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($apiUsers as $apiUser)
                    <tr id="key-row-{{ $apiUser->id }}">
                        <td><code>{{ $apiUser->access_key }}</code></td>
                        <td>
                            @if ($apiUser->isActive())
                                <span class="badge rounded-pill bg-label-success">Active</span>
                            @else
                                <span class="badge rounded-pill bg-label-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $apiUser->last_used_at ? $general->dateFormat($apiUser->last_used_at) : 'Never' }}</td>
                        <td>{{ $general->dateFormat($apiUser->created_at) }}</td>
                        <td>
                            <button class="btn btn-icon btn-regenerate" data-id="{{ $apiUser->id }}" title="Regenerate Secret"><i class="bx bx-refresh icon-base"></i></button>
                            <button class="btn btn-icon btn-toggle" data-id="{{ $apiUser->id }}" title="Enable/Disable"><i class="bx bx-power-off icon-base"></i></button>
                            <button class="btn btn-icon btn-delete-key" data-id="{{ $apiUser->id }}" title="Delete"><i class="bx bxs-trash icon-base"></i></button>
                        </td>
                    </tr>
                @endforeach
                @if ($apiUsers->isEmpty())
                    <tr><td colspan="5" class="text-center">No API credentials yet.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Secret reveal modal -->
<div class="modal fade" id="secret-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Your Secret Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger">This is shown only once. Copy it now - it cannot be retrieved later.</p>
                <div class="mb-3">
                    <label class="form-label">Access Key</label>
                    <input type="text" class="form-control" id="secret-access-key" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Secret Key</label>
                    <input type="text" class="form-control" id="secret-secret-key" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
documentReady(function () {
    $('#btn-create-key').on('click', function () {
        app.ajaxPost('{{ route("api-keys/create") }}', {}, function (resp) {
            if (!resp.status) { app.showMessage(resp.message, 'danger'); return; }
            $('#secret-access-key').val(resp.access_key);
            $('#secret-secret-key').val(resp.secret_key);
            $('#secret-modal').modal('show');
            setTimeout(function () { window.location.reload(); }, 500);
        });
    });

    $(document).on('click', '.btn-regenerate', function () {
        var id = $(this).data('id');
        app.ajaxConfirm('{{ route("api-keys/regenerate") }}', { id: id }, function (resp) {
            if (!resp.status) { return; }
            $('#secret-access-key').val('');
            $('#secret-secret-key').val(resp.secret_key);
            $('#secret-modal').modal('show');
        });
    });

    $(document).on('click', '.btn-toggle', function () {
        var id = $(this).data('id');
        app.ajaxPost('{{ route("api-keys/toggle-status") }}', { id: id }, function (resp) {
            app.showMessage(resp.message, resp.status ? 'success' : 'danger');
            if (resp.status) { window.location.reload(); }
        });
    });

    $(document).on('click', '.btn-delete-key', function () {
        var id = $(this).data('id');
        app.ajaxConfirm('{{ route("api-keys/delete") }}', { id: id }, function (resp) {
            if (resp.status) { $('#key-row-' + id).remove(); }
        });
    });
});
</script>
@endpush
