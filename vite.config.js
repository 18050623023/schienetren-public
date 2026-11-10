import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/component.css',
                'resources/js/app.js',
                'resources/js/geofence-map.js',
                'resources/css/filament/portal/theme.css',
                'resources/css/filament/tenant/theme.css',
            ],
            refresh: true,
        }),
    ],
});
