<template>
    <div class="relative w-full mb-2" ref="dropdown">
        <!-- Label -->
        <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">
            {{ label }}
        </label>

        <!-- Button -->
        <button type="button" @click="toggleDropdown"
            class="block w-full text-left rounded-md border border-[var(--color-border)] bg-[var(--color-bg-secondary)] px-4 py-2.5 text-sm text-[var(--color-text-primary)] font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)]/70 focus:border-[var(--color-primary)] transition-all duration-200 flex justify-between items-center">
            <span :class="!modelValue.length ? 'text-[var(--color-text-secondary)]' : ''">
                {{ selectedText }}
            </span>
            <svg :class="{ 'rotate-180': open }" class="w-5 h-5 transition-transform" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.23 8.27a.75.75 0 01.02-1.06z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown menu -->
        <ul v-show="open" ref="dropdownMenu"
            class="absolute z-50 bottom-full mb-1 w-full bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-md shadow-lg max-h-48 overflow-y-auto scrollbar-thin scrollbar-thumb-rounded-full scrollbar-track-rounded-full">
            <li v-for="option in options" :key="option.id" @click.stop="toggleOption(option)"
                class="px-4 py-2 hover:bg-[var(--color-primary)]/10 cursor-pointer text-sm border-b border-[var(--color-border)]/30 last:border-b-0 flex items-center gap-2">
                <input type="checkbox" class="w-4 h-4 text-[var(--color-primary)] border-gray-300 rounded"
                    :checked="modelValue.includes(option.id)" readonly />
                <span>{{ option.name }}</span>
            </li>
        </ul>

    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    label: String,
    options: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue"]);

const open = ref(false);
const dropdown = ref(null);

const toggleDropdown = () => {
    open.value = !open.value;
};

const toggleOption = (option) => {
    let newValue = [...props.modelValue];
    const index = newValue.indexOf(option.id);

    if (index === -1) {
        newValue.push(option.id);
    } else {
        newValue.splice(index, 1);
    }

    emit("update:modelValue", newValue);
};

const selectedText = computed(() => {
    if (!props.modelValue.length) return "Select options";
    const selected = props.options.filter((opt) => props.modelValue.includes(opt.id));
    return selected.map((opt) => opt.name).join(", ");
});

// Close dropdown on outside click
const handleClickOutside = (event) => {
    if (dropdown.value && !dropdown.value.contains(event.target)) {
        open.value = false;
    }
};

window.addEventListener("click", handleClickOutside);
</script>
