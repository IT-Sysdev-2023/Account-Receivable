import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp, Head, Link } from "@inertiajs/vue3";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import Layout from "./Layouts/Layout.vue";
import ripple from "./Directives/ripple";

import SvgIcon from "@jamescoyle/vue-icon";

import useTheme from "./Pages/Composables/useTheme";

createInertiaApp({
    title: (title) => `AR System ${title}`,
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        let page = pages[`./Pages/${name}.vue`];
        if (page.default.layout === undefined) {
            page.default.layout = Layout;
        }
        return page;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component("Head", Head) // No need to import Head and Link in every vue file
            .component("Link", Link) //
            .component("svg-icon", SvgIcon)
            .directive("ripple", ripple);

        const { initTheme } = useTheme();
        initTheme();

        app.mount(el);
    },
    progress: {
        // The color of the progress bar...
        color: "var(--color-bg-avatar)",

        // Whether to include the default NProgress styles...
        includeCSS: true,

        // Whether the NProgress spinner will be shown...
        showSpinner: false,
    },
});
