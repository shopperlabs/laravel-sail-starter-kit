import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import '@dotenvx/dotenvx/config';

const APP_HOST = process.env.APP_URL ? new URL(process.env.APP_URL).host : 'laravel.local';
const VITE_PORT = 5173;

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: ['app/Livewire/**', 'app/Filament/**', ...refreshPaths],
    }),
    {
      name: 'blade',
      handleHotUpdate({ file, server }) {
        if (file.endsWith('.blade.php')) {
          server.ws.send({
            type: 'full-reload',
            path: '*',
          })
        }
      },
    },
    tailwindcss(),
  ],
  resolve: {
    alias: {
      '@': '/resources/js',
    },
  },
  server: {
    host: '0.0.0.0',
    port: VITE_PORT,
    strictPort: true,
    hmr: {
      host: APP_HOST,
      protocol: 'wss',
    },
  },
});
