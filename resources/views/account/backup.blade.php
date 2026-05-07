<div class="container mt-4">
    <div class="card">
        <div class="card-header text-white">
            <h5 class="mb-0">Backup Codes</h5>
        </div>
        <div class="card-body">
            <div class="form-group mb-3">
                <textarea class="form-control" id="backup_code" placeholder="Backup Code Is Not Set!" rows="5" readonly
                    style="font-family: monospace;">{{ str_replace(',', "\n", $backupCode) }}</textarea>
                <small class="text-muted">Copy & save these codes safely. Each used once.</small>
                <button
                    onclick="navigator.clipboard.writeText('{{ str_replace(',', ', ', $backupCode) }}'); app.showMessage('All codes copied!')"
                    class="btn btn-sm btn-outline-primary mt-2">Copy All</button>
            </div>

            <div class="flex items-center justify-end gap-4 p-4 border-t border-t-border-primary">
                <button type="button" class="btn btn-outline-secondary mb-4 mx-3"
                    data-bs-dismiss="modal">Close</button>

                <button onclick="regenerateBackupCodes()" class="btn btn-primary me-3 mb-4 text-white"
                    id="regenerate-btn">
                    <span class="d-none d-sm-block">Regenerate</span>
                    <i class="icon-base bx bx-refresh d-block d-sm-none"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function regenerateBackupCodes() {
        var btn = $('#regenerate-btn');
        btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Regenerating...');

        app.ajaxPost('{{ route('backup-codes-regenerate') }}', {}, function(r) {
            btn.prop('disabled', false).html(
                '<span class="d-none d-sm-block">Regenerate</span><i class="icon-base bx bx-refresh d-block d-sm-none"></i>'
            );

            if (r.status) {
                app.showMessage(r.message);
                $('#backup_code').val(r.backup_code ? r.backup_code.split(',').join('\\n') : '');
            } else {
                app.showMessage(r.message || 'Error', 'error');
            }
        });
    }
</script>
