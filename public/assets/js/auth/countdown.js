/**
 * Plain-JS 60-second resend countdown, used on the OTP/verify pages.
 * Disables the button while counting down and restores it afterwards.
 * No jQuery, no framework.
 */
var authCountdown = (function () {
    'use strict';

    function attachResend(button, onResend) {
        if (!button) {
            return;
        }

        var seconds = parseInt(button.getAttribute('data-resend-seconds'), 10) || 60;
        var originalText = button.textContent;
        var timer = null;

        function tick(remaining) {
            if (remaining <= 0) {
                clearInterval(timer);
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            button.disabled = true;
            button.textContent = 'Resend in ' + remaining + 's';
        }

        function start() {
            var remaining = seconds;
            tick(remaining);
            timer = setInterval(function () {
                remaining -= 1;
                tick(remaining);
            }, 1000);
        }

        button.addEventListener('click', function () {
            if (button.disabled) {
                return;
            }

            if (typeof onResend === 'function') {
                onResend();
            }

            start();
        });

        start();
    }

    return { attachResend: attachResend };
})();
