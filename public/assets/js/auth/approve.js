/**
 * login/approve page (auth/login-approve.blade.php). Plain DOM + the
 * existing app.ajaxPost helper.
 */
var loginApprove = (function () {
    'use strict';

    function show(id) {
        ['approve-loading', 'approve-content', 'approve-result', 'approve-invalid'].forEach(function (elId) {
            document.getElementById(elId).style.display = elId === id ? 'block' : 'none';
        });
    }

    function init(opts) {
        if (!opts.id || !opts.token) {
            show('approve-invalid');
            return;
        }

        var url = opts.infoUrl + '?id=' + encodeURIComponent(opts.id) + '&token=' + encodeURIComponent(opts.token);

        app.ajaxGet(url, function (response) {
            if (response.status != 1) {
                show('approve-invalid');
                return;
            }

            var data = response.data;
            document.getElementById('approve-email').textContent = data.email;
            document.getElementById('approve-device').textContent = [data.device_name || 'Unknown device', data.location].filter(Boolean).join(' · ');
            document.getElementById('approve-code').textContent = data.code;
            show('approve-content');

            function respond(action) {
                app.ajaxPost(opts.infoUrl, { id: opts.id, token: opts.token, action: action }, function (resp) {
                    document.getElementById('approve-result-message').textContent = resp.message;
                    document.getElementById('approve-result-message').className = resp.status == 1 ? 'mb-0 text-success' : 'mb-0 text-danger';
                    show('approve-result');
                });
            }

            document.getElementById('approve-btn').addEventListener('click', function () {
                respond('approve');
            });
            document.getElementById('reject-btn').addEventListener('click', function () {
                respond('reject');
            });
        });
    }

    return { init: init };
})();
