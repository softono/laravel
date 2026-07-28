/**
 * Plain-JS 2FA method picker + verify form for auth/verify-tfa.blade.php.
 * No jQuery, no framework - talks to the server via the existing
 * app.ajaxPost helper (public/assets/js/app.js), which already speaks
 * the {status,message,data} envelope.
 */
var tfaVerify = (function () {
    'use strict';

    var METHOD_LABELS = {
        totp: { title: 'Authenticator App', desc: 'Enter the 6-digit code from your authenticator app.' },
        otp: { title: 'Email Code', desc: 'We will email you a 6-digit code.' },
        backup: { title: 'Backup Code', desc: 'Use one of your saved backup codes.' },
    };

    function init(opts) {
        var list = document.getElementById('tfa-method-list');
        var form = document.getElementById('tfa-verify-form');
        var methodInput = document.getElementById('tfa-method');
        var codeLabel = document.getElementById('tfa-code-label');
        var codeInput = document.getElementById('tfa-code');
        var backBtn = document.getElementById('tfa-back');

        function showList() {
            form.style.display = 'none';
            list.style.display = 'block';
        }

        function showForm(method) {
            list.style.display = 'none';
            form.style.display = 'block';
            methodInput.value = method;
            codeLabel.textContent = (METHOD_LABELS[method] || {}).title || 'Code';
            codeInput.value = '';
            codeInput.focus();

            if (method === 'otp') {
                app.ajaxPost(opts.sendOtpUrl, {}, function (response) {
                    app.showMessage(response.message, response.status ? 'success' : 'error');
                });
            }
        }

        app.ajaxGet(opts.methodsUrl, function (response) {
            var methods = (response.data && response.data.methods) || [];

            if (!methods.length) {
                list.innerHTML = '<p class="text-danger">No verification methods are available. Please log in again.</p>';
                return;
            }

            list.innerHTML = '';
            methods.forEach(function (method) {
                var info = METHOD_LABELS[method] || { title: method, desc: '' };
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-primary d-grid w-100 mb-2 text-start';
                btn.innerHTML = '<strong>' + info.title + '</strong><br><small>' + info.desc + '</small>';
                btn.addEventListener('click', function () {
                    showForm(method);
                });
                list.appendChild(btn);
            });
        });

        backBtn.addEventListener('click', showList);

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!codeInput.value) {
                app.showMessage('Please enter a code.', 'error');
                return;
            }

            var submitBtn = document.getElementById('tfa-submit');
            submitBtn.disabled = true;

            app.ajaxPost(opts.verifyUrl, {
                method: methodInput.value,
                code: codeInput.value,
                trust_device: document.getElementById('trust_device').checked ? 1 : 0,
            }, function (response) {
                submitBtn.disabled = false;

                if (response.status == 1) {
                    window.location.href = opts.dashboardUrl;
                } else {
                    app.showMessage(response.message, 'error');
                }
            });
        });
    }

    return { init: init };
})();
