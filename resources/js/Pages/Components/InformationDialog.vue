<!-- INFORMATIONDialog.vue -->
<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[9999]"
    >
        <div
            class="bg-[var(--color-bg-secondary)] p-4 rounded-2xl border border-[var(--color-border)] max-w-lg w-full text-center group"
        >
            <div class="flex items-center justify-center">
                <span
                    class="transition-transform duration-600 group-hover:rotate-360"
                >
                    <svg-icon
                        type="mdi"
                        :path="mdiCloseCircleOutline"
                        width="150px"
                        height="150px"
                        class="text-red-500"
                    />
                </span>
            </div>
            <h2
                class="text-sm font-semibold mb-4 text-[var(--color-text-primary)] uppercase"
            >
                {{ message }}
            </h2>
            <div class="flex justify-center gap-4">
                <button
                    @click="closeModal()"
                    class="px-6 py-2 rounded-md cursor-pointer bg-[var(--color-primary)] hover:bg-[var(--color-primary-hover)] text-white font-medium relative overflow-hidden"
                >
                    Okay
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { mdiCloseCircleOutline } from "@mdi/js";
import { reactive } from "vue";

const props = defineProps({
    show: Boolean,
    message: String,
});

const emit = defineEmits(["close"]);

const form = reactive({
    processing: false,
});

const closeModal = () => {
    emit("close", false);
};
</script>
