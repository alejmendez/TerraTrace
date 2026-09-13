import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite'
import i18n from 'laravel-vue-i18n/vite';
import path from 'path';

const modules = [
  'Core',
  'Auth',
  'Dashboard',
  'Fields',
  'Tasks',
  'Users',
]

const aliasModules = modules.reduce((acc, module) => {
  acc[`@${module}`] = path.resolve(__dirname, `./Modules/${module}/Resources`);
  return acc;
}, {})

const alias = {
  ...aliasModules,
  'ziggy-js': path.resolve(__dirname, './vendor/tightenco/ziggy'),
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
              'resources/css/app.css',
              'resources/js/app.js'
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
        i18n({
          additionalLangPaths: modules.map(module => `Modules/${module}/Lang`)
        }),
    ],
    resolve: {
        alias: alias,
    },
});
