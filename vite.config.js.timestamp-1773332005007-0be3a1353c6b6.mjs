// vite.config.js
import { defineConfig } from "file:///D:/Xampp/htdocs/cms/node_modules/vite/dist/node/index.js";
import laravel from "file:///D:/Xampp/htdocs/cms/node_modules/laravel-vite-plugin/dist/index.js";
import vue from "file:///D:/Xampp/htdocs/cms/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import { VitePWA } from "file:///D:/Xampp/htdocs/cms/node_modules/vite-plugin-pwa/dist/index.js";
import path from "path";
var __vite_injected_original_dirname = "D:\\Xampp\\htdocs\\cms";
var vite_config_default = defineConfig({
  resolve: {
    alias: {
      "@": path.resolve(__vite_injected_original_dirname, "./resources/js")
    }
  },
  server: {
    host: "127.0.0.1"
  },
  plugins: [
    laravel({
      input: "resources/js/app.js",
      refresh: true
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    VitePWA({
      registerType: "autoUpdate",
      outDir: "public",
      buildBase: "/",
      scope: "/",
      injectRegister: "auto",
      manifest: {
        name: "CMS Qu\u1EA3n tr\u1ECB H\u1ED9i Th\xE1nh",
        short_name: "CMS HT",
        description: "\u1EE8ng d\u1EE5ng qu\u1EA3n tr\u1ECB v\xE0 \u0111i\u1EC1u h\xE0nh trung t\xE2m d\xE0nh cho H\u1ED9i Th\xE1nh",
        theme_color: "#1e40af",
        background_color: "#f8fafc",
        display: "standalone",
        orientation: "portrait",
        start_url: "/",
        icons: [
          {
            src: "/icon-192.png",
            sizes: "192x192",
            type: "image/png"
          },
          {
            src: "/icon-512.png",
            sizes: "512x512",
            type: "image/png"
          }
        ]
      },
      workbox: {
        cleanupOutdatedCaches: true,
        globDirectory: "public",
        globPatterns: ["**/*.{js,css,html,ico,png,svg,woff2}"],
        navigateFallback: "/"
      }
    })
  ]
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJEOlxcXFxYYW1wcFxcXFxodGRvY3NcXFxcY21zXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJEOlxcXFxYYW1wcFxcXFxodGRvY3NcXFxcY21zXFxcXHZpdGUuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9EOi9YYW1wcC9odGRvY3MvY21zL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IHsgZGVmaW5lQ29uZmlnIH0gZnJvbSAndml0ZSc7XG5pbXBvcnQgbGFyYXZlbCBmcm9tICdsYXJhdmVsLXZpdGUtcGx1Z2luJztcbmltcG9ydCB2dWUgZnJvbSAnQHZpdGVqcy9wbHVnaW4tdnVlJztcbmltcG9ydCB7IFZpdGVQV0EgfSBmcm9tICd2aXRlLXBsdWdpbi1wd2EnO1xuaW1wb3J0IHBhdGggZnJvbSAncGF0aCc7XG5cbmV4cG9ydCBkZWZhdWx0IGRlZmluZUNvbmZpZyh7XG4gICAgcmVzb2x2ZToge1xuICAgICAgICBhbGlhczoge1xuICAgICAgICAgICAgJ0AnOiBwYXRoLnJlc29sdmUoX19kaXJuYW1lLCAnLi9yZXNvdXJjZXMvanMnKSxcbiAgICAgICAgfSxcbiAgICB9LFxuICAgIHNlcnZlcjoge1xuICAgICAgICBob3N0OiAnMTI3LjAuMC4xJyxcbiAgICB9LFxuICAgIHBsdWdpbnM6IFtcbiAgICAgICAgbGFyYXZlbCh7XG4gICAgICAgICAgICBpbnB1dDogJ3Jlc291cmNlcy9qcy9hcHAuanMnLFxuICAgICAgICAgICAgcmVmcmVzaDogdHJ1ZSxcbiAgICAgICAgfSksXG4gICAgICAgIHZ1ZSh7XG4gICAgICAgICAgICB0ZW1wbGF0ZToge1xuICAgICAgICAgICAgICAgIHRyYW5zZm9ybUFzc2V0VXJsczoge1xuICAgICAgICAgICAgICAgICAgICBiYXNlOiBudWxsLFxuICAgICAgICAgICAgICAgICAgICBpbmNsdWRlQWJzb2x1dGU6IGZhbHNlLFxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB9LFxuICAgICAgICB9KSxcbiAgICAgICAgVml0ZVBXQSh7XG4gICAgICAgICAgICByZWdpc3RlclR5cGU6ICdhdXRvVXBkYXRlJyxcbiAgICAgICAgICAgIG91dERpcjogJ3B1YmxpYycsXG4gICAgICAgICAgICBidWlsZEJhc2U6ICcvJyxcbiAgICAgICAgICAgIHNjb3BlOiAnLycsXG4gICAgICAgICAgICBpbmplY3RSZWdpc3RlcjogJ2F1dG8nLFxuICAgICAgICAgICAgbWFuaWZlc3Q6IHtcbiAgICAgICAgICAgICAgICBuYW1lOiAnQ01TIFF1XHUxRUEzbiB0clx1MUVDQiBIXHUxRUQ5aSBUaFx1MDBFMW5oJyxcbiAgICAgICAgICAgICAgICBzaG9ydF9uYW1lOiAnQ01TIEhUJyxcbiAgICAgICAgICAgICAgICBkZXNjcmlwdGlvbjogJ1x1MUVFOG5nIGRcdTFFRTVuZyBxdVx1MUVBM24gdHJcdTFFQ0Igdlx1MDBFMCBcdTAxMTFpXHUxRUMxdSBoXHUwMEUwbmggdHJ1bmcgdFx1MDBFMm0gZFx1MDBFMG5oIGNobyBIXHUxRUQ5aSBUaFx1MDBFMW5oJyxcbiAgICAgICAgICAgICAgICB0aGVtZV9jb2xvcjogJyMxZTQwYWYnLFxuICAgICAgICAgICAgICAgIGJhY2tncm91bmRfY29sb3I6ICcjZjhmYWZjJyxcbiAgICAgICAgICAgICAgICBkaXNwbGF5OiAnc3RhbmRhbG9uZScsXG4gICAgICAgICAgICAgICAgb3JpZW50YXRpb246ICdwb3J0cmFpdCcsXG4gICAgICAgICAgICAgICAgc3RhcnRfdXJsOiAnLycsXG4gICAgICAgICAgICAgICAgaWNvbnM6IFtcbiAgICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICAgICAgc3JjOiAnL2ljb24tMTkyLnBuZycsXG4gICAgICAgICAgICAgICAgICAgICAgICBzaXplczogJzE5MngxOTInLFxuICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ2ltYWdlL3BuZydcbiAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICAgICAgc3JjOiAnL2ljb24tNTEyLnBuZycsXG4gICAgICAgICAgICAgICAgICAgICAgICBzaXplczogJzUxMng1MTInLFxuICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ2ltYWdlL3BuZydcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIF1cbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB3b3JrYm94OiB7XG4gICAgICAgICAgICAgICAgY2xlYW51cE91dGRhdGVkQ2FjaGVzOiB0cnVlLFxuICAgICAgICAgICAgICAgIGdsb2JEaXJlY3Rvcnk6ICdwdWJsaWMnLFxuICAgICAgICAgICAgICAgIGdsb2JQYXR0ZXJuczogWycqKi8qLntqcyxjc3MsaHRtbCxpY28scG5nLHN2Zyx3b2ZmMn0nXSxcbiAgICAgICAgICAgICAgICBuYXZpZ2F0ZUZhbGxiYWNrOiAnLycsXG4gICAgICAgICAgICB9XG4gICAgICAgIH0pXG4gICAgXSxcbn0pO1xuIl0sCiAgIm1hcHBpbmdzIjogIjtBQUFtUCxTQUFTLG9CQUFvQjtBQUNoUixPQUFPLGFBQWE7QUFDcEIsT0FBTyxTQUFTO0FBQ2hCLFNBQVMsZUFBZTtBQUN4QixPQUFPLFVBQVU7QUFKakIsSUFBTSxtQ0FBbUM7QUFNekMsSUFBTyxzQkFBUSxhQUFhO0FBQUEsRUFDeEIsU0FBUztBQUFBLElBQ0wsT0FBTztBQUFBLE1BQ0gsS0FBSyxLQUFLLFFBQVEsa0NBQVcsZ0JBQWdCO0FBQUEsSUFDakQ7QUFBQSxFQUNKO0FBQUEsRUFDQSxRQUFRO0FBQUEsSUFDSixNQUFNO0FBQUEsRUFDVjtBQUFBLEVBQ0EsU0FBUztBQUFBLElBQ0wsUUFBUTtBQUFBLE1BQ0osT0FBTztBQUFBLE1BQ1AsU0FBUztBQUFBLElBQ2IsQ0FBQztBQUFBLElBQ0QsSUFBSTtBQUFBLE1BQ0EsVUFBVTtBQUFBLFFBQ04sb0JBQW9CO0FBQUEsVUFDaEIsTUFBTTtBQUFBLFVBQ04saUJBQWlCO0FBQUEsUUFDckI7QUFBQSxNQUNKO0FBQUEsSUFDSixDQUFDO0FBQUEsSUFDRCxRQUFRO0FBQUEsTUFDSixjQUFjO0FBQUEsTUFDZCxRQUFRO0FBQUEsTUFDUixXQUFXO0FBQUEsTUFDWCxPQUFPO0FBQUEsTUFDUCxnQkFBZ0I7QUFBQSxNQUNoQixVQUFVO0FBQUEsUUFDTixNQUFNO0FBQUEsUUFDTixZQUFZO0FBQUEsUUFDWixhQUFhO0FBQUEsUUFDYixhQUFhO0FBQUEsUUFDYixrQkFBa0I7QUFBQSxRQUNsQixTQUFTO0FBQUEsUUFDVCxhQUFhO0FBQUEsUUFDYixXQUFXO0FBQUEsUUFDWCxPQUFPO0FBQUEsVUFDSDtBQUFBLFlBQ0ksS0FBSztBQUFBLFlBQ0wsT0FBTztBQUFBLFlBQ1AsTUFBTTtBQUFBLFVBQ1Y7QUFBQSxVQUNBO0FBQUEsWUFDSSxLQUFLO0FBQUEsWUFDTCxPQUFPO0FBQUEsWUFDUCxNQUFNO0FBQUEsVUFDVjtBQUFBLFFBQ0o7QUFBQSxNQUNKO0FBQUEsTUFDQSxTQUFTO0FBQUEsUUFDTCx1QkFBdUI7QUFBQSxRQUN2QixlQUFlO0FBQUEsUUFDZixjQUFjLENBQUMsc0NBQXNDO0FBQUEsUUFDckQsa0JBQWtCO0FBQUEsTUFDdEI7QUFBQSxJQUNKLENBQUM7QUFBQSxFQUNMO0FBQ0osQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
