import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';
import vue from '@vitejs/plugin-vue';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    plugins: [
        vue(),
        symfonyPlugin({
            stimulus: true
        }),
        viteStaticCopy({
            targets: [
                { src: 'assets/images/*', dest: 'images', rename: { stripBase: 2 } },
                { src: 'node_modules/tinymce/**/*', dest: 'tinymce', rename: { stripBase: 2 } },
                { src: 'assets/tinymce/css/*', dest: 'tinymce/css', rename: { stripBase: 3 } },
                { src: 'assets/tinymce/js/langs/*', dest: 'tinymce/langs', rename: { stripBase: 4 } }
            ]
        })
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js'
        }
    },
    build: {
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: './assets/app.js',
                security: './assets/security.js',
                analytics: './assets/js/analytics.js',
                app_vue_calendar_manage: './assets/vue/app-calendar-manage.ts',
                app_vue_calendar_reservation: './assets/vue/app-calendar-reservation.ts',
                app_vue_calendar_event_detail: './assets/vue/app-calendar-event-detail.ts'
            }
        }
    }
});
