// resources/js/composables/useTheme.js
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";

export default function useTheme() {
    const page = usePage();
    const theme = ref(localStorage.getItem("theme") || "Light");

    const getProps = () => {
        return page.props?.value || page.props || {};
    };

    const setTheme = async (newTheme) => {
        theme.value = newTheme;
        document.documentElement.className = newTheme;
        localStorage.setItem("theme", newTheme);

        const props = getProps();
        if (props.auth?.user) {
            await axios.post(route("setTheme"), { theme: newTheme });
        }
    };

    const initTheme = () => {
        const saved = localStorage.getItem("theme");
        const props = getProps();

        if (saved) {
            setTheme(saved);
        } else if (props.theme) {
            setTheme(props.theme);
        } else {
            setTheme("Light");
        }
    };

    // Listen for system preference changes
    if (window.matchMedia) {
        const colorSchemeQuery = window.matchMedia(
            "(prefers-color-scheme: Dark)"
        );
        colorSchemeQuery.addEventListener("change", (e) => {
            if (!localStorage.getItem("theme")) {
                setTheme(e.matches ? "Dark" : "Light");
            }
        });
    }

    return {
        theme,
        setTheme,
        initTheme,
    };
}
