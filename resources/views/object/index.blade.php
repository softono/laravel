@extends('layouts.main')
@section('title')
Objects - {{ $bucket->name }}
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center py-3 mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0">{{ $bucket->name }} <small class="text-muted">/ {{ $prefix ?: '' }}</small></h4>
    <div class="d-flex gap-2">
        <input type="text" id="object-search" class="form-control" placeholder="Search objects...">
        <label class="btn btn-primary mb-0">
            <i class="icon-base bx bx-upload icon-sm"></i> Upload
            <input type="file" id="object-upload-input" class="d-none">
        </label>
        <a href="{{ route('buckets') }}" class="btn btn-outline-secondary pjax">Back to Buckets</a>
    </div>
</div>

<nav aria-label="breadcrumb" id="object-breadcrumb" class="mb-3"></nav>

<div class="card">
    <div class="table-responsive">
        <table class="table" id="object-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Type</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="object-table-body">
            </tbody>
        </table>
    </div>
</div>

<!-- Metadata modal -->
<div class="modal fade" id="metadata-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Object Metadata</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="metadata-modal-body"></div>
        </div>
    </div>
</div>

<!-- Move/Copy modal -->
<div class="modal fade" id="move-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Move / Copy Object</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="move-source-key">
                <div class="mb-3">
                    <label class="form-label">Destination Key</label>
                    <input type="text" class="form-control" id="move-dest-key">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" id="btn-copy">Copy</button>
                <button type="button" class="btn btn-primary" id="btn-move">Move</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var bucketName = @json($bucket->name);
    var currentPrefix = @json($prefix);

    function navigate(prefix) {
        window.location = '{{ route("objects") }}?bucket=' + encodeURIComponent(bucketName) + '&prefix=' + encodeURIComponent(prefix);
    }

    function renderBreadcrumb() {
        var parts = currentPrefix.split('/').filter(Boolean);
        var html = '<ol class="breadcrumb mb-0">';
        html += '<li class="breadcrumb-item"><a href="javascript:void(0)" data-prefix="">' + bucketName + '</a></li>';
        var acc = '';
        parts.forEach(function (part) {
            acc += part + '/';
            html += '<li class="breadcrumb-item"><a href="javascript:void(0)" data-prefix="' + acc + '">' + part + '</a></li>';
        });
        html += '</ol>';
        $('#object-breadcrumb').html(html);
        $('#object-breadcrumb a').on('click', function () {
            navigate($(this).data('prefix'));
        });
    }

    function rowActions(file) {
        var html = '<a href="{{ route("objects/download") }}?bucket=' + encodeURIComponent(bucketName) + '&key=' + encodeURIComponent(file.key) + '" class="btn btn-icon" title="Download"><i class="bx bx-download icon-base"></i></a>';
        html += '<button class="btn btn-icon btn-metadata" data-key="' + file.key + '" title="Metadata"><i class="bx bx-info-circle icon-base"></i></button>';
        html += '<button class="btn btn-icon btn-move" data-key="' + file.key + '" title="Move/Copy"><i class="bx bx-transfer icon-base"></i></button>';
        html += '<button class="btn btn-icon btn-delete" data-key="' + file.key + '" title="Delete"><i class="bx bxs-trash icon-base"></i></button>';
        return html;
    }

    function loadObjects(search) {
        var payload = { bucket: bucketName, prefix: currentPrefix };
        if (search) { payload.search = search; }

        app.ajaxPost('{{ route("objects/list") }}', payload, function (resp) {
            var body = $('#object-table-body');
            body.empty();

            (resp.folders || []).forEach(function (folder) {
                body.append(
                    '<tr>' +
                    '<td><a href="javascript:void(0)" class="folder-link" data-prefix="' + folder.prefix + '"><i class="bx bxs-folder icon-base me-1"></i>' + folder.name + '</a></td>' +
                    '<td>-</td><td>Folder</td><td>-</td><td></td>' +
                    '</tr>'
                );
            });

            (resp.files || []).forEach(function (file) {
                body.append(
                    '<tr>' +
                    '<td><i class="bx bx-file icon-base me-1"></i>' + file.name + '</td>' +
                    '<td>' + file.size + '</td>' +
                    '<td>' + (file.mime_type || '-') + '</td>' +
                    '<td>' + file.created_at + '</td>' +
                    '<td class="d-flex">' + rowActions(file) + '</td>' +
                    '</tr>'
                );
            });

            if (!(resp.folders || []).length && !(resp.files || []).length) {
                body.append('<tr><td colspan="5" class="text-center">No objects here yet.</td></tr>');
            }

            $('.folder-link').on('click', function () {
                navigate($(this).data('prefix'));
            });
        });
    }

    $('#object-upload-input').on('change', function () {
        var file = this.files[0];
        if (!file) { return; }
        var formData = new FormData();
        formData.append('file', file);
        formData.append('bucket', bucketName);
        formData.append('prefix', currentPrefix.replace(/\/$/, ''));
        formData.append('_token', CSRF_TOKEN);

        $.ajax({
            url: '{{ route("objects/upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (resp) {
                app.showMessage(resp.message, resp.status ? 'success' : 'danger');
                if (resp.status) { loadObjects(); }
            }
        });
    });

    $('#object-search').on('keyup', function () {
        var val = $(this).val();
        loadObjects(val.length > 1 ? val : null);
    });

    $(document).on('click', '.btn-delete', function () {
        var key = $(this).data('key');
        app.ajaxConfirm('{{ route("objects/delete") }}', { bucket: bucketName, key: key }, function () {
            loadObjects();
        });
    });

    $(document).on('click', '.btn-metadata', function () {
        var key = $(this).data('key');
        app.ajaxGet('{{ route("objects/metadata") }}?bucket=' + encodeURIComponent(bucketName) + '&key=' + encodeURIComponent(key), function (resp) {
            if (!resp.status) { return; }
            var html = '<table class="table"><tbody>';
            html += '<tr><th>Key</th><td>' + resp.data.key + '</td></tr>';
            html += '<tr><th>Original Filename</th><td>' + resp.data.original_filename + '</td></tr>';
            html += '<tr><th>MIME Type</th><td>' + (resp.data.mime_type || '-') + '</td></tr>';
            html += '<tr><th>Size</th><td>' + resp.data.size + '</td></tr>';
            html += '<tr><th>Checksum (MD5)</th><td>' + resp.data.checksum + '</td></tr>';
            html += '<tr><th>Uploaded</th><td>' + resp.data.created_at + '</td></tr>';
            Object.keys(resp.data.metadata || {}).forEach(function (k) {
                html += '<tr><th>x-amz-meta-' + k + '</th><td>' + resp.data.metadata[k] + '</td></tr>';
            });
            html += '</tbody></table>';
            $('#metadata-modal-body').html(html);
            $('#metadata-modal').modal('show');
        });
    });

    $(document).on('click', '.btn-move', function () {
        $('#move-source-key').val($(this).data('key'));
        $('#move-dest-key').val($(this).data('key'));
        $('#move-modal').modal('show');
    });

    function moveOrCopy(move) {
        var sourceKey = $('#move-source-key').val();
        var destKey = $('#move-dest-key').val();
        app.ajaxPost('{{ route("objects/copy") }}', {
            source_bucket: bucketName,
            dest_bucket: bucketName,
            source_key: sourceKey,
            dest_key: destKey,
            move: move ? 1 : 0,
        }, function (resp) {
            app.showMessage(resp.message, resp.status ? 'success' : 'danger');
            if (resp.status) {
                $('#move-modal').modal('hide');
                loadObjects();
            }
        });
    }

    $('#btn-copy').on('click', function () { moveOrCopy(false); });
    $('#btn-move').on('click', function () { moveOrCopy(true); });

    renderBreadcrumb();
    loadObjects();
})();
</script>
@endpush
