/**
 * Magic login-link panel on auth/login.blade.php. Plain DOM + the
 * existing app.ajaxPost helper - no jQuery plugin, no framework.
 */
(function () {
    'use strict';

    var toggleBtn = document.getElementById('magic-link-toggle');
    var panel = document.getElementById('magic-link-panel');
    var startBlock = document.getElementById('magic-link-start');
    var waitingBlock = document.getElementById('magic-link-waiting');
    var sendBtn = document.getElementById('magic-link-send');
    var emailInput = document.getElementById('magic-link-email');
    var codeEl = document.getElementById('magic-link-code');
    var statusEl = document.getElementById('magic-link-status');

    if (!toggleBtn) {
        return;
    }

    var pollTimer = null;
    var expiresAt = null;
    var pollUrl = toggleBtn.dataset.pollUrl;
    var startUrl = toggleBtn.dataset.startUrl;
    var dashboardUrl = toggleBtn.dataset.dashboardUrl;

    toggleBtn.addEventListener('click', function () {
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function showWaitingError(message) {
        statusEl.textContent = message;
        statusEl.style.display = 'block';
        stopPolling();
    }

    function poll(requestId, pollToken, remember) {
        // Check client-side expiry BEFORE polling, so an expired link
        // doesn't keep burning the 900/300s poll rate-limit budget on
        // dead requests.
        if (expiresAt && new Date(expiresAt) < new Date()) {
            showWaitingError('This login link has expired. Please try again.');
            return;
        }

        app.ajaxPost(pollUrl, {
            request_id: requestId,
            poll_token: pollToken,
            remember: remember ? 1 : 0,
        }, function (response) {
            var state = response.data && response.data.state;

            if (state === 'approved') {
                stopPolling();
                window.location.href = dashboardUrl;
            } else if (state === 'rejected') {
                showWaitingError('Login was rejected.');
            } else if (state === 'expired') {
                showWaitingError('This login link has expired. Please try again.');
            }
            // 'pending' - keep polling silently.
        });
    }

    sendBtn.addEventListener('click', function () {
        var email = emailInput.value;

        if (!email) {
            app.showMessage('Please enter your email.', 'error');
            return;
        }

        var remember = document.getElementById('remember-me') ? document.getElementById('remember-me').checked : false;

        sendBtn.disabled = true;

        app.ajaxPost(startUrl, { email: email, remember: remember ? 1 : 0 }, function (response) {
            sendBtn.disabled = false;

            if (response.status != 1) {
                app.showMessage(response.message, 'error');
                return;
            }

            var data = response.data;
            expiresAt = data.expires_at;
            codeEl.textContent = data.code;
            statusEl.style.display = 'none';

            startBlock.style.display = 'none';
            waitingBlock.style.display = 'block';

            pollTimer = setInterval(function () {
                poll(data.request_id, data.poll_token, remember);
            }, 2000);
        });
    });
})();
