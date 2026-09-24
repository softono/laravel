<style>
    .swal2-container.swal2-backdrop-show {
        z-index: 999999999999 !important;
    }
</style>

<div class="w-full" id="verifyOtpModal">
    <div class="modal-header">
        <h5 class="modal-title" id="verifyOtpModalLabel">Verify OTP</h5>
    </div>

    <div class="modal-body">
        <p class="text-center text-sm text-slate-500">
            Enter the 6-digit code from your authenticator app.
        </p>

        <form id="otpForm" method="POST" action="{{ route('otp.confirm') }}">
            <input type="hidden" name="secretKey" value="{{$secretKey}}">
            <input type="hidden" name="id" value="{{$id}}">
            @csrf
            <div class="mb-3">
                <label for="otp_code" class="form-label">OTP Code <span class="text-rose-500">*</span></label>
                <input type="text" name="otp" id="otp_code" class="form-input text-center" maxlength="6" required>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" class="btn-outline" data-modal-dismiss>Cancel</button>
                <button type="submit" class="btn-success">Verify</button>
            </div>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#otpForm').on('submit', function(e) {
        e.preventDefault();

        // Hide OTP form to avoid it showing behind popup
        $('#verifyOtpModal').hide();

        Swal.fire({
            title: 'Confirm OTP',
            text: 'Do you want to verify this OTP code?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post($(this).attr('action'), $(this).serialize(), function(response) {
                    if (response.status === 1) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            if (response.next === 'refresh') {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Failed', response.message || 'Verification failed.', 'error');
                        $('#verifyOtpModal').show();
                    }
                });
            }
        });
    });
});
</script>
