import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';

export default defineConfig({
    // Reduce log level to minimize warnings
    logLevel: 'warn',
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
        origin: 'http://localhost:5173',
    },
    css: {
        preprocessorOptions: {
            scss: {
                // Suppress deprecation warnings from Sass
                silenceDeprecations: [
                    'legacy-js-api',
                    'import',
                    'global-builtin',
                    'color-functions'
                ],
                // Suppress warnings from dependencies (like Bootstrap)
                quietDeps: true,
                // Reduce verbose output
                verbose: false,
            },
        },
    },
    build: {
        // Suppress chunk size warnings for large bundles
        chunkSizeWarningLimit: 1000,
        // Use esbuild for faster minification (default and faster than terser)
        minify: 'esbuild',
        // Rollup options for better chunking
        rollupOptions: {
            output: {
                // Manual chunking to reduce bundle size and improve caching
                manualChunks: (id) => {
                    // Vendor chunks for better caching
                    if (id.includes('node_modules')) {
                        if (id.includes('vue') || id.includes('vue-router')) {
                            return 'vendor-vue';
                        }
                        if (id.includes('bootstrap')) {
                            return 'vendor-bootstrap';
                        }
                        if (id.includes('chart.js')) {
                            return 'vendor-charts';
                        }
                        if (id.includes('handsontable') || id.includes('simple-datatables')) {
                            return 'vendor-tables';
                        }
                        // Other node_modules
                        return 'vendor';
                    }
                },
            },
        },
    },
});
