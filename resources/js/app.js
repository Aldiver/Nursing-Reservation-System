import "./bootstrap";
import "../css/app.css";
import "../css/main.css";
import "floating-vue/dist/style.css";
import "vue3-toastify/dist/index.css";

import { createPinia } from "pinia";
import { useStyleStore } from "@/stores/style.js";

import { darkModeKey, styleKey } from "@/config.js";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import FloatingVue from "floating-vue";
import Notifications from "notiwind";

const appName = import.meta.env.VITE_APP_NAME || "ADZU NURSING RESERVATION";

const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(FloatingVue)
            .use(Notifications)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});

const styleStore = useStyleStore(pinia);

/* App style */
styleStore.setStyle(localStorage[styleKey] ?? "basic");

/* Dark mode */
if (
    (!localStorage[darkModeKey] &&
        window.matchMedia("(prefers-color-scheme: dark)").matches) ||
    localStorage[darkModeKey] === "1"
) {
    styleStore.setDarkMode(true);
}
