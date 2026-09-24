import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';


// Relative asset URLs: the app is served from a sub-path (/laravel/laravel/public), so fonts referenced from the
// built CSS must resolve next to the CSS file, not against the domain root.
export default defineConfig({
    base: './',
    plugins: [
        laravel({
            input: ['resources/css/app.css'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    esbuild: {
        keepNames: true,
    }
});
