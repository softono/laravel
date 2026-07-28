/**
 * Plain-JS password show/hide toggle for the [data-password-toggle]
 * eye-icon buttons used on the new auth pages (login/register/reset).
 * No jQuery, no framework - just DOM APIs.
 */
(function () {
    'use strict';

    document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var selector = toggle.getAttribute('data-password-toggle');
            var input = document.querySelector(selector);

            if (!input) {
                return;
            }

            var icon = toggle.querySelector('i');
            var isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';

            if (icon) {
                icon.classList.toggle('bx-hide', !isHidden);
                icon.classList.toggle('bx-show', isHidden);
            }
        });
    });
})();
