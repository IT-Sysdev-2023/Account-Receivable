<template>
    <Transition name="modal">
        <div
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-60 flex items-center justify-center px-4"
        >
            <!-- Modal Container -->
            <div
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-5xl rounded-2xl border border-[var(--color-border)]"
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
                        <h2 class="text-2xl font-bold tracking-wide">
                            TRANSACTION LIST
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
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
                                        <th
                                            class="px-5 py-2 text-left w-[20%] tracking-wide uppercase"
                                        >
                                            Document No
                                        </th>
                                        <th
                                            class="px-5 py-2 text-left w-[20%] tracking-wide uppercase"
                                        >
                                            Date
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[20%] tracking-wide uppercase"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[20%] tracking-wide uppercase"
                                        >
                                            Amount
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[20%] tracking-wide uppercase"
                                        >
                                            Balance
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
                                            ) in allTransactionResults"
                                            :key="index"
                                            class="rounded-xl hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group"
                                        >
                                            <td
                                                class="px-5 py-2 font-medium w-[20%]"
                                            >
                                                {{ invoice.docunumber }}
                                            </td>
                                            <td
                                                class="px-5 py-2 w-[20%] font-medium"
                                            >
                                                {{ formatDate(invoice.date) }}
                                            </td>
                                            <td
                                                class="px-5 py-2 w-[20%] text-center"
                                            >
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
                                                class="px-5 py-2 text-right font-medium w-[20%]"
                                            >
                                                {{
                                                    formatCurrency(
                                                        invoice.amount
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-5 py-2 text-right font-medium w-[20%]"
                                            >
                                                {{
                                                    formatCurrency(
                                                        invoice.balance
                                                    )
                                                }}
                                            </td>
                                        </tr>

                                        <!-- Empty State -->
                                        <tr
                                            v-if="
                                                allTransactionResults.length ===
                                                    0 && !isLoading
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

                    <div
                        v-if="!isLoading"
                        class="flex flex-col md:flex-col gap-4 px-4 mt-6"
                    >
                        <div
                            class="w-full grid sm:grid-cols-1 md:grid-cols-4 gap-4"
                        >
                            <TextInput
                                label="Sales Invoice Total"
                                v-model="form.salesInvoiceTotal"
                                type="text"
                                :message="form.errors.salesInvoiceTotal"
                                readonly
                            />
                            <TextInput
                                label="Charge Invoice Total"
                                v-model="form.chargeInvoiceTotal"
                                type="text"
                                :message="form.errors.chargeInvoiceTotal"
                                readonly
                            />
                            <TextInput
                                label="Payment Total"
                                v-model="form.paymentTotal"
                                type="text"
                                :message="form.errors.paymentTotal"
                                readonly
                            />
                            <TextInput
                                label="Overall Total"
                                v-model="form.overallTotal"
                                type="text"
                                :message="form.errors.overallTotal"
                                readonly
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch } from "vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { mdiClose } from "@mdi/js";

const props = defineProps({
    customer_code: String,
    receipt_date: String,
    type: String,
});

const form = useForm({
    salesInvoiceTotal: null,
    chargeInvoiceTotal: null,
    paymentTotal: null,
    overallTotal: null,
});

const emit = defineEmits(["close", "submit"]);

const allTransactionResults = ref([]);
const selectedInvoiceNumber = ref(null); // Now only stores the invoice number
const selectedTotalAmount = ref(null); // Now only stores the invoice amount
const selectedDate = ref(null); // Now only stores the invoice date
const isLoading = ref(false);

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

                if (props.type === "Add") {
                    const response = await axios.get(route("getInvoiceList"), {
                        params: {
                            type: newCode,
                        },
                    });

                    allTransactionResults.value = response.data
                        .filter(
                            (invoice) =>
                                invoice.receipt_date <= props.receipt_date
                        )
                        .filter((invoice) => invoice.type !== "BG")
                        //.filter((invoice) => invoice.running_balance !== "0.00")
                        .map((invoice) => ({
                            docunumber: invoice.invoice_no,
                            date: invoice.receipt_date,
                            type: invoice.type,
                            amount: invoice.amount,
                            balance: invoice.running_balance,
                        }));

                    // Calculate totals for each type
                    const calculateTotal = (type) => {
                        return allTransactionResults.value
                            .filter((invoice) => invoice.type === type)
                            .reduce(
                                (total, invoice) =>
                                    total + parseFloat(invoice.balance),
                                0
                            )
                            .toFixed(2);
                    };

                    // Assign to form fields
                    const sitot = calculateTotal("Sales Invoice") || "0.00";
                    const citot = calculateTotal("Charge Invoice") || "0.00";
                    const pytot = calculateTotal("Payment") || "0.00";

                    form.salesInvoiceTotal = formatCurrency(sitot);
                    form.chargeInvoiceTotal = formatCurrency(citot);
                    form.paymentTotal = formatCurrency(pytot);

                    const ovtot = (
                        parseFloat(sitot) +
                        parseFloat(citot) +
                        parseFloat(pytot)
                    ).toFixed(2);

                    // Calculate overall total
                    form.overallTotal = formatCurrency(ovtot);
                } else if (props.type === "View") {
                    const response = await axios.get(
                        route("getInvoiceClearedList"),
                        {
                            params: {
                                type: newCode,
                            },
                        }
                    );

                    allTransactionResults.value = response.data
                        .filter(
                            (invoice) =>
                                invoice.receipt_date <= props.receipt_date
                        )
                        .filter((invoice) => invoice.type !== "BG")
                        //.filter((invoice) => invoice.running_balance !== "0.00")
                        .map((invoice) => ({
                            docunumber: invoice.invoice_no,
                            date: invoice.receipt_date,
                            type: invoice.type,
                            amount: invoice.amount,
                            balance: invoice.running_balance,
                        }));

                    // Calculate totals for each type
                    const calculateTotal = (type) => {
                        return allTransactionResults.value
                            .filter((invoice) => invoice.type === type)
                            .reduce(
                                (total, invoice) =>
                                    total + parseFloat(invoice.balance),
                                0
                            )
                            .toFixed(2);
                    };

                    // Assign to form fields
                    const sitot = calculateTotal("Sales Invoice") || "0.00";
                    const citot = calculateTotal("Charge Invoice") || "0.00";
                    const pytot = calculateTotal("Payment") || "0.00";

                    form.salesInvoiceTotal = formatCurrency(sitot);
                    form.chargeInvoiceTotal = formatCurrency(citot);
                    form.paymentTotal = formatCurrency(pytot);

                    const ovtot = (
                        parseFloat(sitot) +
                        parseFloat(citot) +
                        parseFloat(pytot)
                    ).toFixed(2);

                    // Calculate overall total
                    form.overallTotal = formatCurrency(ovtot);
                }
            } catch (error) {
                console.error("Error fetching invoices:", error);
                allTransactionResults.value = [];
            } finally {
                isLoading.value = false;
            }
        } else {
            allTransactionResults.value = [];
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

const closeModal = () => {
    emit("close");
};
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
