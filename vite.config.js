import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import copy from 'rollup-plugin-copy';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/sass/app.scss',

                'resources/js/company.js',
                'resources/js/address.js',
                'resources/js/phone.js',
                'resources/js/google2fa.js',
            ],
            refresh: true,
        }),

        // Copy files
        copy({
            targets: [
                // images
                { src: 'resources/img/**/*', dest: 'public/img' },

                // Copy external JS dependencies
                { src: 'node_modules/jquery/dist/jquery.min.js', dest: 'public/vendor/jquery' },
            ],
            hook: 'writeBundle',
        }),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },

    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
                // Include any global SCSS files here
            },
        },
    },

    build: {
        sourcemap: true,
        minify: 'esbuild',
        terserOptions: {
            compress: {
                drop_console: true,   // remove console logs
                drop_debugger: true, // remove debugger
            },
            format: {
                comments: false,     // remove all comments
            },
        },
    },
});
