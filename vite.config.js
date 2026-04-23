import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/css/home.css',
                'resources/css/profile.css',
                'resources/views/layout/navbar_jelajah.css',
                 'resources/css/card-detail.css',
                
                'resources/css/explore.css',
                'resources/js/explore-search.js'
            ],
            refresh: true,
        }),
    ],
});
