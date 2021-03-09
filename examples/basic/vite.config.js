import liveReload from 'vite-plugin-live-reload';

export default {
  root: 'src',
  base: process.env.APP_ENV === 'development' ? '/' : '/dist/',

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
    rollupOptions: {
      input: './src/index.js'
    }
  },

  plugins: [
    liveReload(
      '../public/site/(templates|snippets|controllers|models)/**/*.php'
    )
  ]
};
