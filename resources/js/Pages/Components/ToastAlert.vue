<template>
    <transition name="fade-wobble" mode="out-in">
        <div
            v-if="show"
            class="fixed top-5 left-1/2 transform -translate-x-1/2 bg-[var(--color-primary)] text-white px-4 py-3 rounded-md shadow-lg shadow-[#131313a2] z-[9999] w-auto min-w-[250px] max-w-[90vw]"
        >
            <div class="flex items-center justify-center">
                <div class="mr-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="white"
                        class="size-8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                        />
                    </svg>
                </div>
                <span>{{ message }}</span>
                <div
                    class="h-1 bg-white/60 absolute bottom-0 left-0 rounded-bl-md rounded-br-md progress-bar"
                    :style="`--duration: ${duration}ms;`"
                ></div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { watch, ref } from "vue";

const props = defineProps({
    show: Boolean,
    message: String,
});

const duration = 3000; // toast display time in ms
const progress = ref(100);

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            progress.value = 100;
            // reset and drain the bar
            requestAnimationFrame(() => {
                progress.value = 0;
            });
        }
    }
);
</script>

<style scoped>
.progress-bar {
    width: 100%;
    animation: progress var(--duration) linear forwards;
}

@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
.fade-wobble-enter-active,
.fade-wobble-leave-active {
    transition: opacity 0.5s ease;
}

.fade-wobble-enter-from,
.fade-wobble-leave-to {
    opacity: 0;
    transform: translateX(0%) rotate(0deg); /* ← fixes the jump */
}

.fade-wobble-enter-active {
    animation: wobble 0.4s ease;
}

@keyframes wobble {
    0% {
        transform: translateX(0%) rotate(0deg);
    }
    20% {
        transform: translateX(0%) rotate(3deg);
    }
    40% {
        transform: translateX(0%) rotate(-3deg);
    }
    60% {
        transform: translateX(0%) rotate(2deg);
    }
    80% {
        transform: translateX(0%) rotate(-2deg);
    }
    100% {
        transform: translateX(0%) rotate(0deg);
    }
}
</style>
