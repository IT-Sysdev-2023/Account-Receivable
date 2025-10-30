<template>
    <div
        class="min-h-screen bg-gradient-to-b from-[var(--color-bg-primary)] to-[var(--color-bg-secondary)] flex items-center justify-center p-6"
    >
        <div
            class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center"
        >
            <!-- Content Section -->
            <div class="order-2 lg:order-1 text-left">
                <div class="mb-8">
                    <span
                        class="text-[var(--color-text-secondary)] font-mono text-xl font-medium tracking-wider"
                        >ERROR 403</span
                    >
                    <h1
                        class="text-5xl md:text-6xl font-bold text-[var(--color-text-primary)] mt-2 mb-4 hh"
                    >
                        Access Denied
                    </h1>
                    <p
                        class="text-lg text-[var(--color-text-secondary)] max-w-lg leading-relaxed"
                    >
                        You don't have permission to access this page. The gates
                        are firmly shut, but perhaps our confused friend can
                        point you to accessible pages.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <Link
                        :href="route('landing')"
                        class="px-6 py-3 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white disabled:opacity-70 disabled:cursor-not-allowed group hover:-translate-y-0.5"
                    >
                        <div
                            class="relative flex items-center justify-center gap-1"
                        >
                            <span
                                class="transition-transform duration-300 group-hover:rotate-360"
                            >
                                <svg-icon
                                    type="mdi"
                                    :path="mdiHome"
                                    class="w-5 h-5"
                                />
                            </span>

                            Back to Safety
                        </div>
                    </Link>
                </div>

                <!-- Fun Fact -->
                <div
                    class="mt-12 p-5 bg-[var(--color-bg-primary)] border border-[var(--color-border)] bg-opacity-10 backdrop-blur-sm rounded-2xl max-w-md"
                >
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-[var(--color-border)] rounded-lg">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-white"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="text-sm font-medium text-[var(--color-text-primary)] mb-1"
                            >
                                Permission Trivia
                            </p>
                            <p
                                class="text-xs font-medium text-[var(--color-border)]"
                            >
                                In medieval times, forbidden areas were often
                                marked with a simple crossed rope - the origin
                                of the "no entry" symbol we use today.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confused Man Animation Section -->
            <div class="order-1 lg:order-2 flex justify-center lg:justify-end">
                <div class="relative w-full max-w-md">
                    <LottieAnimation
                        :animation-data="forbidden"
                        width="500px"
                        height="500px"
                        :speed="1"
                        class="w-full h-auto"
                    />
                    <div
                        class="absolute inset-0 bg-blue-200/10 rounded-full blur-xl -z-10"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import forbidden from "@/Animation/forbidden.json";
import LottieAnimation from "./Components/LottieAnimation.vue";
import { mdiHome, mdiHelpCircle } from "@mdi/js";
import { onBeforeUnmount, onMounted } from "vue";
import useTheme from "./Composables/useTheme";

defineOptions({
    layout: false,
});

const { setTheme, initTheme } = useTheme();

onMounted(() => {
    // Init theme on load
    initTheme();

    // Listen for changes from other tabs
    const handleStorageChange = (e) => {
        if (e.key === "theme" && e.newValue) {
            setTheme(e.newValue);
        }
    };

    window.addEventListener("storage", handleStorageChange);

    onBeforeUnmount(() => {
        window.removeEventListener("storage", handleStorageChange);
    });
});
</script>

<style>
@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-15px);
    }
}

.hh {
    animation: float 5s ease-in-out infinite;
}
</style>
