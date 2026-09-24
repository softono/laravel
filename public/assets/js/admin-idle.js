/**
 * Signs an admin out after two hours without activity, unless they chose
 * "remember me". Activity is shared between tabs through localStorage, so a
 * busy tab keeps the idle ones alive.
 */
var adminIdle = (function () {
    'use strict';

    var TIMEOUT_MS = 120 * 60 * 1000;
    var WRITE_THROTTLE_MS = 30 * 1000;
    var KEY = 'admin_last_activity';

    function read() {
        try {
            return parseInt(localStorage.getItem(KEY), 10) || 0;
        } catch (e) {
            return 0;
        }
    }

    function init(opts) {
        if (opts.remember) { return; }

        var lastWrite = 0;
        var timer = null;

        function schedule(delay) {
            clearTimeout(timer);
            timer = setTimeout(check, delay);
        }

        function check() {
            var idleFor = Date.now() - Math.max(read(), lastWrite);
            if (idleFor >= TIMEOUT_MS) {
                window.location.href = opts.logoutUrl;
            } else {
                schedule(TIMEOUT_MS - idleFor);
            }
        }

        function touch() {
            var now = Date.now();
            if (now - lastWrite < WRITE_THROTTLE_MS) { return; }
            lastWrite = now;
            try { localStorage.setItem(KEY, String(now)); } catch (e) {}
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach(function (name) {
            window.addEventListener(name, touch, { passive: true });
        });

        touch();
        schedule(TIMEOUT_MS);
    }

    return { init: init };
})();
