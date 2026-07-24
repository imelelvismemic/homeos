import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // Filament custom tema (Tailwind v3, kompajlirana preko PostCSS-a —
            // vidi postcss.config.js). app.js ostaje za eventualni ne-Filament JS.
            input: ['resources/js/app.js', 'resources/js/calendar.js', 'resources/css/filament/app/theme.css'],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
