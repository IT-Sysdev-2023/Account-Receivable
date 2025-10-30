<template>
    <div
        class="min-h-screen bg-gradient-to-b from-[var(--color-bg-primary)] to-[var(--color-bg-secondary)] flex items-center justify-center p-4"
    >
        <div class="w-full max-w-3xl animate-fade-in">
            <div
                class="bg-[var(--color-bg-secondary)] rounded-2xl shadow-xl overflow-hidden transition-all duration-300 hover:shadow-2xl"
            >
                <div class="px-8 pb-8 text-center w-full">
                    <!-- Animated Lottie -->
                    <div class="mx-auto flex items-center justify-center mb-6">
                        <LottieAnimation
                            :animation-data="Farmhouse"
                            width="400px"
                            height="400px"
                            :speed="1.5"
                        />
                    </div>

                    <!-- Header -->
                    <h1
                        class="text-3xl font-bold text-[var(--color-text-primary)] mb-2"
                    >
                        Session Timeout
                    </h1>

                    <!-- Subtext -->
                    <p class="text-[var(--color-text-secondary)] mb-8">
                        Your session has expired due to inactivity. Please sign
                        in again to continue.
                    </p>

                    <!-- Login button -->
                    <Link
                        :href="route('login')"
                        class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-base font-medium text-white bg-red-600 hover:bg-red-700 transition-all duration-200 transform hover:-translate-y-0.5"
                    >
                        Sign In Again
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import LottieAnimation from "./Components/LottieAnimation.vue";
import Farmhouse from "@/Animation/Farmhouse.json";
import { onBeforeUnmount, onMounted, ref } from "vue";
import useTheme from "./Composables/useTheme";

defineOptions({
    layout: false,
});

onMounted(() => {
    localStorage.removeItem("activeUserMenu");
    localStorage.removeItem("activeUserSubmenu");
    localStorage.removeItem("pageTitle");
    localStorage.removeItem("activeMenu");
    localStorage.removeItem("activeSubmenu");
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
/* Animation styles */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}
</style>
