import { defineConfig, normalizePath } from 'vite'
import tailwind from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin'
import typography from '@tailwindcss/typography'
import fs from 'node:fs'
import path from 'node:path'

const themeRoot = __dirname

const baseViewsReal = path.resolve(themeRoot, 'resources/views')
const tp = (p) => normalizePath(p) 

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/styles/app.css',
        'resources/scripts/app.js',
        'resources/styles/editor.css',
        'resources/scripts/editor.js',
        'resources/scripts/gallery.js',
        'resources/scripts/single-slider.js',
      ],
      refresh: true,
      buildDirectory: 'build/.vite',
    }),

    tailwind({
      config: {
        content: [
          tp(path.resolve(themeRoot, 'app/**/*.php')),
          tp(path.join(baseViewsReal, '**/*.blade.php')),
        ],
        plugins: [typography],
      },
    }),
  ],
  build: {
    outDir: 'public/build',
    manifest: 'manifest.json',
    // Zapobieganie nadmiernej optymalizacji GSAP
    rollupOptions: {
      output: {
        manualChunks: {
          'gsap': ['gsap', 'gsap/ScrollTrigger']
        }
      }
    },
    // Minimalna optymalizacja dla lepszej kompatybilności
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: false, // Zachowaj console.log dla debugowania
        pure_funcs: [] // Nie usuwaj żadnych funkcji
      },
      mangle: {
        keep_classnames: true, // Zachowaj nazwy klas
        keep_fnames: true // Zachowaj nazwy funkcji (ważne dla GSAP)
      }
    }
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    cors: true,
    origin: 'http://localhost:5173',
    hmr: {
      host: 'localhost',
    },
  },
})
