<template>
    <div class="mb-2">
        <label
            class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2"
            >{{ label }}</label
        >
        <div>
            <input
                v-if="prefixType === 'DS'"
                :type="type"
                v-model="model"
                :readonly="readonly"
                :disabled="readonly"
                :placeholder="
                    readonly ? modifiedPlaceholder : defaultPlaceholder
                "
                :class="[
                    'form-input',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : validation === 'yes'
                        ? model !== 'DS#'
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    selectable === 'yes' ? 'cursor-pointer' : '',
                ]"
            />

            <input
                v-else-if="prefixType === 'JV'"
                :type="type"
                v-model="model"
                :readonly="readonly"
                :disabled="readonly"
                :placeholder="
                    readonly ? modifiedPlaceholder : defaultPlaceholder
                "
                :class="[
                    'form-input',
                    message
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    validation === 'yes'
                        ? model !== 'JV#'
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    selectable === 'yes' ? 'cursor-pointer' : '',
                ]"
            />

            <input
                v-else-if="prefixType === 'CHK'"
                :type="type"
                v-model="model"
                :readonly="readonly"
                :disabled="readonly"
                :placeholder="
                    readonly ? modifiedPlaceholder : defaultPlaceholder
                "
                :class="[
                    'form-input',
                    localMessage
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : validation === 'yes'
                        ? model !== 'CHK#'
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    selectable === 'yes' ? 'cursor-pointer' : '',
                ]"
            />

            <input
                v-else-if="prefixType === 'WHT'"
                :type="type"
                v-model="model"
                :readonly="readonly"
                :disabled="readonly"
                :placeholder="
                    readonly ? modifiedPlaceholder : defaultPlaceholder
                "
                :class="[
                    'form-input',
                    message
                        ? '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    validation === 'yes'
                        ? model !== 'WHT#'
                            ? 'border-[var(--color-border)]'
                            : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10'
                        : '',
                    selectable === 'yes' ? 'cursor-pointer' : '',
                ]"
            />
        </div>
    </div>
</template>

<script setup>
import { computed, watch, nextTick, ref, toRefs } from "vue";

const model = defineModel();

const props = defineProps({
    label: String,
    type: {
        type: String,
        default: "text",
    },

    message: {
        type: String,
        default: "",
    },
    readonly: {
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
    placeholder: String,
    prefixType: String,
});

const localMessage = ref(props.message);

watch(model, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        localMessage.value = "";
    }
});

// const { type } = toRefs(props);
</script>
