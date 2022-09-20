import { resolve } from 'path'
import kirby from 'vite-plugin-kirby'
import legacy from '@vitejs/plugin-legacy'

export default ({ mode }) => ({
  root: 'src',
  base: mode === 'development' ? '/' : '/dist/',

  build: {
    outDir: resolve(process.cwd(), 'public/dist'),
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(process.cwd(), 'src/index.js'),
    },
  },

  plugins: [legacy(), kirby()],
})
