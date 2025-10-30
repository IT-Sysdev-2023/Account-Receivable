<template>
    <div class="mb-2">
        <label
            class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
            @click.stop
            >{{ label }}</label
        >

        <!-- Text or Password Input -->
        <div>
            <!-- Text Input -->
            <input
                v-if="type === 'text'"
                :type="type"
                v-model="model"
                @input="$emit('update:model', $event.target.value)"
                @focus="$emit('focus', $event)"
                @keydown="$emit('keydown', $event)"
                @blur="$emit('blur', $event)"
                :readonly="readonly"
                :disabled="readonly"
                :placeholder="
                    readonly ? modifiedPlaceholder : defaultPlaceholder
                "
                :class="[
                    'form-input',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    validation === 'yes'
                        ? model
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    selectable === 'yes' ? 'cursor-pointer' : '',
                ]"
            />

            <div v-else-if="type === 'password'" class="relative">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    v-model="model"
                    @input="$emit('update:model', $event.target.value)"
                    @focus="$emit('focus', $event)"
                    @keydown="$emit('keydown', $event)"
                    @blur="$emit('blur', $event)"
                    :readonly="readonly"
                    :disabled="readonly"
                    :placeholder="
                        readonly ? modifiedPlaceholder : defaultPlaceholder
                    "
                    :class="[
                        'form-input',
                        localMessage
                            ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                            : '',
                        validation === 'yes'
                            ? model
                                ? 'border-[var(--color-border)]'
                                : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                            : '',
                    ]"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] transition-colors duration-200"
                    @click="togglePasswordVisibility"
                    tabindex="-1"
                >
                    <svg
                        v-if="showPassword"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                    </svg>
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
                        />
                    </svg>
                </button>
            </div>

            <!-- Number/Decimal Input -->
            <input
                v-else-if="type === 'decimal' || type === 'number'"
                type="number"
                :step="type === 'decimal' ? '0.01' : '1'"
                v-model="model"
                :readonly="readonly"
                :disabled="readonly"
                :placeholder="
                    readonly ? modifiedPlaceholder : defaultPlaceholder
                "
                :class="[
                    'form-input hide-arrows',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    validation === 'yes'
                        ? model
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    ,
                ]"
            />

            <!-- Date Input -->
            <div v-else-if="type === 'date'" class="relative">
                <input
                    type="text"
                    v-model="displayDate"
                    @click="showPicker = true"
                    @blur="handleBlur"
                    :readonly="readonly"
                    :disabled="readonly"
                    placeholder="mm/dd/yyyy"
                    :class="[
                        'form-input cursor-pointer',
                        localMessage
                            ? '!border-red-400 !ring-2 !ring-red-500/50'
                            : '',
                        validation === 'yes'
                            ? displayDate
                                ? 'border-[var(--color-border)]'
                                : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                            : '',
                    ]"
                />
                <input
                    ref="datePicker"
                    type="date"
                    v-model="internalDate"
                    @change="handleDateChange"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    :style="{ 'pointer-events': showPicker ? 'auto' : 'none' }"
                />
            </div>

            <!-- Textarea -->
            <textarea
                v-else-if="type === 'textarea'"
                v-model="model"
                :rows="rows"
                :readonly="readonly"
                :disabled="readonly"
                :placeholder="
                    readonly ? modifiedPlaceholder : defaultPlaceholder
                "
                :class="[
                    'form-textarea',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    validation === 'yes'
                        ? model
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    selectable === 'yes' ? 'cursor-pointer' : '',
                ]"
            />

            <!-- Select Input -->
            <select
                v-else-if="type === 'select'"
                v-model="model"
                :readonly="readonly"
                :disabled="readonly"
                :class="[
                    'form-select',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    validation === 'yes'
                        ? model
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    noSelectionPlaceholder
                        ? 'text-[var(--color-text-secondary)]'
                        : '',
                    selectplaceholder
                        ? 'text-[var(--color-text-secondary)]'
                        : '',
                ]"
            >
                <option disabled hidden value="">
                    {{ !readonly ? selectplaceholder : noSelectionPlaceholder }}
                </option>
                <option
                    v-for="option in options"
                    :key="option"
                    :value="option"
                    class="text-[var(--color-text-primary)]"
                >
                    {{ option }}
                </option>
            </select>
        </div>

        <!-- Validation Message -->
        <!-- <div class="h-4">
            <p v-if="message" class="text-red-500 text-sm">{{ message }}</p>
        </div> -->
    </div>
</template>

<script setup>
import { computed, watch, nextTick, ref, toRefs } from "vue";

const model = defineModel();
const datePicker = ref(null);
const showPicker = ref(false);
const displayDate = ref("");
const internalDate = ref("");

const props = defineProps({
    label: String,
    type: {
        type: String,
        default: "text",
    },
    options: {
        type: Array,
        default: () => [],
    },
    message: {
        type: String,
        default: "",
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    selectplaceholder: {
        type: String,
        default: "Click To Select",
    },
    noSelectionPlaceholder: {
        type: String,
        default: "Please Select Customer First",
    },
    hasSelection: {
        type: Boolean,
        default: false,
    },
    defaultPlaceholder: {
        type: String,
        default: "",
    },
    modifiedPlaceholder: {
        type: String,
        default: "",
    },
    validation: {
        type: String,
        default: "yes",
    },
    selectable: {
        type: String,
        default: "no",
    },
    rows: {
        type: String,
        default: "",
    },
    placeholder: String,
});
const isShowingPlaceholder = computed(() => !model.value || model.value === "");
defineEmits([
    "update:model", // Required for v-model
    "focus", // For @focus
    "blur", // For @blur
    "keydown", // For @keydown
    "input", // For @input
]);

const localMessage = ref(props.message);

watch(model, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        localMessage.value = "";
    }
});

watch(
    () => props.message,
    async (newVal) => {
        localMessage.value = newVal;
    }
);

const { type } = toRefs(props);
// Watch for external model changes
watch(
    [model, type],
    ([newValue, currentType], [oldValue, oldType]) => {
        if (currentType !== "date") return;

        if (newValue) {
            internalDate.value = convertToIsoDate(newValue);
            displayDate.value = formatDate(newValue);
        } else {
            internalDate.value = "";
            displayDate.value = "";
        }
    },
    { immediate: true }
);

// Convert to ISO format (YYYY-MM-DD)
function convertToIsoDate(date) {
    if (!date) return "";

    // If already in ISO format
    if (date.match(/^\d{4}-\d{2}-\d{2}$/)) return date;

    // If in mm/dd/yyyy format
    if (date.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
        const [month, day, year] = date.split("/");
        return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
    }

    // Try parsing as Date object
    const d = new Date(date);
    if (!isNaN(d.getTime())) {
        return d.toISOString().split("T")[0];
    }

    return "";
}

// Format as mm/dd/yyyy
function formatDate(date) {
    const isoDate = convertToIsoDate(date);
    if (!isoDate) return "";

    const [year, month, day] = isoDate.split("-");
    return `${month}/${day}/${year}`;
}

// Handle text input changes
function handleInput(event) {
    let value = event.target.value.replace(/\D/g, "");

    // Auto-format as user types
    if (value.length > 2 && value.length <= 4) {
        value = value.slice(0, 2) + "/" + value.slice(2);
    } else if (value.length > 4) {
        value =
            value.slice(0, 2) +
            "/" +
            value.slice(2, 4) +
            "/" +
            value.slice(4, 8);
    }

    displayDate.value = value;

    // Update model if we have a complete valid date
    if (value.length === 10) {
        const parsed = parseInputDate(value);
        if (parsed) {
            internalDate.value = parsed;
            model.value = parsed;
        }
    }
}

// Parse mm/dd/yyyy input
function parseInputDate(input) {
    if (!input || !input.match(/^\d{2}\/\d{2}\/\d{4}$/)) return null;

    const [month, day, year] = input.split("/");
    const date = new Date(year, month - 1, day);

    // Validate date
    if (
        date.getFullYear() != year ||
        date.getMonth() + 1 != month ||
        date.getDate() != day
    ) {
        return null;
    }

    return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
}

// Handle native date picker changes
function handleDateChange() {
    if (internalDate.value) {
        displayDate.value = formatDate(internalDate.value);
        model.value = internalDate.value;
    }
    showPicker.value = false;
}

// Handle blur event
function handleBlur() {
    // Validate the displayed date
    const parsed = parseInputDate(displayDate.value);
    if (parsed) {
        internalDate.value = parsed;
        model.value = parsed;
        displayDate.value = formatDate(parsed); // Reformat
    } else {
        displayDate.value = "";
        model.value = "";
    }
    showPicker.value = false;
}

// When text input is focused, show the picker
watch(showPicker, async (shouldShow) => {
    if (shouldShow && datePicker.value) {
        await nextTick();
        datePicker.value.showPicker();
    }
});

const showPassword = ref(false);
const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};
</script>
