import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => {
    // const isDev = mode === "development";

    return {
        plugins: [
            vue(),
            laravel({
                input: ["resources/css/app.css", "resources/js/app.js"],
                refresh: true,
            }),
            tailwindcss(),
        ],
        resolve: {
            alias: {
                "@": "/resources/js",
            },
        },
        server: {
                cors: true,
            },
        // ...(isDev && {
        //     server: {
        //         host: "0.0.0.0",
        //         port: 8000,
        //         hmr: {
        //             host: "10.233.1.60:81",
        //             protocol: "http",
        //             port: 8000,
        //         },
        //         cors: true,
        //     },
        // }),
    };
});
