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
                    // textContent, not innerHTML: the passkey name is user-supplied.
                    [pk.name, pk.device_type, pk.created_at || ''].forEach(function (text) {
                        var td = document.createElement('td');
                        td.textContent = text;
                        tr.appendChild(td);
                    });
                    var actionCell = document.createElement('td');
                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-outline-danger';
                    removeBtn.dataset.id = pk.id;
                    removeBtn.textContent = 'Remove';
                    actionCell.appendChild(removeBtn);
                    tr.appendChild(actionCell);
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
