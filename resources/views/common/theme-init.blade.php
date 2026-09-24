{{--
    Runs in <head>. Renders the visitor's colour theme (the `app-theme` cookie, else the admin's `default_theme`
    setting) as CSS variables server-side so there is no flash, then applies light/dark before first paint.
    Keep the mode rule in sync with app.ui.theme in assets/js/app.js.
--}}
@php
    $themeName = \App\Helpers\ThemeCatalog::activeName(request());
    $themeFonts = \App\Helpers\ThemeCatalog::fontHref($themeName);
@endphp
@if ($themeFonts)
    <link id="app-theme-fonts" rel="stylesheet" href="{{ $themeFonts }}">
@endif
<style id="app-theme-vars" data-theme-name="{{ $themeName }}">{!! \App\Helpers\ThemeCatalog::css($themeName) !!}</style>
<script>
    (function () {
        try {
            var cookie = document.cookie.match(/(?:^|; )app-color-mode=([^;]*)/);
            var mode = localStorage.getItem('app-color-mode') || (cookie && cookie[1]);
            var dark = mode === 'dark' || (mode !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            var root = document.documentElement;
            root.classList.toggle('dark', dark);
            root.setAttribute('data-theme', dark ? 'dark' : 'light');
        } catch (e) {}
    })();
</script>
