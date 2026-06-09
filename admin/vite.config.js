import {defineConfig} from 'vite'
import path from 'path'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue()
  ],
  hmr: true,
  resolve: {
    // https://cn.vitejs.dev/config/#resolve-alias
    alias: {
      // 设置路径
      '~': path.resolve(__dirname, './'),
      // 设置别名
      '@': path.resolve(__dirname, './src')
    },
    // https://cn.vitejs.dev/config/#resolve-extensions
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue']
  },
  // vite 相关配置
  server: {
    port: 2800,
    host: true,
    // open: true,
    // proxy: {
    //     // https://cn.vitejs.dev/config/#server-proxy
    //     '/api': {
    //       target: 'https://xxx.com/api',
    //       changeOrigin: true,
    //       rewrite: (p) => p.replace(/^\/api/, '')
    //     }
    // },
  },
  css: {
    postcss: {
      plugins: [
        {
          postcssPlugin: 'internal:charset-removal',
          AtRule: {
            charset: (atRule) => {
              if (atRule.name === 'charset') {
                atRule.remove();
              }
            }
          }
        }
      ],
    },
  },
  build: {
    outDir: 'dist',
    assetsDir: 'admin',
    assetsInlineLimit: 1048 * 1048 * 5,
    chunkSizeWarningLimit: 1024
  }
})
