<!-- ConfirmationDialog.vue -->
<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999]"
    >
        <div
            class="bg-[var(--color-bg-secondary)] p-4 rounded-2xl border border-[var(--color-border)] max-w-sm w-full text-center"
        >
            <div class="flex items-center justify-center p-4">
                <svg-icon
                    type="mdi"
                    :path="mdiHelpCircleOutline"
                    class="w-[120px] h-[120px] text-yellow-600"
                />
            </div>
            <h2
                class="text-sm font-semibold mb-4 text-[var(--color-text-primary)] uppercase"
            >
                {{ message }}
            </h2>
            <div class="flex justify-center gap-4">
                <button
                    @click="confirm(false)"
                    class="px-8 py-2 rounded-md cursor-pointer hover:bg-[var(--color-primary-hover)]/20 border border-[var(--color-border)] text-[var(--color-text-primary)] font-medium relative overflow-hidden"
                    :disabled="form.processing"
                >
                    No
                </button>
                <button
                    @click="confirm(true)"
                    class="px-8 py-2 rounded-md cursor-pointer bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] text-white font-medium relative overflow-hidden disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <!-- Button text -->
                    <span class="relative z-10">
                        <span v-if="form.processing">Processing...</span>
                        <span v-else>Yes</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { mdiHelpCircleOutline } from "@mdi/js";
import { reactive } from "vue";

const props = defineProps({
    show: Boolean,
    message: String,
});

const emit = defineEmits(["close"]);

const form = reactive({
    processing: false,
});

const confirm = async (choice) => {
    if (choice) {
        form.processing = true; // start loading
        await new Promise((r) => setTimeout(r, 500));
        emit("close", true);
        // Optional: add slight delay if you want to visually show processing
        form.processing = false; // this should ideally be reset from the parent
    } else {
        emit("close", false);
    }
};
</script>
