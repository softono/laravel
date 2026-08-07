@extends('layouts.main')
@section('title')
API Keys
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center py-3 mb-4">
    <h4 class="fw-bold mb-0">API Keys</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-key-modal">
        <i class="icon-base bx bx-plus icon-sm"></i> Create Credential
    </button>
</div>

<div class="alert alert-info">
    Every credential has full access to all of your buckets - there is no per-key, per-bucket access
    restriction. The bucket field is for your own reference/organization only.
    The secret key is only ever shown once, right after creation or regeneration.
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">S3 Client Configuration</h5>
        <button type="button" class="btn btn-sm btn-label-secondary" id="btn-copy-env">
            <i class="bx bx-copy icon-base"></i> Copy
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Use these values to configure any S3-compatible SDK or CLI against this server. Fill in
            <code>AWS_BUCKET</code>, <code>AWS_ACCESS_KEY_ID</code> and <code>AWS_SECRET_ACCESS_KEY</code>
            with a bucket name and credential created above.
        </p>
        <pre class="mb-0 p-3 bg-lighter rounded" id="env-config-block"><code>AWS_REGION="{{ config('setting.storage_s3_region', 'us-east-1') }}"
AWS_BUCKET=
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=

# S3-compatible endpoint, e.g. http://localhost
AWS_ENDPOINT={{ rtrim(url('/'), '/') }}</code></pre>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Access Key</th>
                    <th>Bucket</th>
                    <th>Status</th>
                    <th>Last Used</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($apiUsers as $apiUser)
                <tr id="key-row-{{ $apiUser->id }}">
                    <td>{{ $apiUser->title ?: '—' }}</td>
                    <td><code>{{ $apiUser->access_key }}</code></td>
                    <td>{{ $apiUser->bucket->name ?? 'All Buckets' }}</td>
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
                        <button class="btn btn-icon btn-regenerate" data-id="{{ $apiUser->id }}"
                            title="Regenerate Secret"><i class="bx bx-refresh icon-base"></i></button>
                        <button class="btn btn-icon btn-toggle" data-id="{{ $apiUser->id }}" title="Enable/Disable"><i
                                class="bx bx-power-off icon-base"></i></button>
                        <button class="btn btn-icon btn-delete-key" data-id="{{ $apiUser->id }}" title="Delete"><i
                                class="bx bxs-trash icon-base"></i></button>
                    </td>
                </tr>
                @endforeach
                @if ($apiUsers->isEmpty())
                <tr>
                    <td colspan="7" class="text-center">No API credentials yet.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Create credential modal -->
<div class="modal fade" id="create-key-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create API Credential</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="create-key-title" placeholder="e.g. Backup script">
                </div>
                <div class="mb-3">
                    <label class="form-label">Bucket</label>
                    <select class="form-select" id="create-key-bucket">
                        <option value="">All Buckets</option>
                        @foreach ($buckets as $bucket)
                        <option value="{{ $bucket->id }}">{{ $bucket->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-create-key">Create</button>
            </div>
        </div>
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
documentReady(function() {
    $('#btn-copy-env').on('click', function() {
        var text = $('#env-config-block').text();
        navigator.clipboard.writeText(text).then(function() {
            app.showMessage('Copied to clipboard.', 'success');
        });
    });

    $('#btn-create-key').on('click', function() {
        app.ajaxPost('{{ route("api-keys/create") }}', {
            title: $('#create-key-title').val(),
            bucket_id: $('#create-key-bucket').val()
        }, function(resp) {
            if (!resp.status) {
                app.showMessage(resp.message, 'danger');
                return;
            }
            $('#create-key-modal').modal('hide');
            $('#create-key-title').val('');
            $('#create-key-bucket').val('');
            $('#secret-access-key').val(resp.access_key);
            $('#secret-secret-key').val(resp.secret_key);
            $('#secret-modal').modal('show');
        });
    });

    $(document).on('click', '.btn-regenerate', function() {
        var id = $(this).data('id');
        app.ajaxConfirm('{{ route("api-keys/regenerate") }}', {
            id: id
        }, function(resp) {
            if (!resp.status) {
                return;
            }
            $('#secret-access-key').val('');
            $('#secret-secret-key').val(resp.secret_key);
            $('#secret-modal').modal('show');
        });
    });

    $(document).on('click', '.btn-toggle', function() {
        var id = $(this).data('id');
        app.ajaxPost('{{ route("api-keys/toggle-status") }}', {
            id: id
        }, function(resp) {
            app.showMessage(resp.message, resp.status ? 'success' : 'danger');
            if (resp.status) {
                window.location.reload();
            }
        });
    });

    $(document).on('click', '.btn-delete-key', function() {
        var id = $(this).data('id');
        app.ajaxConfirm('{{ route("api-keys/delete") }}', {
            id: id
        }, function(resp) {
            if (resp.status) {
                $('#key-row-' + id).remove();
            }
        });
    });
});
</script>
@endpush