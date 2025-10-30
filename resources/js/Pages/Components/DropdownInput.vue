<template>
    <div class="relative w-full mb-2" ref="dropdown">
        <label
            class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
            >{{ label }}</label
        >
        <button
            type="button"
            @click="toggle"
            :disabled="disabled"
            class="block w-full text-left rounded-md border border-[var(--color-border)] bg-[var(--color-bg-secondary)] px-4 py-2.5 text-sm text-[var(--color-text-primary)] font-semibold placeholder-[var(--color-text-secondary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/70 focus:border-[var(--color-primary)] transition-all duration-200 disabled:cursor-not-allowed group"
            :class="[
                modelValue
                    ? 'border-[var(--color-border)]'
                    : '!border-red-400 !ring-2 !ring-red-500/50',
                localMessage ? '!border-red-400 !ring-2 !ring-red-500/50' : '',
            ]"
        >
            <div class="flex justify-between items-center gap-2">
                <span
                    :class="
                        !modelValue ? 'text-[var(--color-text-secondary)]' : ''
                    "
                >
                    {{
                        modelValue
                            ? modelValue
                            : disabled
                            ? disabledPlaceholder
                            : placeholder
                    }}
                </span>
                <span
                    :class="[
                        'transition-transform duration-300',
                        open ? 'rotate-180' : '',
                    ]"
                >
                    <svg
                        class="inline w-5 h-5 float-right"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </span>
            </div>
        </button>

        <ul
            v-if="open"
            ref="dropdownMenu"
            class="absolute z-50 mt-1 w-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-md shadow-lg max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-rounded-full scrollbar-track-rounded-full"
            :class="dropUp ? 'bottom-full mb-1' : 'mt-1'"
            :style="menuMaxHeightStyle"
        >
            <li
                v-for="option in options"
                :key="option"
                @click="select(option)"
                class="px-4 py-2 hover:bg-[var(--color-primary)]/10 cursor-pointer text-sm border-b border-[var(--color-border)]/30 last:border-b-0"
            >
                {{ option }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";

const props = defineProps({
    label: String,
    modelValue: String,
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: "Select an option",
    },
    disabledPlaceholder: {
        type: String,
        default: "Disabled",
    },
    message: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue"]);

const open = ref(false);
const dropdown = ref(null);
const dropdownMenu = ref(null);
const dropUp = ref(false);
const observer = ref(null);
const menuMaxHeightStyle = ref(null);

const toggle = () => {
    if (props.disabled) return;

    open.value = !open.value;

    if (open.value) {
        checkDropdownPosition();
    }
};

const select = (option) => {
    emit("update:modelValue", option);
    open.value = false;
};

const checkDropdownPosition = () => {
    if (!open.value || !dropdownMenu.value || !dropdown.value) return;

    const dropdownRect = dropdown.value.getBoundingClientRect();
    const menuHeight = dropdownMenu.value.scrollHeight;
    const defaultMaxHeight = 192;

    // Check space below the dropdown
    const spaceBelow = window.innerHeight - dropdownRect.bottom;
    // Check space above the dropdown
    const spaceAbove = dropdownRect.top;

    // Default to drop down if there's enough space or if there's more space below
    if (spaceBelow >= menuHeight || spaceBelow >= spaceAbove) {
        dropUp.value = false;
    } else {
        // If not enough space below and more space above, drop up
        dropUp.value = true;
    }

    const availableSpace = dropUp.value
        ? dropdownRect.top - 10
        : window.innerHeight - dropdownRect.bottom - 10;

    // Only apply custom max height if available space is less than default
    if (availableSpace < defaultMaxHeight) {
        menuMaxHeightStyle.value = {
            maxHeight: `${Math.max(availableSpace, 100)}px`,
        }; // Ensure minimum 100px height
    } else {
        // Reset to default if there's enough space
        menuMaxHeightStyle.value = null;
    }

    // Additional check to prevent menu from going off-screen
    // if (dropUp.value) {
    //     const menuTop = dropdownRect.top - menuHeight;
    //     if (menuTop < 0) {
    //         // If dropping up would go off the top of the screen, adjust max height
    //         dropdownMenu.value.style.maxHeight = `${dropdownRect.top - 10}px`;
    //     }
    // } else {
    //     const menuBottom = dropdownRect.bottom + menuHeight;
    //     if (menuBottom > window.innerHeight) {
    //         // If dropping down would go off the bottom of the screen, adjust max height
    //         dropdownMenu.value.style.maxHeight = `${
    //             window.innerHeight - dropdownRect.bottom - 10
    //         }px`;
    //     }
    // }
};

const handleClickOutside = (event) => {
    if (dropdown.value && !dropdown.value.contains(event.target)) {
        open.value = false;
    }
};

onMounted(() => {
    // Initialize ResizeObserver to detect changes in viewport
    observer.value = new ResizeObserver(checkDropdownPosition);
    observer.value.observe(document.body);
    window.addEventListener("resize", checkDropdownPosition);
});

onBeforeUnmount(() => {
    if (observer.value) {
        observer.value.disconnect();
    }
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("resize", checkDropdownPosition);
});

watch(open, (newVal) => {
    if (newVal) {
        document.addEventListener("click", handleClickOutside);
        window.addEventListener("resize", checkDropdownPosition);
        // Recheck position in next tick to ensure DOM is updated
        setTimeout(checkDropdownPosition, 0);
    } else {
        document.removeEventListener("click", handleClickOutside);
        window.removeEventListener("resize", checkDropdownPosition);
    }
});

const localMessage = ref(props.message);

watch(
    () => props.modelValue,
    async (newVal, oldVal) => {
        if (newVal !== oldVal) {
            localMessage.value = "";
        }
    }
);

watch(
    () => props.message,
    async (newVal) => {
        localMessage.value = newVal;
    }
);
</script>
