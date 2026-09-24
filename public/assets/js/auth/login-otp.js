/**
 * "Login with OTP" panel on the login page: email -> mailed code -> session.
 * Step 1 carries the page's shared reCAPTCHA token (a token is single-use,
 * so step 2 sends none). Follows the flags the server returns
 * (`requires_tfa`, `requires_verification`) like the password form.
 */
(function () {
    'use strict';

    var toggleBtn = document.getElementById('otp-toggle');
    if (!toggleBtn) { return; }

    var panel = document.getElementById('otp-panel');
    var startBlock = document.getElementById('otp-start');
    var verifyBlock = document.getElementById('otp-verify');
    var emailInput = document.getElementById('otp-email');
    var codeInput = document.getElementById('otp-code');
    var captcha = document.getElementById('login-captcha');
    var opts = toggleBtn.dataset;

    var email = '';

    toggleBtn.addEventListener('click', function () {
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });

    function send() {
        var data = { step: 1, email: emailInput.value.trim() };

        if (!data.email) {
            app.showMessage('Please enter your email.', 'error');
            return;
        }

        if (opts.captcha === '1') {
            captcha.style.display = 'block';
            data['g-recaptcha-response'] = window.grecaptcha ? grecaptcha.getResponse() : '';
            if (!data['g-recaptcha-response']) {
                app.showMessage('Please complete the captcha verification', 'error');
                return;
            }
        }

        app.ajaxPost(opts.url, data, function (response) {
            app.showMessage(response.message, response.status ? 'success' : 'error');
            if (response.status != 1) { return; }
            email = data.email;
            startBlock.style.display = 'none';
            verifyBlock.style.display = 'block';
            codeInput.focus();
        });
    }

    document.getElementById('otp-send').addEventListener('click', send);
    document.getElementById('otp-resend').addEventListener('click', send);

    document.getElementById('otp-submit').addEventListener('click', function () {
        var code = codeInput.value.trim();
        if (!code) {
            app.showMessage('Please enter the code.', 'error');
            return;
        }

        var remember = document.getElementById('remember-me');
        app.ajaxPost(opts.url, { step: 2, email: email, otp: code, remember: remember && remember.checked ? 1 : 0 }, function (response) {
            var data = response.data || {};

            if (response.status == 1) {
                window.location.href = data.requires_tfa ? opts.tfaUrl : opts.dashboardUrl;
            } else if (data.requires_verification) {
                window.location.href = opts.verifyAccountUrl + '?code=' + btoa(data.email || email);
            } else {
                app.showMessage(response.message, 'error');
            }
        });
    });
})();
