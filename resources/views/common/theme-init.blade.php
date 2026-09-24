{{-- Runs in <head>, before the first paint, so a stored dark/system choice never flashes the light theme. Keep in sync with app.ui.theme in assets/js/app.js. --}}
<script>
    (function () {
        try {
            var mode = localStorage.getItem('app-color-mode');
            var dark = mode === 'dark' || (mode !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            var root = document.documentElement;
            root.classList.toggle('dark', dark);
            root.setAttribute('data-theme', dark ? 'dark' : 'light');
        } catch (e) {}
    })();
</script>
