<template>
    <Transition name="modal">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60"
        >
            <ToastAlertWarning :show="showToast" :message="toastMessage" />
            <!-- Modal Container -->
            <div
                class="w-full max-w-4xl overflow-hidden rounded-2xl bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
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
                            DOCUMENT NUMBER LIST
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
                            <table
                                class="w-full text-[var(--color-text-primary)]"
                            >
                                <thead
                                    class="border-b border-[var(--color-border)]/50"
                                >
                                    <tr>
                                        <th class="px-5 py-2 text-left w-[25%]">
                                            DOCUMENT NO
                                        </th>
                                        <th class="px-5 py-2 text-left w-[25%]">
                                            DATE
                                        </th>
                                        <th class="px-5 py-2 text-left w-[25%]">
                                            TYPE
                                        </th>
                                        <th
                                            class="px-5 py-2 text-right w-[25%]"
                                        >
                                            AMOUNT
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
                                            v-for="(
                                                invoice, index
                                            ) in filteredData"
                                            :key="index"
                                            @click="submitSelected(invoice)"
                                            class="rounded-xl hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group cursor-pointer"
                                        >
                                            <td
                                                class="px-5 py-2 font-medium w-[25%]"
                                            >
                                                {{ invoice.docunumber }}
                                            </td>
                                            <td class="px-5 py-2">
                                                {{ formatDate(invoice.date) }}
                                            </td>
                                            <td class="px-5 py-2 w-[25%]">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-emerald-700 text-emerald-300':
                                                            invoice.type ===
                                                            'Sales Invoice',
                                                        'bg-cyan-700 text-cyan-300':
                                                            invoice.type ===
                                                            'Charge Invoice',
                                                        'bg-pink-700 text-pink-300':
                                                            invoice.type ===
                                                            'Payment',
                                                        'bg-purple-700 text-purple-300':
                                                            invoice.type ===
                                                            'BG',
                                                    }"
                                                >
                                                    {{ invoice.type }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-5 py-2 text-right font-medium w-[25%]"
                                            >
                                                {{
                                                    formatCurrency(
                                                        invoice.amount
                                                    )
                                                }}
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
                                                        No data found for this
                                                        customer
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
    </Transition>
</template>

<script setup>
import { nextTick, ref, watch } from "vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import { mdiClose, mdiMagnify } from "@mdi/js";

const props = defineProps({
    customer_code: String,
    apply_to: String,
});

const emit = defineEmits(["close", "submit"]);

const documentNoResults = ref([]);
const selectedInvoiceNumber = ref(null); // Now only stores the invoice number
const selectedTotalAmount = ref(null); // Now only stores the invoice amount
const selectedDate = ref(null); // Now only stores the invoice date
const selectedAmountPaid = ref(null); // Now only stores the AmountPaid
const selectedType = ref(null); // Now only stores the type
const isLoading = ref(false);
const searchQuery = ref("");
const filteredData = ref([]);
const searchInput = ref(null);
const documents = ref([]);
let debounceTimeout = null;

// Watch for changes in customer_code
watch(
    () => props.customer_code,
    async (newCode) => {
        if (newCode) {
            try {
                isLoading.value = true;
                selectedInvoiceNumber.value = null; // Reset selection when customer changes
                selectedTotalAmount.value = null; // Reset selection when customer changes
                selectedDate.value = null; // Reset selection when customer changes
                selectedAmountPaid.value = null; // Reset selection when customer changes
                selectedType.value = null; // Reset selection when customer changes

                const response = await axios.get(route("getInvoiceList"), {
                    params: {
                        type: newCode,
                    },
                });
                documents.value = response.data
                    .filter(
                        (invoice) =>
                            invoice.type ===
                            (props.apply_to === "Sales Invoice"
                                ? "Sales Invoice"
                                : "Charge Invoice")
                    )
                    .filter((invoice) => invoice.running_balance !== "0.00")
                    .map((invoice) => ({
                        docunumber: invoice.invoice_no,
                        date: invoice.receipt_date,
                        type: invoice.type,
                        amount_paid: invoice.amount_paid,
                        amount: invoice.running_balance,
                    }));
                filteredData.value = documents.value;
            } catch (error) {
                console.error("Error fetching invoices:", error);
                filteredData.value = [];
                documents.value = [];
            } finally {
                isLoading.value = false;
            }
        } else {
            filteredData.value = [];
            documents.value = [];
        }
    },
    { immediate: true }
);

const formatDate = (dateString) => {
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const clearSearch = () => {
    searchQuery.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

const submitSelected = (document) => {
    if (document.amount_paid !== "0.00") {
        showWarningToast("This Document Number Already Has An Initial Payment");
    } else {
        emit("submit", {
            invoiceNumber: document.docunumber,
            totalAmount: document.amount,
            date: document.date,
            type: document.type,
        });
        closeModal();
    }
};

const closeModal = () => {
    emit("close");
};

watch(
    () => searchQuery.value,
    (query) => {
        isLoading.value = true;
        if (debounceTimeout) clearTimeout(debounceTimeout);

        debounceTimeout = setTimeout(() => {
            if (!query.trim()) {
                filteredData.value = documents.value;
            } else {
                filteredData.value = documents.value.filter((document) =>
                    document.docunumber
                        ?.toString()
                        .toLowerCase()
                        .includes(query.toLowerCase())
                );
            }
            isLoading.value = false;
        }, 400); // 400ms debounce
    }
);

//#region ////////////////TOAST///////////////////////////////////////////////////////////////////////////////////////////////////////
const showToast = ref(false);
const toastMessage = ref("");
let toastTimeout = null; // to keep track of the timeout

const showWarningToast = (message) => {
    toastMessage.value = message;
    showToast.value = false; // Hide first to trigger reactivity if the same toast shows again
    if (toastTimeout) clearTimeout(toastTimeout); // Clear any previous timeout

    // Trigger reactivity again on next tick
    setTimeout(() => {
        showToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showToast.value = false;
        toastTimeout = null;
    }, 3000);
};

//#endregion
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
