<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60"
    >
        <!-- Modal Container -->
        <div
            class="w-full max-w-3xl overflow-hidden rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
        >
            <!-- Content -->
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
                <!-- Header -->
                <div class="mb-6 text-center">
                    <h2
                        class="text-2xl font-bold text-[var(--color-text-primary)] tracking-wide"
                    >
                        CUSTOMER LIST
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

                <!-- TABLE -->
                <div
                    class="w-full rounded-xl overflow-hidden border border-[var(--color-border)] backdrop-blur-sm pl-2"
                >
                    <div class="sticky top-0 z-10 pr-2">
                        <table class="w-full text-[var(--color-text-primary)]">
                            <thead
                                class="border-b border-[var(--color-border)]/50"
                            >
                                <tr>
                                    <th class="px-5 py-3 text-left w-[30%]">
                                        CUSTOMER CODE
                                    </th>
                                    <th class="px-5 py-3 text-left w-[40%]">
                                        CUSTOMER NAME
                                    </th>
                                    <th class="px-5 py-3 text-center w-[30%]">
                                        CUSTOMER TYPE
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="relative overflow-hidden">
                        <div
                            class="max-h-72 overflow-y-auto relative scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-primary)]/20 scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
                        >
                            <table
                                class="w-full text-[var(--color-text-primary)] text-sm"
                            >
                                <!-- Loading State -->
                                <tbody v-if="isLoading">
                                    <tr>
                                        <td
                                            colspan="5"
                                            class="text-center py-8"
                                        >
                                            <div
                                                class="flex justify-center items-center"
                                            >
                                                <svg
                                                    width="30"
                                                    height="30"
                                                    viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg"
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

                                <!-- Data Rows -->
                                <tbody
                                    v-else
                                    class="divide-y divide-[var(--color-border)]/50 rounded-xl"
                                >
                                    <tr
                                        v-for="(code, index) in filteredData"
                                        :key="index"
                                        @click="handleSelect(code)"
                                        class="rounded-xl hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group cursor-pointer"
                                    >
                                        <td
                                            class="px-5 py-2 font-medium w-[30%]"
                                        >
                                            {{ code.customer_code }}
                                        </td>
                                        <td class="px-5 py-2 w-[40%]">
                                            {{ code.customer_name }}
                                        </td>
                                        <td
                                            class="px-5 py-2 w-[30%] text-center"
                                        >
                                            {{ code.customer_type }}
                                        </td>
                                    </tr>

                                    <!-- Empty State -->
                                    <tr
                                        v-if="
                                            filteredData.length === 0 &&
                                            !isLoading
                                        "
                                    >
                                        <td
                                            colspan="5"
                                            class="px-5 py-6 text-center"
                                        >
                                            <div
                                                class="flex flex-col items-center justify-center text-[var(--color-text-primary)]"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-10 w-10 mb-2"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>
                                                <p class="font-medium">
                                                    No Customer Code found
                                                </p>
                                            </div>
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
import axios from "axios";
import { nextTick, ref, watch } from "vue";

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(["close", "submit"]);

const customerCodeResults = ref([]);
const isLoading = ref(false);
const searchQuery = ref("");
const filteredData = ref([]);
const searchInput = ref(null);
const customers = ref([]);
let debounceTimeout = null;

const handleSelect = (code) => {
    emit("submit", {
        cus_code: code.customer_code,
        cus_name: code.customer_name,
        price_group: code.customer_type,
        adv_py_bal: code.customer_adv_py_bal,
        editable_wht: code.editable_wht,
        journal_voucher: code.journal_voucher,
    });
    closeModal();
};

const closeModal = () => {
    emit("close");
};

const clearSearch = () => {
    searchQuery.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

// Fetch customer data only once when modal opens
watch(
    () => props.show,
    async (show) => {
        if (!show) {
            filteredData.value = [];
            customers.value = [];
            return;
        }

        isLoading.value = true;
        try {
            // Fall back to Laravel endpoint if external API fails
            const localResponse = await axios.get(route("customers.getAll"));

            customers.value = localResponse.data.map((customer) => ({
                customer_code: customer.cus_code, // Adjust these fields based on your Customer model
                customer_name: customer.cus_name,
                customer_type: customer.cus_type,
                customer_adv_py_bal: customer.advanced_payment_balance,
                editable_wht: customer.editable_wht,
                journal_voucher: customer.journal_voucher,
            }));
            filteredData.value = customers.value;
        } catch (error) {
            console.error("Error fetching customers:", error);
            customers.value = [];
            filteredData.value = [];
        } finally {
            isLoading.value = false;
        }
    },
    { immediate: true }
);

// Debounced search
watch(
    () => searchQuery.value,
    (query) => {
        isLoading.value = true;
        if (debounceTimeout) clearTimeout(debounceTimeout);

        debounceTimeout = setTimeout(() => {
            if (!query.trim()) {
                filteredData.value = customers.value;
            } else {
                filteredData.value = customers.value.filter((customer) =>
                    customer.customer_name
                        ?.toString()
                        .toLowerCase()
                        .includes(query.toLowerCase())
                );
            }
            isLoading.value = false;
        }, 400); // 400ms debounce
    }
);
</script>
<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
