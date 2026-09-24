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
        link: { title: 'Login Link', desc: 'We will email you a link to approve this sign-in.' },
    };

    function init(opts) {
        var list = document.getElementById('tfa-method-list');
        var form = document.getElementById('tfa-verify-form');
        var methodInput = document.getElementById('tfa-method');
        var codeLabel = document.getElementById('tfa-code-label');
        var codeInput = document.getElementById('tfa-code');
        var backBtn = document.getElementById('tfa-back');
        var linkPanel = document.getElementById('tfa-link-panel');
        var linkCode = document.getElementById('tfa-link-code');
        var linkStatus = document.getElementById('tfa-link-status');
        var pollTimer = null;

        function stopPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        // The link is approved on any device; this browser polls until it is, then the server sets the session cookie.
        function startLink() {
            linkStatus.style.display = 'none';
            var trust = document.getElementById('trust_device').checked ? 1 : 0;

            app.ajaxPost(opts.sendLinkUrl, { trust_device: trust }, function (response) {
                if (response.status != 1) {
                    app.showMessage(response.message, 'error');
                    return;
                }

                var link = response.data;
                linkCode.textContent = link.code;
                linkPanel.style.display = 'block';

                pollTimer = setInterval(function () {
                    if (new Date(link.expires_at) < new Date()) {
                        stopPolling();
                        linkStatus.textContent = 'This login link has expired. Choose a method again.';
                        linkStatus.style.display = 'block';
                        return;
                    }

                    app.ajaxPost(opts.pollUrl, { request_id: link.request_id, poll_token: link.poll_token }, function (poll) {
                        var state = poll.data && poll.data.state;

                        if (state === 'approved') {
                            stopPolling();
                            window.location.href = opts.dashboardUrl;
                        } else if (state === 'rejected' || state === 'expired') {
                            stopPolling();
                            linkStatus.textContent = state === 'rejected' ? 'The sign-in was rejected.' : 'This login link has expired.';
                            linkStatus.style.display = 'block';
                        }
                    });
                }, 3000);
            });
        }

        function showList() {
            stopPolling();
            linkPanel.style.display = 'none';
            form.style.display = 'none';
            list.style.display = 'block';
        }

        function showForm(method) {
            list.style.display = 'none';

            if (method === 'link') {
                form.style.display = 'none';
                startLink();
                return;
            }

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
                list.innerHTML = '<p class="text-destructive text-sm">No verification methods are available. Please log in again.</p>';
                return;
            }

            list.innerHTML = '';
            methods.forEach(function (method) {
                var info = METHOD_LABELS[method] || { title: method, desc: '' };
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'border-input bg-background hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:hover:bg-input/50 mb-2 flex w-full cursor-pointer flex-col items-start gap-0.5 rounded-md border px-4 py-2 text-left text-sm shadow-xs transition-colors';
                btn.innerHTML = '<strong class="font-medium">' + info.title + '</strong><small class="text-muted-foreground">' + info.desc + '</small>';
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
