<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60"
    >
        <div
            class="w-full max-w-sm overflow-hidden rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
        >
            <div class="relative p-6">
                <!-- Close X Icon -->
                <button
                    type="button"
                    @click="closeModal"
                    class="absolute top-4 right-4 text-[var(--color-text-primary)] hover:text-red-500 transition group"
                    title="Close"
                >
                    <div class="flex justify-center items-center gap-2">
                        <span
                            class="transition-transform duration-300 group-hover:rotate-180"
                        >
                            <svg-icon type="mdi" :path="mdiClose" />
                        </span>
                    </div>
                </button>

                <!-- Title -->
                <div class="mb-6 text-center">
                    <h2
                        class="text-2xl font-bold text-[var(--color-text-primary)] tracking-wide"
                    >
                        {{ title }}
                    </h2>
                    <div
                        class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                    ></div>
                </div>

                <!-- Search Input -->
                <div class="mb-4 relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        ref="searchInput"
                        class="w-full rounded-md px-4 py-2 text-[var(--color-text-primary)] border border-[var(--color-border)]"
                        :class="{
                            '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10':
                                filteredData.length === 0,
                        }"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)] hover:text-red-500"
                    >
                        <svg-icon
                            type="mdi"
                            :path="mdiClose"
                            class="w-5 h-5 hover:text-red-500"
                        />
                    </button>
                    <div
                        v-else
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--color-text-secondary)]"
                    >
                        <svg-icon type="mdi" :path="mdiMagnify" size="20" />
                    </div>
                </div>

                <!-- Table -->
                <div
                    class="rounded-xl bg-[var(--color-primary)]/20 backdrop-blur-sm overflow-hidden"
                >
                    <div class="relative">
                        <div
                            class="overflow-y-auto max-h-[220px] scrollbar-thin scrollbar-stable [scrollbar-gutter:stable] pl-2 pb-2"
                        >
                            <table
                                class="w-full text-[var(--color-text-primary)] text-sm"
                            >
                                <tbody v-if="isLoading">
                                    <tr>
                                        <td colspan="1" class="py-12">
                                            <div
                                                class="flex justify-center items-center"
                                            >
                                                <svg
                                                    width="30"
                                                    height="30"
                                                    viewBox="0 0 24 24"
                                                    fill="var(--color-icon)"
                                                >
                                                    <rect
                                                        class="spinner_jCIR"
                                                        x="1"
                                                        y="6"
                                                        width="2.8"
                                                        height="12"
                                                    />
                                                    <rect
                                                        class="spinner_jCIR spinner_upm8"
                                                        x="5.8"
                                                        y="6"
                                                        width="2.8"
                                                        height="12"
                                                    />
                                                    <rect
                                                        class="spinner_jCIR spinner_2eL5"
                                                        x="10.6"
                                                        y="6"
                                                        width="2.8"
                                                        height="12"
                                                    />
                                                    <rect
                                                        class="spinner_jCIR spinner_Rp9l"
                                                        x="15.4"
                                                        y="6"
                                                        width="2.8"
                                                        height="12"
                                                    />
                                                    <rect
                                                        class="spinner_jCIR spinner_dy3W"
                                                        x="20.2"
                                                        y="6"
                                                        width="2.8"
                                                        height="12"
                                                    />
                                                </svg>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else class="rounded-xl">
                                    <tr
                                        v-for="(item, index) in filteredData"
                                        :key="index"
                                        @click="handleSelect(item)"
                                        :class="[
                                            'cursor-pointer hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group h-10',
                                            index !== filteredData.length - 1
                                                ? 'border-b border-[var(--color-border)]/50'
                                                : '',
                                        ]"
                                    >
                                        <td
                                            class="px-5 py-3 text-center font-medium"
                                        >
                                            {{ getValue(item, labelKey) }}
                                        </td>
                                    </tr>
                                    <tr v-if="filteredData.length === 0">
                                        <td
                                            colspan="1"
                                            class="px-5 py-6 text-center"
                                        >
                                            No data found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { mdiClose, mdiMagnify } from "@mdi/js";
import { ref, watch, computed, nextTick } from "vue";

const props = defineProps({
    show: Boolean,
    data: Array,
    title: {
        type: String,
        default: "Select Option",
    },
    labelKey: {
        type: String,
        default: "label",
    },
    valueKey: {
        type: String,
        default: "value",
    },
});

const searchQuery = ref("");
const isLoading = ref(false);
const filteredData = ref([]);
const searchInput = ref(null);

const emit = defineEmits(["close", "submit"]);

const getValue = (item, key) => {
    return typeof item === "object" ? item[key] : item;
};

const handleSelect = (item) => {
    emit("submit", {
        value: getValue(item, props.valueKey),
        label: getValue(item, props.labelKey),
    });
    closeModal();
};

const clearSearch = () => {
    searchQuery.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

watch(
    () => searchQuery.value,
    (query) => {
        isLoading.value = true;

        setTimeout(() => {
            if (!query.trim()) {
                filteredData.value = props.data;
            } else {
                filteredData.value = props.data.filter((item) => {
                    const label = getValue(item, props.labelKey);
                    return label
                        ?.toString()
                        .toLowerCase()
                        .includes(query.toLowerCase());
                });
            }
            isLoading.value = false;
        }, 500); // 500ms delay to show loading spinner
    },
    { immediate: true }
);

const closeModal = () => {
    emit("close");
};
</script>
