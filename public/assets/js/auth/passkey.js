/**
 * WebAuthn passkey ceremonies (registration + login), plain vanilla JS.
 * navigator.credentials.create()/get() need challenge/user.id as
 * ArrayBuffers while the server sends/expects base64url strings -
 * base64urlToBuffer/bufferToBase64url below handle that in both
 * directions, and publicKeyCredentialToJSON() is used for both
 * registration and assertion responses.
 */
var passkeyAuth = (function () {
    'use strict';

    function base64urlToBuffer(base64url) {
        var base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        var padded = base64 + '=='.slice(0, (4 - (base64.length % 4)) % 4);
        var binary = atob(padded);
        var buffer = new ArrayBuffer(binary.length);
        var bytes = new Uint8Array(buffer);
        for (var i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return buffer;
    }

    function bufferToBase64url(buffer) {
        var bytes = new Uint8Array(buffer);
        var binary = '';
        for (var i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    function publicKeyCredentialToJSON(credential) {
        var json = {
            id: credential.id,
            rawId: bufferToBase64url(credential.rawId),
            type: credential.type,
            clientExtensionResults: credential.getClientExtensionResults ? credential.getClientExtensionResults() : {},
        };

        var response = credential.response;
        json.response = { clientDataJSON: bufferToBase64url(response.clientDataJSON) };

        if (response.attestationObject) {
            // Registration ceremony.
            json.response.attestationObject = bufferToBase64url(response.attestationObject);
            if (response.getTransports) {
                json.response.transports = response.getTransports();
            }
        } else {
            // Authentication ceremony.
            json.response.authenticatorData = bufferToBase64url(response.authenticatorData);
            json.response.signature = bufferToBase64url(response.signature);
            json.response.userHandle = response.userHandle ? bufferToBase64url(response.userHandle) : null;
        }

        return json;
    }

    function optionsFromServer(options) {
        var opts = Object.assign({}, options);
        opts.challenge = base64urlToBuffer(options.challenge);

        if (opts.user) {
            opts.user = Object.assign({}, opts.user, { id: base64urlToBuffer(options.user.id) });
        }

        if (opts.excludeCredentials) {
            opts.excludeCredentials = opts.excludeCredentials.map(function (c) {
                return Object.assign({}, c, { id: base64urlToBuffer(c.id) });
            });
        }

        if (opts.allowCredentials) {
            opts.allowCredentials = opts.allowCredentials.map(function (c) {
                return Object.assign({}, c, { id: base64urlToBuffer(c.id) });
            });
        }

        return opts;
    }

    function isSupported() {
        return !!(navigator.credentials && window.PublicKeyCredential);
    }

    function register(optionsUrl, verifyUrl, name, done) {
        if (!isSupported()) {
            done({ status: 0, message: 'Passkeys are not supported in this browser.' });
            return;
        }

        app.ajaxGet(optionsUrl, function (response) {
            if (response.status != 1) {
                done(response);
                return;
            }

            navigator.credentials.create({ publicKey: optionsFromServer(response.data.options) })
                .then(function (credential) {
                    app.ajaxPost(verifyUrl, { credential: publicKeyCredentialToJSON(credential), name: name }, done);
                })
                .catch(function (err) {
                    done({ status: 0, message: 'Passkey registration was cancelled or failed: ' + err.message });
                });
        });
    }

    function login(optionsUrl, verifyUrl, done) {
        if (!isSupported()) {
            done({ status: 0, message: 'Passkeys are not supported in this browser.' });
            return;
        }

        app.ajaxPost(optionsUrl, {}, function (response) {
            if (response.status != 1) {
                done(response);
                return;
            }

            navigator.credentials.get({ publicKey: optionsFromServer(response.data.options) })
                .then(function (credential) {
                    app.ajaxPost(verifyUrl, { credential: publicKeyCredentialToJSON(credential) }, done);
                })
                .catch(function (err) {
                    done({ status: 0, message: 'Passkey sign-in was cancelled or failed: ' + err.message });
                });
        });
    }

    return { register: register, login: login, isSupported: isSupported };
})();
