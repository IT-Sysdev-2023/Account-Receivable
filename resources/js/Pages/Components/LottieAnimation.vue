<template>
    <div
        ref="lottieContainer"
        :style="{
            width,
            height,
            ...style,
        }"
    ></div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import lottie from "lottie-web";

// Define props
const props = defineProps({
    animationData: {
        type: Object,
        required: true,
        validator: (val) => val !== null && typeof val === "object",
    },
    width: {
        type: String,
        default: "80px",
    },
    height: {
        type: String,
        default: "80px",
    },
    loop: {
        type: Boolean,
        default: true,
    },
    autoplay: {
        type: Boolean,
        default: true,
    },
    speed: {
        type: Number,
        default: 1,
    },
    style: {
        type: Object,
        default: () => ({}),
    },
});

// Define emitted events
const emit = defineEmits(["complete"]);

const lottieContainer = ref(null);
let animationInstance = null;

const loadAnimation = () => {
    if (typeof window === "undefined") return;

    if (animationInstance) {
        animationInstance.destroy();
    }

    animationInstance = lottie.loadAnimation({
        container: lottieContainer.value,
        renderer: "svg",
        loop: props.loop,
        autoplay: props.autoplay,
        animationData: props.animationData,
    });

    animationInstance.setSpeed(props.speed);

    animationInstance.addEventListener("complete", () => {
        emit("complete");
    });
};

onMounted(() => {
    loadAnimation();
});

onBeforeUnmount(() => {
    if (animationInstance) {
        animationInstance.destroy();
    }
});

// Watch for speed changes
watch(
    () => props.speed,
    (newSpeed) => {
        if (animationInstance) {
            animationInstance.setSpeed(newSpeed);
        }
    }
);

// Watch for animationData changes (optional hot-swap)
watch(
    () => props.animationData,
    () => {
        loadAnimation();
    }
);
</script>
