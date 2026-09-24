<div class="modal-header">
    <h4 class="modal-title">Backup Codes</h4>
    <button type="button" class="btn-close" data-modal-dismiss aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <textarea class="form-input font-mono" id="backup_code" placeholder="Backup Code Is Not Set!" rows="5" readonly>{{ str_replace(',', "\n", $backupCode) }}</textarea>
        <small class="text-slate-500">Copy & save these codes safely. Each used once.</small>
        <button
            onclick="navigator.clipboard.writeText('{{ str_replace(',', ', ', $backupCode) }}'); app.showMessage('All codes copied!')"
            class="btn-outline btn-sm mt-2">Copy All</button>
    </div>
</div>
<div class="modal-footer justify-between">
    <button type="button" class="btn-outline" data-modal-dismiss>Close</button>

    <button onclick="regenerateBackupCodes()" class="btn-primary text-white"
        id="regenerate-btn">
        <span class="hidden sm:block">Regenerate</span>
        <i class="bx bx-refresh block sm:hidden"></i>
    </button>
</div>

<script>
    function regenerateBackupCodes() {
        var btn = $('#regenerate-btn');
        btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin mr-2"></i>Regenerating...');

        app.ajaxPost('{{ route('backup-codes-regenerate') }}', {}, function(r) {
            btn.prop('disabled', false).html(
                '<span class="hidden sm:block">Regenerate</span><i class="bx bx-refresh block sm:hidden"></i>'
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
