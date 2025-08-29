// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from '@rollup/plugin-inject';

export default defineConfig({
  plugins: [
    laravel([
      'resources/css/app.css',
      'resources/js/app.js',
    ]),
    inject({
      $:    'jquery',
      jQuery: 'jquery',
      'window.$':    'jquery',
      'window.jQuery': 'jquery'
    }),
  ],
});
