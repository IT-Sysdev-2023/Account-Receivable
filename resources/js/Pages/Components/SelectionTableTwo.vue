<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60"
    >
        <div
            :class="[
                'w-full  bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-2xl overflow-hidden',
                hasThirdColumn ? 'max-w-xl' : 'max-w-md',
            ]"
        >
            <div class="relative p-6">
                <!-- Close Button -->
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
                <h2
                    class="text-2xl font-bold text-center text-[var(--color-text-primary)] mb-4"
                >
                    {{ title }}
                </h2>

                <!-- Search -->
                <div class="mb-4 relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        ref="searchInput"
                        class="w-full rounded-md px-4 py-2 border border-[var(--color-border)] text-[var(--color-text-primary)]"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute right-3 top-1/2 -translate-y-1/2"
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
                        <svg-icon
                            type="mdi"
                            :path="mdiMagnify"
                            class="w-5 h-5"
                        />
                    </div>
                </div>

                <!-- Table -->
                <div
                    class="w-full rounded-xl overflow-hidden border border-[var(--color-border)] backdrop-blur-sm"
                >
                    <div class="sticky top-0 z-10 pr-2">
                        <table
                            class="w-full text-[var(--color-text-primary)] text-sm text-left"
                        >
                            <thead>
                                <tr>
                                    <th
                                        class="px-4 py-2"
                                        :class="[
                                            hasThirdColumn
                                                ? 'w-[33.3%]'
                                                : 'w-[50%]',
                                        ]"
                                    >
                                        {{ props.firstHeader }}
                                    </th>
                                    <th
                                        class="px-4 py-2"
                                        :class="[
                                            hasThirdColumn
                                                ? 'w-[33.4%]'
                                                : 'w-[50%]',
                                        ]"
                                    >
                                        {{ props.secondHeader }}
                                    </th>
                                    <th
                                        v-if="hasThirdColumn"
                                        class="px-4 py-2 w-[33.3%]"
                                        :class="[
                                            title === 'Select Item'
                                                ? 'text-center'
                                                : '',
                                        ]"
                                    >
                                        {{
                                            props.thirdHeader || "Third Header"
                                        }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="relative overflow-hidden">
                        <div
                            class="max-h-64 overflow-y-auto relative scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-primary)]/20 scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                        >
                            <table
                                class="w-full text-[var(--color-text-primary)] text-sm"
                            >
                                <tbody v-if="isLoading">
                                    <tr>
                                        <td
                                            colspan="2"
                                            class="text-center py-6"
                                        >
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr
                                        v-for="(item, index) in filteredData"
                                        :key="index"
                                        @click="handleSelect(item)"
                                        class="hover:bg-[var(--color-primary)]/10 cursor-pointer"
                                    >
                                        <td
                                            class="px-4 py-2"
                                            :class="[
                                                hasThirdColumn
                                                    ? 'w-[33.3%]'
                                                    : 'w-[50%]',
                                            ]"
                                        >
                                            {{ item.firstData }}
                                        </td>
                                        <td
                                            class="px-4 py-2"
                                            :class="[
                                                hasThirdColumn
                                                    ? 'w-[33.4%]'
                                                    : 'w-[50%]',
                                            ]"
                                        >
                                            {{ item.secondData }}
                                        </td>
                                        <td
                                            v-if="hasThirdColumn"
                                            class="px-4 py-2 w-[33.3%]"
                                        >
                                            <div
                                                class="flex items-center"
                                                :class="[
                                                    title === 'Select Item'
                                                        ? 'justify-center'
                                                        : 'justify-start',
                                                ]"
                                            >
                                                <span
                                                    v-if="
                                                        title === 'Select Item'
                                                    "
                                                >
                                                    <img
                                                        class="w-10 h-10 rounded overflow-hidden object-center object-cover border-2 border-[var(--color-border)]"
                                                        alt="Item Photo"
                                                        :src="
                                                            item.thirdData
                                                                ? item.thirdData
                                                                : '/storage/images/addItem.png'
                                                        "
                                                    />
                                                </span>
                                                <span v-else>
                                                    {{ item.thirdData }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredData.length === 0">
                                        <td
                                            colspan="2"
                                            class="text-center py-4 text-[var(--color-text-secondary)]"
                                        >
                                            No results found
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
import { ref, watch, nextTick, computed } from "vue";
import { mdiClose, mdiMagnify } from "@mdi/js";
import { debounce } from "lodash";

const props = defineProps({
    show: Boolean,
    title: String,
    data: {
        type: Array,
        default: () => [],
    },
    firstHeader: {
        type: String,
        default: "",
    },
    secondHeader: {
        type: String,
        default: "",
    },
    thirdHeader: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["close", "submit"]);

const searchQuery = ref("");
const filteredData = ref([]);
const isLoading = ref(false);
const searchInput = ref(null);

const filterData = debounce((query) => {
    if (!query.trim()) {
        filteredData.value = props.data;
    } else {
        filteredData.value = props.data.filter(
            (item) =>
                item.secondData.toLowerCase().includes(query.toLowerCase()) ||
                item.firstData.toLowerCase().includes(query.toLowerCase())
        );
    }
    isLoading.value = false;
}, 400);

const hasThirdColumn = computed(() => {
    return props.data.some((item) => "thirdData" in item);
});

watch(
    () => searchQuery.value,
    (query) => {
        isLoading.value = true;
        filterData(query);
    }
);

watch(
    () => props.data,
    () => {
        filteredData.value = props.data;
    },
    { immediate: true }
);

const clearSearch = () => {
    searchQuery.value = "";
    nextTick(() => searchInput.value?.focus());
};

const closeModal = () => {
    emit("close");
};

const handleSelect = (item) => {
    emit("submit", item);
    closeModal();
};
</script>
