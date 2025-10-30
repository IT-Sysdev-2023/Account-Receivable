<template>
    <Head title=" | Landing" />
    <Transition name="fade" @after-leave="goToLogin" mode="out-in">
        <div
            v-if="show"
            class="min-h-screen bg-gradient-to-br from-[var(--color-bg-primary)] to-[var(--color-bg-secondary)] text-[var(--color-text-primary)]"
        >
            <!-- Navigation -->
            <nav class="container mx-auto px-4 sm:px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <img
                            :src="'storage/images/mflogo.png'"
                            alt="Marcela Farms"
                            class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 object-contain"
                        />
                        <span
                            class="text-xl sm:text-2xl md:text-3xl font-bold italic text-[var(--color-text-primary)]"
                            >Marcela Farms</span
                        >
                    </div>
                    <div class="hidden sm:flex space-x-4 md:space-x-6">
                        <a
                            href="#features"
                            @click.prevent="smoothScroll('features')"
                            class="text-sm sm:text-base text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] transition"
                            >Features</a
                        >
                        <a
                            href="#support"
                            @click.prevent="smoothScroll('support')"
                            class="text-sm sm:text-base text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] transition"
                            >Support</a
                        >
                    </div>

                    <div class="text-sm sm:text-md font-sm">Arsys V1.0</div>
                </div>
            </nav>

            <!-- Hero Section -->
            <section
                class="container mx-auto px-4 sm:px-6 py-12 md:py-16 lg:py-24"
            >
                <div class="flex flex-col lg:flex-row items-center">
                    <div class="lg:w-1/2 mb-8 sm:mb-12 lg:mb-0">
                        <span
                            class="inline-block px-3 py-1 sm:px-4 sm:py-2 rounded-full bg-[var(--color-primary)] bg-opacity-10 text-white text-sm sm:text-md font-medium mb-3 sm:mb-4"
                        >
                            Account Receivable System
                        </span>
                        <h1
                            class="text-3xl sm:text-4xl md:text-5xl font-bold text-[var(--color-text-primary)] leading-tight mb-4 sm:mb-6"
                        >
                            Centralized
                            <span class="text-[var(--color-primary)]"
                                >AR System</span
                            >
                        </h1>
                        <p
                            class="text-base sm:text-lg text-[var(--color-text-secondary)] mb-6 sm:mb-8 max-w-lg"
                        >
                            The new and improved Accounts Receivable System for
                            Marcela Farms' business unit operations, designed
                            for modern efficiency.
                        </p>
                        <div class="flex space-x-3 sm:space-x-4">
                            <button
                                @click="startFadeOut"
                                class="px-4 py-2 sm:px-6 sm:py-3 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed group text-sm sm:text-base"
                            >
                                <div
                                    class="relative flex items-center justify-center gap-1"
                                >
                                    Proceed to Login

                                    <span
                                        class="transition-transform duration-300 group-hover:rotate-405"
                                    >
                                        <svg-icon
                                            type="mdi"
                                            :path="mdiNavigationVariantOutline"
                                            class="w-4 h-4 sm:w-5 sm:h-5"
                                        />
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="lg:w-1/2 w-full mt-8 lg:mt-0">
                        <div
                            class="bg-[var(--color-bg-primary)] p-2 rounded-xl"
                        >
                            <div
                                class="relative h-[300px] sm:h-[400px] md:h-[500px] w-full"
                            >
                                <!-- Gallery Container -->
                                <div
                                    class="absolute inset-0 flex space-x-1 sm:space-x-2 overflow-hidden rounded-xl"
                                >
                                    <!-- Gallery Item 1 -->
                                    <div
                                        class="relative h-full transition-[width] duration-500 ease-in-out hover:w-[40%] w-[25%] group rounded-xl sm:rounded-2xl overflow-hidden delayed-width"
                                    >
                                        <img
                                            :src="'storage/images/gallery1.png'"
                                            alt="Poultry Farm"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />

                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-[var(--color-primary)]/50 to-transparent opacity-30"
                                        ></div>

                                        <!-- Slide-up and fade-in text with delay -->
                                        <div
                                            class="absolute bottom-0 left-0 w-full flex justify-center p-4 sm:p-6 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 delay-300 ease-in-out"
                                        >
                                            <h3
                                                class="text-[var(--color-text-primary)] text-center font-bold text-sm sm:text-lg"
                                            >
                                                New Look Application
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Gallery Item 2 -->
                                    <div
                                        class="relative h-full transition-[width] duration-500 ease-in-out hover:w-[40%] w-[25%] group rounded-xl sm:rounded-2xl overflow-hidden delayed-width"
                                    >
                                        <img
                                            :src="'storage/images/gallery2.png'"
                                            alt="Poultry Farm"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />

                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-[var(--color-primary)]/50 to-transparent opacity-30"
                                        ></div>

                                        <!-- Slide-up and fade-in text with delay -->
                                        <div
                                            class="absolute bottom-0 left-0 w-full flex justify-center p-4 sm:p-6 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 delay-300 ease-in-out"
                                        >
                                            <h3
                                                class="text-[var(--color-text-primary)] text-center font-bold text-sm sm:text-lg"
                                            >
                                                Improved Transaction Process
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Gallery Item 3 -->
                                    <div
                                        class="relative h-full transition-[width] duration-500 ease-in-out hover:w-[40%] w-[25%] group rounded-xl sm:rounded-2xl overflow-hidden delayed-width"
                                    >
                                        <img
                                            :src="'storage/images/gallery3.png'"
                                            alt="Poultry Farm"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />

                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-[var(--color-primary)]/50 to-transparent opacity-30"
                                        ></div>

                                        <!-- Slide-up and fade-in text with delay -->
                                        <div
                                            class="absolute bottom-0 left-0 w-full flex justify-center p-4 sm:p-6 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 delay-300 ease-in-out"
                                        >
                                            <h3
                                                class="text-[var(--color-text-primary)] text-center font-bold text-sm sm:text-lg"
                                            >
                                                Enhanced Report Generation
                                            </h3>
                                        </div>
                                    </div>

                                    <!-- Gallery Item 4 -->
                                    <div
                                        class="relative h-full transition-[width] duration-500 ease-in-out hover:w-[40%] w-[25%] group rounded-xl sm:rounded-2xl overflow-hidden delayed-width"
                                    >
                                        <img
                                            :src="'storage/images/gallery4.png'"
                                            alt="Poultry Farm"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        />

                                        <div
                                            class="absolute inset-0 bg-gradient-to-t from-[var(--color-primary)]/50 to-transparent opacity-30"
                                        ></div>

                                        <!-- Slide-up and fade-in text with delay -->
                                        <div
                                            class="absolute bottom-0 left-0 w-full flex justify-center p-4 sm:p-6 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 delay-300 ease-in-out"
                                        >
                                            <h3
                                                class="text-[var(--color-text-primary)] text-center font-bold text-sm sm:text-lg"
                                            >
                                                User Friendly UI
                                            </h3>
                                        </div>
                                    </div>
                                </div>

                                <!-- Decorative Elements -->
                                <div
                                    class="absolute -top-2 sm:-top-4 -left-2 sm:-left-4 w-32 sm:w-64 h-32 sm:h-64 bg-[var(--color-primary)] bg-opacity-10 rounded-lg -z-10"
                                ></div>
                                <div
                                    class="absolute -bottom-2 sm:-bottom-4 -right-2 sm:-right-4 w-32 sm:w-64 h-32 sm:h-64 bg-[var(--color-primary)] bg-opacity-20 rounded-lg -z-10"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section
                id="features"
                class="container mx-auto px-4 sm:px-6 py-12 sm:py-16 bg-[var(--color-bg-primary)] border border-[var(--color-border)] bg-opacity-50 rounded-3xl my-8 sm:my-12"
            >
                <div class="text-center mb-12">
                    <h2
                        class="text-2xl sm:text-3xl font-bold text-[var(--color-text-primary)] mb-3 sm:mb-4"
                    >
                        System Features
                    </h2>
                    <p
                        class="text-base sm:text-lg text-[var(--color-text-secondary)] max-w-2xl mx-auto"
                    >
                        Designed specifically for poultry farm financial
                        management with modern technology
                    </p>
                </div>
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8"
                >
                    <div
                        v-for="feature in features"
                        :key="feature.name"
                        class="p-6 sm:p-8 rounded-xl border border-[var(--color-border)] border-opacity-30 shadow-sm hover:shadow-md transition"
                    >
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 bg-[var(--color-bg-secondary)] bg-opacity-10 rounded-lg flex items-center justify-center mb-4 sm:mb-6"
                        >
                            <svg
                                class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--color-icon)]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    :d="feature.icon"
                                />
                            </svg>
                        </div>
                        <h3
                            class="text-lg sm:text-xl font-semibold text-[var(--color-text-primary)] mb-2 sm:mb-3"
                        >
                            {{ feature.name }}
                        </h3>
                        <p
                            class="text-sm sm:text-base text-[var(--color-text-secondary)]"
                        >
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Support Section -->
            <section
                id="support"
                class="container mx-auto px-4 sm:px-6 py-12 sm:py-16"
            >
                <div
                    class="bg-[var(--color-bg-primary)] border border-[var(--color-border)] bg-opacity-10 rounded-3xl p-6 sm:p-8 md:p-12"
                >
                    <div class="text-center mb-8 sm:mb-12">
                        <h2
                            class="text-2xl sm:text-3xl font-bold text-[var(--color-text-primary)] mb-3 sm:mb-4"
                        >
                            Technical Support
                        </h2>
                        <p
                            class="text-base sm:text-lg text-[var(--color-text-secondary)] max-w-2xl mx-auto"
                        >
                            Our team is available to help you with any questions
                            or issues
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8 max-w-4xl mx-auto"
                    >
                        <div
                            class="p-6 sm:p-8 rounded-xl border border-[var(--color-border)] border-opacity-30"
                        >
                            <h3
                                class="text-lg sm:text-xl font-semibold text-[var(--color-text-primary)] mb-3 sm:mb-4"
                            >
                                Regular Support
                            </h3>
                            <p
                                class="text-sm sm:text-base text-[var(--color-text-secondary)] mb-3 sm:mb-4"
                            >
                                <span class="font-medium"
                                    >Alturas Group of Companies</span
                                ><br />
                                Corp IT: 501-3000 (Local 1953/1847)
                            </p>
                        </div>
                        <div
                            class="p-6 sm:p-8 rounded-xl border border-[var(--color-border)] border-opacity-30"
                        >
                            <h3
                                class="text-lg sm:text-xl font-semibold text-[var(--color-text-primary)] mb-3 sm:mb-4"
                            >
                                Fraud Reporting
                            </h3>
                            <p
                                class="text-sm sm:text-base text-[var(--color-text-secondary)]"
                            >
                                Call/Text:
                                <span class="font-medium"
                                    >09178006736 or 09178006795</span
                                ><br />
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </Transition>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import useTheme from "./Composables/useTheme";
import { mdiNavigationVariantOutline } from "@mdi/js";
import { route } from "../../../vendor/tightenco/ziggy/src/js";

const features = [
    {
        name: "Modern Dashboard",
        description: "Clean interface with intuitive controls for financials",
        icon: "M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z",
    },
    {
        name: "Fast Processing",
        description: "Optimized performance for quick financial operations",
        icon: "M13 10V3L20 10H13Z M4 10V3L11 10H4Z M13 17V10L20 17H13Z M4 17V10L11 17H4Z",
    },
    {
        name: "Secure Data",
        description: "Enhanced security for your financial information",
        icon: "M12 1L3 5V11C3 16.55 6.84 21.74 12 23C17.16 21.74 21 16.55 21 11V5L12 1ZM12 11.99H19C18.47 16.11 15.72 19.78 12 20.93V12H5V6.3L12 3.19V11.99Z",
    },
];

defineOptions({
    layout: false,
});

const show = ref(true);
const ripple = ref(null);

const startFadeOut = (event) => {
    startRipple(event);
    show.value = false;
};

const goToLogin = () => {
    router.visit(route("login"));
};

const smoothScroll = (id) => {
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });

        // Update URL without page reload
        history.pushState(null, null, `#${id}`);
    }
};

const startRipple = (event) => {
    const btn = event.currentTarget;
    const el = ripple.value;

    if (!el) return;

    const rect = btn.getBoundingClientRect();
    const x = rect.width / 2 - 150;
    const y = rect.height / 2 - 150;

    el.style.left = `${x}px`;
    el.style.top = `${y}px`;
    el.style.transition = "none";
    el.style.transform = "scale(0)";
    el.style.opacity = "0.3";

    void el.offsetWidth;

    el.style.transition = "transform 2.6s ease-out, opacity 2.6s ease-out";
    el.style.transform = "scale(4)";
    el.style.opacity = "0";
};

const { setTheme, initTheme } = useTheme();

onMounted(() => {
    initTheme();
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

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.6s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Smooth transitions for interactive elements */
button,
a,
.transition {
    transition: all 0.3s ease;
}

/* Animation for sections when they come into view */
section {
    view-timeline-name: --section;
    view-timeline-axis: block;

    animation-timeline: --section;
    animation-name: fadeInUp;

    animation-range: entry 25% cover 50%;
    animation-fill-mode: both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.delayed-width {
    transition: width 0.5s ease-in-out;
}

.delayed-width:hover {
    transition-delay: 0s;
}

.delayed-width:not(:hover) {
    transition-delay: 500ms; /* wait for text to fade out before width shrinks */
}

/* Responsive adjustments */
@media (max-width: 767px) {
    .hero-content {
        text-align: center;
    }
    .hero-content button {
        margin: 0 auto;
    }
}
</style>
