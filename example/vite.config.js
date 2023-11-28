import { resolve } from 'path'
import kirby from '../packages/vite-plugin-kirby'

// console.log(resolve(process.cwd(), 'src/index.js'))

export default ({ mode }) => ({
  root: 'src',
  base: mode === 'development' ? '/' : '/dist/',

  build: {
    outDir: resolve(process.cwd(), 'dist'),
    emptyOutDir: true,
    rollupOptions: {
      input: ['src/index.js', 'src/panel.js'],
    },
  },

  plugins: [kirby()],
})
