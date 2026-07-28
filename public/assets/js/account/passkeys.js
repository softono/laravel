/**
 * Account passkey management page (resources/views/account/passkeys.blade.php).
 * Plain DOM + app.ajaxGet/ajaxPost + the passkeyAuth ceremony helper.
 */
var accountPasskeys = (function () {
    'use strict';

    function init(opts) {
        var tbody = document.getElementById('passkey-list');
        var addBtn = document.getElementById('passkey-add');
        var nameInput = document.getElementById('passkey-name');

        if (!passkeyAuth.isSupported()) {
            document.getElementById('passkey-unsupported').style.display = 'block';
            addBtn.disabled = true;
        }

        function load() {
            app.ajaxGet(opts.listUrl, function (response) {
                var passkeys = (response.data && response.data.passkeys) || [];
                tbody.innerHTML = '';

                if (!passkeys.length) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No passkeys added yet.</td></tr>';
                    return;
                }

                passkeys.forEach(function (pk) {
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td>' + pk.name + '</td>' +
                        '<td>' + pk.device_type + '</td>' +
                        '<td>' + (pk.created_at || '') + '</td>' +
                        '<td><button type="button" class="btn btn-sm btn-outline-danger" data-id="' + pk.id + '">Remove</button></td>';
                    tbody.appendChild(tr);
                });

                tbody.querySelectorAll('button[data-id]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        app.ajaxPost(opts.deleteUrl, { id: btn.dataset.id }, function (response) {
                            app.showMessage(response.message, response.status ? 'success' : 'error');
                            if (response.status == 1) {
                                load();
                            }
                        });
                    });
                });
            });
        }

        addBtn.addEventListener('click', function () {
            addBtn.disabled = true;
            var name = nameInput.value || 'Passkey';

            passkeyAuth.register(opts.registerOptionsUrl, opts.registerVerifyUrl, name, function (response) {
                addBtn.disabled = false;
                app.showMessage(response.message, response.status ? 'success' : 'error');

                if (response.status == 1) {
                    nameInput.value = '';
                    load();
                }
            });
        });

        load();
    }

    return { init: init };
})();
