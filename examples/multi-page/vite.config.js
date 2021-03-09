import liveReload from 'vite-plugin-live-reload';
import { resolve } from 'path'
import fs from 'fs'

const root = 'src'
const input = {};
fs.readdirSync(`${root}/templates`).forEach(
  template =>
    (input[template] = resolve(
      __dirname,
      `${root}/templates/${template}/index.js`
    ))
);
input['shared'] = resolve(
  __dirname,
  `${root}/index.js`
)


export default {
  root,
  base: process.env.APP_ENV === 'development' ? '/' : '/dist/',

  resolve: {
    alias: [{ find: '@', replacement: resolve(__dirname, 'src') }]
  },

  server: {
    cors: true,
    // Uncomment if you use a non-localhost dev URL like `my-site.test`:
    // hmr: { host: 'localhost' },
    port: 3000,
    strictPort: true
  },

  build: {
    outDir: '../public/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: { input }
  },

  plugins: [
    liveReload(
      '../public/site/(templates|snippets|controllers|models)/**/*.php'
    )
  ]
};
