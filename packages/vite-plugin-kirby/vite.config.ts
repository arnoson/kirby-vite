import { defineConfig } from 'vite'
import kirby from './src/index'
import legacy from '@vitejs/plugin-legacy'

// An example setup for development of this plugin.

export default defineConfig({
  plugins: [kirby({ kirbyConfigDir: './' }), legacy()],
})
