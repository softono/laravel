    <div class="modal-header">
        <h5 class="modal-title" id="qrModalLabel">Set up Authenticator App</h5>
        <button type="button" class="btn-close" data-modal-dismiss aria-label="Close"><i class="bx bx-x text-xl"></i></button>
    </div>

    <div class="px-5 pt-3">
        <p class="text-sm text-slate-500">
            In the Google Authenticator app, tap the <strong>+</strong> and choose <strong>Scan a QR code</strong>.
        </p>
    </div>

    <div class="modal-body text-center">
        <div class="mb-3">
            {!! $qrCode !!}
        </div>

        <p class="inline-block bg-white px-2 text-sm font-semibold text-slate-500">
            OR enter the code manually
        </p>
        <p id="secretKey" class="mt-2 font-semibold text-primary-600"></p>

        <div class="input-group">
            <input type="text" class="form-input text-center" id="secretKeyInput"
                   name="secretKey" value="{{ $secretKey }}">
            <button class="input-group-text" type="button" onclick="copySecretKey()" title="Copy to clipboard">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
    <div class="modal-footer justify-between">
        <button type="button" class="btn-outline" data-modal-dismiss>
            Cancel
        </button>

        <button
            type="button"
            class="btn-primary"
            tabindex="0"
            onclick="app.showModalView('otp.verify?secretKey={{ $secretKey }}')" >
            Next
        </button>
    </div>

<script>
function copySecretKey() {
    const input = document.getElementById('secretKeyInput');
    const secretKey = input.value;

    navigator.clipboard.writeText(secretKey).then(() => {
        $.ajax({
            url: '/copy.secret.key',
            method: 'POST',
            data: {
                secretKey: secretKey,
                _token: '{{ csrf_token() }}'
            }
        });
    }).catch((err) => {
        console.error('Clipboard error:', err);
    });
}
</script>
