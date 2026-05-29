import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
    plugins: [vue()],

    resolve: {
      alias: {
        '@': resolve(__dirname, 'src'),
      },
    },

    // Dev server: proxy /api to local Laravel
    server: {
      proxy: {
        '/api': {
          target: env.VITE_API_BASE_URL
            ? env.VITE_API_BASE_URL.replace('/api', '')
            : 'http://localhost:8000',
          changeOrigin: true,
        },
      },
    },

    build: {
      // Produce a single clean dist/ with hashed filenames
      outDir: 'dist',
      emptyOutDir: true,
      sourcemap: false,

      rollupOptions: {
        output: {
          // Split vendor libs into a separate chunk for better caching
          manualChunks: {
            vue:    ['vue', 'vue-router', 'pinia'],
            axios:  ['axios'],
          },
        },
      },
    },

    // Make VITE_API_BASE_URL available inside the app
    define: {
      __API_BASE__: JSON.stringify(env.VITE_API_BASE_URL || '/api'),
    },
  }
})
