/**
 * Plain-JS 2FA account-management page (resources/views/account/two-factor.blade.php).
 * No jQuery, no framework - uses the existing app.ajaxPost/ajaxGet helper.
 */
var accountTfa = (function () {
    'use strict';

    function renderBackupCodes(container, codes) {
        container.innerHTML = '';
        codes.forEach(function (code) {
            var col = document.createElement('div');
            col.className = 'col-6 col-md-3';
            col.innerHTML = '<div class="border rounded text-center p-2 font-monospace">' + code + '</div>';
            container.appendChild(col);
        });
    }

    function init(opts) {
        var statusBlock = document.getElementById('tfa-status-block');
        var setupCard = document.getElementById('tfa-setup-card');
        var manageCard = document.getElementById('tfa-manage-card');
        var enableCard = document.getElementById('tfa-enable-card');

        function loadStatus() {
            app.ajaxGet(opts.statusUrl, function (response) {
                var data = response.data || {};
                setupCard.style.display = 'none';

                if (data.enabled) {
                    statusBlock.innerHTML = '<p class="text-success mb-0">Two-factor authentication is enabled.' +
                        (data.backup_codes_remaining ? ' (' + data.backup_codes_remaining + ' backup codes remaining)' : '') +
                        '</p>';
                    manageCard.style.display = 'block';
                    enableCard.style.display = 'none';
                } else {
                    statusBlock.innerHTML = '<p class="text-muted mb-0">Two-factor authentication is disabled.</p>';
                    manageCard.style.display = 'none';
                    enableCard.style.display = 'block';
                }
            });
        }

        document.getElementById('tfa-start-setup').addEventListener('click', function () {
            var password = document.getElementById('tfa-enable-password').value;
            if (!password) {
                app.showMessage('Please enter your current password.', 'error');
                return;
            }

            app.ajaxPost(opts.enableUrl, { password: password }, function (response) {
                if (response.status != 1) {
                    app.showMessage(response.message, 'error');
                    return;
                }

                var data = response.data;
                document.getElementById('tfa-qr-container').innerHTML = data.qr_svg;
                document.getElementById('tfa-secret').textContent = data.secret;

                if (data.backup_codes && data.backup_codes.length) {
                    document.getElementById('tfa-backup-codes-container').style.display = 'block';
                    renderBackupCodes(document.getElementById('tfa-backup-codes-grid'), data.backup_codes);
                }

                enableCard.style.display = 'none';
                setupCard.style.display = 'block';
            });
        });

        document.getElementById('tfa-confirm-setup').addEventListener('click', function () {
            var code = document.getElementById('tfa-setup-code').value;
            if (!code) {
                app.showMessage('Please enter the code from your authenticator app.', 'error');
                return;
            }

            app.ajaxPost(opts.verifySetupUrl, { method: 'totp', code: code }, function (response) {
                app.showMessage(response.message, response.status ? 'success' : 'error');

                if (response.status == 1) {
                    loadStatus();
                }
            });
        });

        document.getElementById('tfa-disable').addEventListener('click', function () {
            var password = document.getElementById('tfa-current-password').value;
            if (!password) {
                app.showMessage('Please enter your current password.', 'error');
                return;
            }

            app.ajaxPost(opts.disableUrl, { password: password }, function (response) {
                app.showMessage(response.message, response.status ? 'success' : 'error');

                if (response.status == 1) {
                    loadStatus();
                }
            });
        });

        document.getElementById('tfa-regenerate-codes').addEventListener('click', function () {
            app.ajaxPost(opts.backupCodesUrl, {}, function (response) {
                if (response.status != 1) {
                    app.showMessage(response.message, 'error');
                    return;
                }

                document.getElementById('tfa-backup-codes-container').style.display = 'block';
                renderBackupCodes(document.getElementById('tfa-backup-codes-grid'), response.data.codes);
                setupCard.style.display = 'block';
                app.showMessage(response.message, 'success');
            });
        });

        // Initial state rendered via Blade template
    }

    return { init: init };
})();
