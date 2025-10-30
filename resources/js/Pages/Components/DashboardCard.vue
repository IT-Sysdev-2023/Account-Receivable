<template>
    <div
        class="relative rounded-xl shadow-lg flex flex-col transition-all duration-300 ease-in-out hover:translate-y-[-5px] hover:z-20 z-10 h-full border border-[var(--color-border)]"
    >
        <!-- Image Background Section -->
        <div
            class="relative h-20 sm:h-24 w-full bg-[var(--color-bg-primary)] rounded-t-xl overflow-hidden"
        >
            <!-- Background Image -->
            <img
                :src="backgroundImage"
                class="absolute inset-0 w-full h-full object-cover"
                alt="Card background"
            />
            <!-- Gradient Overlay -->
            <div
                class="absolute inset-0 w-full h-full object-cover bg-gradient-to-t from-[var(--color-bg-secondary)] to-transparent"
            ></div>

            <!-- Icon Badge -->
            <div
                class="absolute bottom-3 sm:bottom-4 left-3 sm:left-4 p-2 sm:p-3 rounded-lg shadow-md bg-[var(--color-bg-primary)]/90 backdrop-blur-sm"
            >
                <svg-icon
                    type="mdi"
                    :path="getIconPath"
                    class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--color-icon)]"
                />
            </div>
        </div>

        <!-- Content Section -->
        <div
            class="flex-1 px-4 sm:px-5 pb-4 sm:pb-5 bg-[var(--color-bg-secondary)] rounded-b-xl flex flex-col"
        >
            <!-- Stats Content -->
            <div class="mb-1 sm:mb-2 text-right">
                <p
                    class="text-xs sm:text-sm font-medium text-[var(--color-text-secondary)]"
                >
                    {{ label }}
                </p>
                <p
                    class="text-xl sm:text-2xl font-bold text-[var(--color-text-primary)]"
                >
                    {{ prefix }} <span ref="counterElement">0.00</span>
                </p>
            </div>

            <!-- Footer with Dropdown -->
            <div
                class="flex items-center justify-between gap-1 sm:gap-2 text-[10px] sm:text-xs text-[var(--color-text-secondary)] pt-2 sm:pt-3 border-t border-[var(--color-border)]/50"
            >
                <div class="flex gap-0.5 sm:gap-1 items-center">
                    <svg-icon
                        type="mdi"
                        :path="mdiClock"
                        class="w-3 h-3 sm:w-4 sm:h-4 text-[var(--color-icon)]"
                    />
                    <span>Updated {{ props.lastUpdated }}</span>
                </div>

                <div
                    class="relative"
                    @mouseenter="dropdownOpen = true"
                    @mouseleave="dropdownOpen = false"
                >
                    <button
                        class="flex items-center justify-between w-full bg-[var(--color-bg-primary)]/10 text-[var(--color-text-primary)] text-[10px] sm:text-xs rounded px-1.5 sm:px-2 py-0.5 sm:py-1 border border-[var(--color-border)] hover:bg-[var(--color-bg-primary)]/15 focus:outline-none focus:ring-1 focus:ring-[var(--color-border)]/20 transition-colors duration-150"
                    >
                        <span>{{ selectedPeriod }}</span>
                        <svg-icon
                            type="mdi"
                            :path="dropdownOpen ? mdiChevronUp : mdiChevronDown"
                            class="w-3 h-3 sm:w-4 sm:h-4 ml-1 sm:ml-2 text-[var(--color-icon)]"
                        />
                    </button>

                    <transition
                        enter-active-class="transition duration-100 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <ul
                            v-show="dropdownOpen"
                            class="absolute mt-0.5 sm:mt-1 w-full min-w-[120px] border border-[var(--color-primary)] rounded shadow-lg bg-[var(--color-bg-secondary)]/90 backdrop-blur-sm z-30"
                        >
                            <li
                                v-for="period in periods"
                                :key="period"
                                class="px-2 py-1 sm:px-3 sm:py-2 text-[10px] sm:text-xs text-[var(--color-text-primary)] hover:bg-[var(--color-primary)]/20 cursor-pointer transition-colors duration-150 border-b border-[var(--color-border)]/20"
                                @click="selectPeriod(period)"
                            >
                                {{ period }}
                            </li>
                        </ul>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import SvgIcon from "@jamescoyle/vue-icon";
import {
    mdiInvoiceTextPlus,
    mdiReceiptTextMinus,
    mdiInvoiceTextArrowRight,
    mdiInvoiceTextCheck,
    mdiClock,
    mdiCashClock,
    mdiChevronDown,
    mdiChevronUp,
} from "@mdi/js";

const iconMap = {
    invoice: mdiInvoiceTextPlus,
    adjustment: mdiReceiptTextMinus,
    payment: mdiInvoiceTextArrowRight,
    balance: mdiInvoiceTextCheck,
    check: mdiCashClock,
};

const props = defineProps({
    iconType: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    prefix: {
        type: String,
        default: "",
    },
    values: {
        type: Object,
        required: true,
    },
    periods: {
        type: Array,
        default: () => ["Last 7 days", "Last 30 days", "Overall Total"],
    },
    lastUpdated: {
        type: String,
        default: "a few seconds ago",
    },
    isCurrency: {
        type: Boolean,
        default: false,
    },
    color: {
        type: String,
        default: "lime",
        validator: (value) => {
            return [
                "slate",
                "gray",
                "zinc",
                "neutral",
                "stone",
                "red",
                "orange",
                "amber",
                "yellow",
                "lime",
                "green",
                "emerald",
                "teal",
                "cyan",
                "sky",
                "blue",
                "indigo",
                "violet",
                "purple",
                "fuchsia",
                "pink",
                "rose",
            ].includes(value);
        },
    },
    backgroundImage: {
        type: String,
        default: "/storage/images/card-bg.jpg", // Provide a default background image
    },
});

//GET ICON PATH
const getIconPath = computed(() => {
    return iconMap[props.iconType];
});

//ANIMATE NUMBER
const counterElement = ref(null);
const selectedPeriod = ref(props.periods[0]);

const animateValue = (start, end, duration = 1000) => {
    const startTime = performance.now();

    const formatNumber = (value) => {
        if (props.isCurrency) {
            return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,");
        }
        return Math.floor(value).toLocaleString();
    };

    const updateCounter = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const currentValue = start + (end - start) * progress;
        counterElement.value.textContent = formatNumber(currentValue);

        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    };

    requestAnimationFrame(updateCounter);
};

const updateValue = () => {
    const currentText = counterElement.value.textContent.replace(/,/g, "");
    const currentValue = parseFloat(currentText) || 0;
    const targetValue = props.values[selectedPeriod.value];
    animateValue(currentValue, targetValue);
};

onMounted(() => {
    animateValue(0, props.values[selectedPeriod.value], 1500);
});

//CUSTOMIZE DROPDDOWN FOR FOOTER SELECT
const emit = defineEmits(["update:modelValue"]);

const dropdownOpen = ref(false);

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const selectPeriod = (period) => {
    selectedPeriod.value = period;
    dropdownOpen.value = false;
    emit("update:modelValue", period);
    updateValue();
};
</script>
