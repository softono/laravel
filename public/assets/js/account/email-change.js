/**
 * "Change email" card (modules/user/account/component/email_change.blade.php).
 * Three forms shown one at a time; the server keeps the challenge and hands
 * back a handle that each step sends along. `data.restart` means the
 * challenge is gone and the user must begin again.
 */
var emailChange = (function () {
    'use strict';

    var LABELS = {
        otp: 'Email code (sent to your current email)',
        totp: 'Authenticator app code',
        backup: 'Backup code',
    };

    function init(opts) {
        var card = document.getElementById('email-change');
        if (!card) { return; }

        var forms = {};
        card.querySelectorAll('form[data-step]').forEach(function (form) { forms[form.dataset.step] = form; });
        var handle = null;

        function show(step) {
            Object.keys(forms).forEach(function (name) {
                var visible = name === step;
                forms[name].classList.toggle('hidden', !visible);
                forms[name].classList.toggle('grid', visible);
            });
        }

        function restart() {
            handle = null;
            Object.keys(forms).forEach(function (name) { forms[name].reset(); });
            show('start');
        }

        function post(url, data, done) {
            app.ajaxPost(url, data, function (response) {
                if (response.status != 1) {
                    app.showMessage(response.message, 'error');
                    if (response.data && response.data.restart) { restart(); }
                    return;
                }
                done(response);
            });
        }

        function updateMethod() {
            var method = forms.verify.elements.method.value;
            forms.verify.querySelector('[data-code-label]').textContent = LABELS[method] || 'Code';
            forms.verify.querySelector('[data-action="send-otp"]').classList.toggle('hidden', method !== 'otp');
        }

        forms.start.addEventListener('submit', function (event) {
            event.preventDefault();
            var data = { email: forms.start.elements.email.value, password: forms.start.elements.password.value };
            post(opts.startUrl, data, function (response) {
                handle = response.data.handle;
                forms['verify-new'].querySelector('[data-new-email]').textContent = data.email;
                app.showMessage(response.message, 'success');
                show('verify-new');
            });
        });

        forms['verify-new'].addEventListener('submit', function (event) {
            event.preventDefault();
            post(opts.verifyNewUrl, { handle: handle, code: forms['verify-new'].elements.code.value }, function (response) {
                var select = forms.verify.elements.method;
                select.innerHTML = '';
                (response.data.methods || []).forEach(function (method) {
                    var option = document.createElement('option');
                    option.value = method;
                    option.textContent = LABELS[method] || method;
                    select.appendChild(option);
                });
                updateMethod();
                show('verify');
            });
        });

        forms['verify-new'].querySelector('[data-action="resend"]').addEventListener('click', function () {
            post(opts.resendUrl, { handle: handle }, function (response) { app.showMessage(response.message, 'success'); });
        });

        forms.verify.elements.method.addEventListener('change', updateMethod);

        forms.verify.querySelector('[data-action="send-otp"]').addEventListener('click', function () {
            post(opts.sendOtpUrl, {}, function (response) { app.showMessage(response.message, 'success'); });
        });

        forms.verify.addEventListener('submit', function (event) {
            event.preventDefault();
            var data = { handle: handle, method: forms.verify.elements.method.value, code: forms.verify.elements.code.value };
            post(opts.verifyUrl, data, function (response) {
                app.showMessage(response.message, 'success');
                window.location.reload();
            });
        });

        card.querySelectorAll('[data-action="cancel"]').forEach(function (btn) { btn.addEventListener('click', restart); });
    }

    return { init: init };
})();
