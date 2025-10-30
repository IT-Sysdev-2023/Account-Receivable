<template>
    <Transition name="modal">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60 overflow-y-auto scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full"
        >
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <ConfirmationDialog
                    :show="showDialog"
                    :message="confirmationMessage"
                    @close="handleConfirm"
                />
            </Transition>
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <ConfirmationDialog
                    :show="showDialogDoc"
                    :message="confirmationMessageDoc"
                    @close="handleConfirmDoc"
                />
            </Transition>
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <InformationDialog
                    :show="showInfoDialog"
                    :message="`This Document No Already Has a Total Floating Payment of ${formatCurrency(
                        totalFloatingAmount
                    )} Which is Equal To Its Current Balance. Further Payment is Not Allowed For This Document`"
                    @close="showInfoDialog = false"
                />
            </Transition>

            <ToastAlertWarning :show="showToast" :message="toastMessage" />
            <!-- Modal Container -->
            <div
                class="w-full max-w-7xl overflow-hidden rounded-2xl text-[var(--color-text-primary)] bg-[var(--color-bg-secondary)] border border-[var(--color-border)]"
            >
                <!-- Content -->
                <div class="p-6">
                    <!-- Header -->
                    <div class="mb-3 text-center">
                        <h2 class="text-2xl font-bold tracking-wide">
                            DOCUMENT NUMBER
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent"
                        ></div>
                    </div>

                    <div class="flex justify-between items-start mb-4">
                        <div class="w-1/2 flex flex-col gap-2">
                            <label class="block text-md font-bold"
                                >PAYMENT MODE</label
                            >
                            <div class="flex gap-4">
                                <label
                                    class="w-full inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="payment_mode"
                                        value="Oldest to Newest"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="w-full relative flex items-center justify-start p-2"
                                    >
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/30"
                                            :class="{
                                                'opacity-100':
                                                    payment_mode ===
                                                    'Oldest to Newest',
                                            }"
                                        ></div>
                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-border)] transition-colors z-10"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    payment_mode ===
                                                    'Oldest to Newest',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        payment_mode ===
                                                        'Oldest to Newest',
                                                    'opacity-0':
                                                        payment_mode !==
                                                        'Oldest to Newest',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm z-10"
                                            >Oldest to Newest</span
                                        >
                                    </div>
                                </label>

                                <label
                                    class="w-full inline-flex items-center cursor-pointer group"
                                >
                                    <input
                                        type="radio"
                                        v-model="payment_mode"
                                        value="Manual Select"
                                        class="hidden peer"
                                    />
                                    <div
                                        class="w-full relative flex items-center justify-start p-2"
                                    >
                                        <div
                                            class="absolute -inset-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-[var(--color-border)]/30"
                                            :class="{
                                                'opacity-100':
                                                    payment_mode ===
                                                    'Manual Select',
                                            }"
                                        ></div>
                                        <div
                                            class="relative w-5 h-5 mr-2 rounded-full border-2 border-[var(--color-border)] transition-colors z-10"
                                            :class="{
                                                'border-[var(--color-border)]':
                                                    payment_mode ===
                                                    'Manual Select',
                                            }"
                                        >
                                            <div
                                                class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-[var(--color-border)] transition-opacity"
                                                :class="{
                                                    'opacity-100':
                                                        payment_mode ===
                                                        'Manual Select',
                                                    'opacity-0':
                                                        payment_mode !==
                                                        'Manual Select',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm z-10"
                                            >Manual Select</span
                                        >
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="w-1/3 flex flex-col gap-2">
                            <label class="block text-md font-bold"
                                >FILTER BY TYPE</label
                            >
                            <div class="relative">
                                <DropdownInput
                                    v-model="selectedTypeFilter"
                                    :options="[
                                        'All Types',
                                        'Sales Invoice',
                                        'Charge Invoice',
                                        'Payment',
                                        'BG',
                                    ]"
                                    placeholder="Click to Select"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="mb-4 relative">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search..."
                            ref="searchInput"
                            :readonly="payment_mode === 'Oldest to Newest'"
                            class="w-full rounded-md px-4 py-2 text-[var(--color-text-primary)] border border-[var(--color-border)] read-only:cursor-not-allowed"
                            :class="{
                                '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10':
                                    filteredData.length === 0 &&
                                    searchQuery !== '',
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
                        v-if="payment_mode === 'Oldest to Newest'"
                        class="w-full rounded-xl overflow-hidden border border-[var(--color-border)] backdrop-blur-sm pl-2"
                    >
                        <div
                            v-if="props.paymentType === '5E - Creditable(WHT)'"
                            class="h-82 w-full flex flex-col justify-center items-center gap-2 p-6"
                        >
                            <span
                                ><svg-icon
                                    type="mdi"
                                    :path="mdiAlert"
                                    class="w-20 h-20"
                            /></span>
                            <span
                                class="font-extrabold text-2xl uppercase text-center"
                                >Unsupported payment type. Please try
                                another.</span
                            >
                        </div>
                        <div
                            v-else
                            class="h-82 w-full flex flex-col justify-center items-center gap-2"
                        >
                            <span
                                ><svg-icon
                                    type="mdi"
                                    :path="mdiCalendarClock"
                                    class="w-20 h-20"
                            /></span>
                            <span
                                v-if="filteredData.length !== 0"
                                class="font-extrabold text-2xl"
                                >OLDEST TO NEWEST SELECTED</span
                            >
                            <span v-else class="font-extrabold text-2xl"
                                >NO DATA FOUND FOR THIS CUSTOMER</span
                            >
                        </div>
                    </div>
                    <div
                        v-if="payment_mode === 'Manual Select'"
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
                                        <th class="px-5 py-2 text-left w-[13%]">
                                            DOCUMENT NO
                                        </th>
                                        <th class="px-5 py-2 text-left w-[13%]">
                                            DATE
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[12%]"
                                        >
                                            TYPE
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[12%]"
                                        >
                                            TRADE TYPE
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[20%]"
                                        >
                                            SI/CI AMOUNT
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[20%]"
                                        >
                                            AMOUNT TO PAY
                                        </th>
                                        <th
                                            class="px-5 py-2 text-center w-[10%]"
                                        >
                                            SELECT
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
                                                colspan="6"
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
                                            class="rounded-xl hover:bg-[var(--color-primary)]/20 transition-colors duration-150 group cursor-pointer"
                                        >
                                            <td
                                                class="px-5 py-2 font-medium w-[13%]"
                                                @click="
                                                    (e) =>
                                                        handleCheckboxClick(
                                                            invoice,
                                                            e
                                                        )
                                                "
                                            >
                                                {{ invoice.docunumber }}
                                            </td>
                                            <td
                                                class="px-5 py-2 w-[13%]"
                                                @click="
                                                    (e) =>
                                                        handleCheckboxClick(
                                                            invoice,
                                                            e
                                                        )
                                                "
                                            >
                                                {{ formatDate(invoice.date) }}
                                            </td>
                                            <td
                                                class="px-5 py-2 text-center w-[12%]"
                                                @click="
                                                    (e) =>
                                                        handleCheckboxClick(
                                                            invoice,
                                                            e
                                                        )
                                                "
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
                                                class="px-5 py-2 text-center w-[12%]"
                                                @click="
                                                    (e) =>
                                                        handleCheckboxClick(
                                                            invoice,
                                                            e
                                                        )
                                                "
                                            >
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="[
                                                        invoice.trade_type ===
                                                        'Trade'
                                                            ? 'bg-lime-700 text-lime-300'
                                                            : invoice.trade_type ===
                                                              'Non-Trade'
                                                            ? 'bg-green-700 text-green-300'
                                                            : 'bg-emerald-700 text-emerald-300',
                                                    ]"
                                                >
                                                    {{
                                                        invoice.trade_type ||
                                                        "N/A"
                                                    }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-5 py-2 text-right font-medium w-[20%]"
                                                @click="
                                                    (e) =>
                                                        handleCheckboxClick(
                                                            invoice,
                                                            e
                                                        )
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        invoice.balance
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-5 py-2 text-right font-medium w-[20%]"
                                            >
                                                <TextInput
                                                    type="decimal"
                                                    v-model="
                                                        invoice.amountToPay
                                                    "
                                                    :readonly="
                                                        !isInvoiceSelected(
                                                            invoice
                                                        ) ||
                                                        (props.paymentType ===
                                                            '5E - Creditable(WHT)' &&
                                                            !props.editable_wht)
                                                    "
                                                    :validation="
                                                        isInvoiceSelected(
                                                            invoice
                                                        )
                                                            ? 'yes'
                                                            : 'no'
                                                    "
                                                    @blur="
                                                        validateAmount(invoice)
                                                    "
                                                    @keyup.enter="
                                                        validateAmount(invoice)
                                                    "
                                                />
                                            </td>
                                            <td
                                                class="px-5 py-2 text-center w-[10%]"
                                            >
                                                <label
                                                    class="relative inline-block w-5 h-5"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :checked="
                                                            isInvoiceSelected(
                                                                invoice
                                                            )
                                                        "
                                                        @click="
                                                            (e) =>
                                                                handleCheckboxClick(
                                                                    invoice,
                                                                    e
                                                                )
                                                        "
                                                        class="peer appearance-none w-5 h-5 border-2 rounded-sm border-[var(--color-border)] bg-transparent checked:bg-[var(--color-primary)] checked:!border-[var(--color-primary)] focus:outline-none transition-colors duration-200 cursor-pointer"
                                                        @click.stop
                                                    />
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24"
                                                        class="absolute p-0.5 top-0 left-0 w-5 h-5 text-[var(--color-bg-primary)] hidden peer-checked:block pointer-events-none"
                                                        fill="white"
                                                    >
                                                        <path
                                                            d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"
                                                        />
                                                    </svg>
                                                </label>
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
                                                colspan="6"
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

                    <!-- Footer -->
                    <div
                        class="mt-4 pt-2 border-t border-[var(--color-border)] flex justify-end gap-2"
                    >
                        <button @click="closeModal" class="closeButton group">
                            <div class="flex justify-center items-center gap-2">
                                <span
                                    class="transition-transform duration-300 group-hover:rotate-180"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiClose"
                                        class="w-5 h-5"
                                    />
                                </span>
                                Close
                            </div>
                        </button>
                        <button
                            :disabled="
                                payment_mode === 'Oldest to Newest' &&
                                props.paymentType === '5E - Creditable(WHT)'
                            "
                            @click="submitSelected"
                            class="submitButton group"
                        >
                            <div class="flex justify-center items-center gap-2">
                                <span
                                    class="transition-transform duration-300 group-hover:rotate-405"
                                >
                                    <svg-icon
                                        type="mdi"
                                        :path="mdiNavigationVariantOutline"
                                        class="w-5 h-5"
                                    />
                                </span>
                                <span v-if="payment_mode === 'Manual Select'"
                                    >Select</span
                                >
                                <span v-else>Confirm</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from "vue";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import InformationDialog from "../../Pages/Components/InformationDialog.vue";
import {
    mdiAlert,
    mdiCalendarClock,
    mdiClose,
    mdiMagnify,
    mdiNavigationVariantOutline,
    mdiSignCaution,
} from "@mdi/js";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import { isEmpty } from "lodash";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";

const props = defineProps({
    customer_code: String,
    date: String,
    paymentType: String,
    editable_wht: Boolean,
});

const emit = defineEmits(["close", "submit"]);

const selectedInvoiceNumbers = ref([]);
const selectedInvoiceType = ref([]);
const selectedTotalAmount = ref(0); // Now only stores the invoice amount
const selectedTotalAmountPaid = ref(0); // Now only stores the invoice amount
const selectedDate = ref(null); // Now only stores the invoice date
const pdcfloatingAmount = ref(0);
const haspdcFloating = ref(false);
const dcfloatingAmount = ref(0);
const hasdcFloating = ref(false);
const whtfloatingAmount = ref(0);
const haswhtFloating = ref(false);
const totalFloatingAmount = ref(0);
const isLoading = ref(false);
const payment_mode = ref("Manual Select");
const searchQuery = ref("");
const filteredData = ref([]);
const searchInput = ref(null);
const documents = ref([]);
let debounceTimeout = null;
const showToast = ref(false);
const toastMessage = ref("");
let toastTimeout = null; // to keep track of the timeout
const selectedInvoices = ref([]);
const selectedTypeFilter = ref("All Types");

// Watch for changes in customer_code
watch(
    () => [props.customer_code],
    async ([newCode]) => {
        if (!newCode) {
            filteredData.value = [];
            documents.value = [];
            return;
        }

        try {
            isLoading.value = true;

            const response = await axios.get(
                route("getInvoiceListForPayment"),
                {
                    params: {
                        customer_code: newCode,
                        date: props.date,
                    },
                }
            );

            documents.value = response.data
                .filter((invoice) => invoice.running_balance !== "0.00")
                .map((invoice) => ({
                    docunumber: invoice.invoice_no,
                    date: invoice.receipt_date,
                    type: invoice.type,
                    amount: invoice.amount,
                    amount_paid: invoice.amount_paid,
                    balance: invoice.running_balance,
                    amountToPay: 0.0,
                    trade_type: invoice.trade_type,
                    pdc_floating_amount: invoice.pdc_floating_amount,
                    has_pdc_floating_payments:
                        invoice.has_pdc_floating_payments,
                    dc_floating_amount: invoice.dc_floating_amount,
                    has_dc_floating_payments: invoice.has_dc_floating_payments,
                    wht_floating_amount: invoice.wht_floating_amount,
                    has_wht_floating_payments:
                        invoice.has_wht_floating_payments,
                }));
            filteredData.value = documents.value;
        } catch (error) {
            console.error("Error fetching invoices:", error);
            filteredData.value = [];
            documents.value = [];
        } finally {
            isLoading.value = false;
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

const handleCheckboxClick = (invoice, event) => {
    if (props.paymentType === "5E - Creditable(WHT)") {
        if (
            invoice.amount_paid !== "0.00" ||
            invoice.has_dc_floating_payments ||
            invoice.has_pdc_floating_payments ||
            invoice.has_wht_floating_payments
        ) {
            // Prevent the checkbox from visually changing
            event.preventDefault();
            showWarningToast(
                "A Partial Payment Has Already Been Made For This Document Number"
            );
            return;
        }
    } else {
        const balance = parseFloat(invoice.balance) || 0;
        const pdcfloating = parseFloat(invoice.pdc_floating_amount) || 0;
        const dcfloating = parseFloat(invoice.dc_floating_amount) || 0;
        const whtfloating = parseFloat(invoice.wht_floating_amount) || 0;

        totalFloatingAmount.value = pdcfloating + dcfloating + whtfloating;

        if (balance === totalFloatingAmount.value) {
            event.preventDefault();
            return (showInfoDialog.value = true);
        }
    }

    // Only proceed with normal handling if validation passes
    handleCheckboxChange(invoice);
};

const isInvoiceSelected = (invoice) => {
    return selectedInvoices.value.some(
        (inv) =>
            inv.docunumber === invoice.docunumber && inv.type === invoice.type
    );
};

const handleCheckboxChange = (invoice) => {
    if (props.paymentType === "5E - Creditable(WHT)" && !props.editable_wht) {
        invoice.amountToPay = invoice.balance;
    }
    invoice.amountToPay = invoice.balance;

    const existingIndex = selectedInvoices.value.findIndex(
        (inv) =>
            inv.docunumber === invoice.docunumber && inv.type === invoice.type
    );

    if (existingIndex > -1) {
        // Remove from selection
        selectedInvoices.value.splice(existingIndex, 1);
        invoice.amountToPay = 0.0;
    } else {
        // Add to selection
        selectedInvoices.value.push({
            docunumber: invoice.docunumber,
            type: invoice.type,
            date: invoice.date,
            amount: invoice.amount,
            amount_paid: invoice.amount_paid,
            balance: parseFloat(invoice.balance),
            amountToPay: parseFloat(invoice.amountToPay),
            pdc_floating_amount: parseFloat(invoice.pdc_floating_amount || 0),
            dc_floating_amount: parseFloat(invoice.dc_floating_amount || 0),
            wht_floating_amount: parseFloat(invoice.wht_floating_amount || 0),
            has_pdc_floating_payments: invoice.has_pdc_floating_payments,
            has_dc_floating_payments: invoice.has_dc_floating_payments,
            has_wht_floating_payments: invoice.has_wht_floating_payments,
        });
    }

    updateSelectedValues();
};

const updateSelectedValues = () => {
    if (selectedInvoices.value.length === 0) {
        resetValues();
        return;
    }
    // Calculate total amount
    selectedTotalAmount.value = selectedInvoices.value.reduce(
        (sum, invoice) => sum + invoice.balance,
        0
    );

    // Find earliest date
    selectedDate.value = selectedInvoices.value.reduce(
        (earliest, invoice) =>
            new Date(invoice.date) < new Date(earliest)
                ? invoice.date
                : earliest,
        selectedInvoices.value[0].date
    );

    // Calculate floating amounts
    pdcfloatingAmount.value = selectedInvoices.value.reduce(
        (sum, invoice) => sum + invoice.pdc_floating_amount,
        0
    );
    dcfloatingAmount.value = selectedInvoices.value.reduce(
        (sum, invoice) => sum + invoice.dc_floating_amount,
        0
    );
    whtfloatingAmount.value = selectedInvoices.value.reduce(
        (sum, invoice) => sum + invoice.wht_floating_amount,
        0
    );

    // Check floating payments
    haspdcFloating.value = selectedInvoices.value.some(
        (invoice) => invoice.has_pdc_floating_payments
    );
    hasdcFloating.value = selectedInvoices.value.some(
        (invoice) => invoice.has_dc_floating_payments
    );
    haswhtFloating.value = selectedInvoices.value.some(
        (invoice) => invoice.has_wht_floating_payments
    );

    // Update the simple arrays for display
    selectedInvoiceNumbers.value = selectedInvoices.value.map(
        (inv) => inv.docunumber
    );
    const seenTypes = new Set();
    selectedInvoiceType.value = selectedInvoices.value
        .filter((inv) => {
            if (seenTypes.has(inv.type)) return false;
            seenTypes.add(inv.type);
            return true;
        })
        .map((inv) => inv.type);
};

const resetValues = () => {
    selectedInvoices.value = [];
    selectedInvoiceNumbers.value = [];
    selectedInvoiceType.value = [];
    selectedTotalAmount.value = 0;
    selectedDate.value = null;
    pdcfloatingAmount.value = 0;
    haspdcFloating.value = false;
    dcfloatingAmount.value = 0;
    hasdcFloating.value = false;
    whtfloatingAmount.value = 0;
    haswhtFloating.value = false;
};

const validateAmount = (invoice) => {
    if (!isInvoiceSelected(invoice)) return;

    const amount = parseFloat(invoice.amountToPay) || 0;
    const balance = parseFloat(invoice.balance) || 0;
    const pdcfloating = parseFloat(invoice.pdc_floating_amount) || 0;
    const dcfloating = parseFloat(invoice.dc_floating_amount) || 0;
    const whtfloating = parseFloat(invoice.wht_floating_amount) || 0;

    const realBalance = balance - (pdcfloating + dcfloating + whtfloating);
    if (amount > realBalance) {
        showWarningToast(
            "Amount Exceeds Available Balance Automatically Adjusted"
        );
        // Reset to balance if exceeds
        invoice.amountToPay = realBalance.toFixed(2);
    }
    if (/^\d+\.\d{3}$/.test(invoice.amountToPay)) {
        invoice.amountToPay = parseFloat((+invoice.amountToPay).toFixed(2));
    }
};

watch(
    () => documents.value?.map((inv) => inv.amountToPay),
    () => {
        documents.value?.forEach(validateAmount);
    },
    { deep: true }
);

////////SHOW DOCU DIALOG ///////////////////////////
const confirmationMessageDoc = ref(null);
const showDialogDoc = ref(false);
const inv = ref(null);
const eve = ref(null);
const showDocMessage = async (invoice, event) => {
    if (
        invoice.amount_paid !== "0.00" ||
        invoice.has_dc_floating_payments ||
        invoice.has_pdc_floating_payments ||
        invoice.has_wht_floating_payments
    ) {
        handleCheckboxClick(invoice, event);
    } else {
        confirmationMessageDoc.value =
            "No WHT payment has been made for this document. Do you want to proceed?";
        showDialogDoc.value = true;
        inv.value = invoice;
        eve.value = event;
    }
};
const handleConfirmDoc = async (confirmed) => {
    showDialogDoc.value = false;
    if (confirmed) {
        if (!isInvoiceSelected(inv.value)) {
            handleCheckboxClick(inv.value, eve.value);
        }
    } else {
        handleCheckboxClick(inv.value, eve.value);
    }
    inv.value = "";
    eve.value = "";
};

const confirmationMessage = computed(() => {
    const messages = [];

    if (pdcfloatingAmount.value) {
        messages.push(
            `Total PDC Floating Payment of ${formatCurrency(
                pdcfloatingAmount.value
            )}`
        );
    }
    if (dcfloatingAmount.value) {
        messages.push(
            `Total DC Floating Payment of ${formatCurrency(
                dcfloatingAmount.value
            )}`
        );
    }
    if (whtfloatingAmount.value) {
        messages.push(
            `Total WHT Floating Payment of ${formatCurrency(
                whtfloatingAmount.value
            )}`
        );
    }

    if (payment_mode.value === "Manual Select") {
        return messages.length > 0
            ? `This Document Number/s Has Existing ${messages.join(
                  " and "
              )}. Proceed?`
            : "";
    } else {
        return messages.length > 0
            ? `This Customer Has Existing Documents with ${messages.join(
                  " and "
              )}. Proceed?`
            : "";
    }
});

////////SHOW DIALOG ///////////////////////////
const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        if (payment_mode.value === "Manual Select") {
            emit("submit", {
                invoiceNumber: selectedInvoiceNumbers.value.join(", "),
                totalAmount: selectedTotalAmount.value,
                date: selectedDate.value,
                floatingAmount: totalFloatingAmount.value,
                type: selectedInvoiceType.value.join(", "),
                totalAmountPaid: selectedTotalAmountPaid.value,
                selectedDocuments: selectedInvoices.value.map((inv) => {
                    const matched = documents.value.find(
                        (doc) =>
                            doc.docunumber === inv.docunumber &&
                            doc.type === inv.type
                    );

                    return {
                        docunumber: inv.docunumber,
                        type: inv.type,
                        date: inv.date,
                        amount: inv.amount,
                        balance: inv.balance,
                        amountToPay: parseFloat(matched?.amountToPay) || 0,
                    };
                }),
            });
        } else {
            emit("submit", {
                invoiceNumber: selectedInvoiceNumbers.value.join(", "),
                totalAmount: selectedTotalAmount.value,
                date: selectedDate.value,
                floatingAmount: totalFloatingAmount.value,
                type: selectedInvoiceType.value.join(", "),
            });
        }

        closeModal();
    }
};
////////SHOW INFO DIALOG///////////////////////////
const showInfoDialog = ref(false);

const submitSelected = () => {
    if (payment_mode.value === "Manual Select") {
        if (selectedInvoices.value.length === 0) {
            return showWarningToast(
                "Please Select At Least One Document Number"
            );
        }

        const hasZeroAmount = selectedInvoices.value.some((inv) => {
            const matched = documents.value.find(
                (doc) =>
                    doc.docunumber === inv.docunumber && doc.type === inv.type
            );
            return parseFloat(matched?.amountToPay || 0) === 0;
        });

        if (hasZeroAmount) {
            return showWarningToast(
                "Please Provide An Amount To Pay For Every Selected Invoice"
            );
        }

        selectedTotalAmountPaid.value = documents.value.reduce(
            (sum, invoice) => sum + parseFloat(invoice.amountToPay),
            0
        );

        totalFloatingAmount.value = parseFloat(
            pdcfloatingAmount.value +
                dcfloatingAmount.value +
                whtfloatingAmount.value
        );
        if (
            haspdcFloating.value ||
            hasdcFloating.value ||
            haswhtFloating.value
        ) {
            const remainingBalance =
                totalFloatingAmount.value - selectedTotalAmountPaid.value;
            if (remainingBalance === 0) {
                showInfoDialog.value = true;
            } else {
                showDialog.value = true;
            }
        } else {
            emit("submit", {
                invoiceNumber: selectedInvoiceNumbers.value.join(", "),
                totalAmount: selectedTotalAmount.value,
                date: selectedDate.value,
                type: selectedInvoiceType.value.join(", "),
                totalAmountPaid: selectedTotalAmountPaid.value,
                editable_wht_mode: props.editable_wht,
                selectedDocuments: selectedInvoices.value.map((inv) => {
                    const matched = documents.value.find(
                        (doc) =>
                            doc.docunumber === inv.docunumber &&
                            doc.type === inv.type
                    );

                    return {
                        docunumber: inv.docunumber,
                        type: inv.type,
                        date: inv.date,
                        amount: inv.amount,
                        balance: inv.balance,
                        amountToPay: parseFloat(matched?.amountToPay) || 0,
                    };
                }),
            });
            // closeModal();
        }
    } else {
        const invoicesToSelect = documents.value;

        if (invoicesToSelect.length === 0) {
            return showWarningToast("No Documents Available For This Customer");
        }

        selectedInvoiceNumbers.value = ["Oldest to Newest Applied"];

        const seenTypes = new Set();

        if (props.paymentType === "5E - Creditable(WHT)") {
            selectedInvoiceType.value = invoicesToSelect
                .filter((invoice) => invoice.has_dc_floating_payments === false)
                .filter(
                    (invoice) => invoice.has_pdc_floating_payments === false
                )
                .filter(
                    (invoice) => invoice.has_wht_floating_payments === false
                )
                .filter((invoice) => invoice.amount_paid === "0.00")
                .filter((inv) => {
                    if (seenTypes.has(inv.type)) return false;
                    seenTypes.add(inv.type);
                    return true;
                })
                .map((inv) => inv.type);

            if (isEmpty(selectedInvoiceType.value)) {
                return showWarningToast(
                    "No Documents Available For WHT Payment type"
                );
            }

            selectedTotalAmount.value = invoicesToSelect
                .filter((invoice) => invoice.has_dc_floating_payments === false)
                .filter(
                    (invoice) => invoice.has_pdc_floating_payments === false
                )
                .filter(
                    (invoice) => invoice.has_wht_floating_payments === false
                )
                .filter((invoice) => invoice.amount_paid === "0.00")
                .reduce((sum, invoice) => sum + parseFloat(invoice.balance), 0);

            selectedDate.value = invoicesToSelect
                .filter((invoice) => invoice.has_dc_floating_payments === false)
                .filter(
                    (invoice) => invoice.has_pdc_floating_payments === false
                )
                .filter(
                    (invoice) => invoice.has_wht_floating_payments === false
                )
                .filter((invoice) => invoice.amount_paid === "0.00")
                .reduce(
                    (earliest, invoice) =>
                        new Date(invoice.date) < new Date(earliest)
                            ? invoice.date
                            : earliest,
                    invoicesToSelect[0].date
                );
        } else {
            selectedInvoiceType.value = invoicesToSelect
                .filter((inv) => {
                    if (seenTypes.has(inv.type)) return false;
                    seenTypes.add(inv.type);
                    return true;
                })
                .map((inv) => inv.type);

            selectedTotalAmount.value = invoicesToSelect.reduce(
                (sum, invoice) => sum + parseFloat(invoice.balance),
                0
            );

            selectedDate.value = invoicesToSelect.reduce(
                (earliest, invoice) =>
                    new Date(invoice.date) < new Date(earliest)
                        ? invoice.date
                        : earliest,
                invoicesToSelect[0].date
            );

            // Calculate floating amounts
            pdcfloatingAmount.value = invoicesToSelect.reduce(
                (sum, invoice) =>
                    sum + parseFloat(invoice.pdc_floating_amount || 0),
                0
            );
            dcfloatingAmount.value = invoicesToSelect.reduce(
                (sum, invoice) =>
                    sum + parseFloat(invoice.dc_floating_amount || 0),
                0
            );
            whtfloatingAmount.value = invoicesToSelect.reduce(
                (sum, invoice) =>
                    sum + parseFloat(invoice.wht_floating_amount || 0),
                0
            );

            // Check floating payments
            haspdcFloating.value = invoicesToSelect.some(
                (invoice) => invoice.has_pdc_floating_payments
            );
            hasdcFloating.value = invoicesToSelect.some(
                (invoice) => invoice.has_dc_floating_payments
            );
            haswhtFloating.value = invoicesToSelect.some(
                (invoice) => invoice.has_wht_floating_payments
            );

            totalFloatingAmount.value = parseFloat(
                pdcfloatingAmount.value +
                    dcfloatingAmount.value +
                    whtfloatingAmount.value
            );
        }

        // Handle floating payments confirmation
        if (props.paymentType === "5E - Creditable(WHT)") {
            emit("submit", {
                invoiceNumber: selectedInvoiceNumbers.value.join(", "),
                totalAmount: selectedTotalAmount.value,
                date: selectedDate.value,
                type: selectedInvoiceType.value.join(", "),
            });
            closeModal();
        } else {
            if (
                haspdcFloating.value ||
                hasdcFloating.value ||
                haswhtFloating.value
            ) {
                const remainingBalance =
                    selectedTotalAmount.value - totalFloatingAmount.value;
                if (remainingBalance === 0) {
                    showInfoDialog.value = true;
                } else {
                    showDialog.value = true;
                }
            } else {
                emit("submit", {
                    invoiceNumber: selectedInvoiceNumbers.value.join(", "),
                    totalAmount: selectedTotalAmount.value,
                    date: selectedDate.value,
                    type: selectedInvoiceType.value.join(", "),
                });
                closeModal();
            }
        }
    }
};

////////// SHOW TOAST ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

const closeModal = () => {
    emit("close");
};

//SEARCH FUNCTION
const clearSearch = () => {
    searchQuery.value = ""; // Clear search input

    nextTick(() => {
        searchInput.value?.focus();
    });
};

// Debounced search
watch(
    () => [searchQuery.value, selectedTypeFilter.value],
    ([query, typeFilter]) => {
        isLoading.value = true;
        if (debounceTimeout) clearTimeout(debounceTimeout);

        debounceTimeout = setTimeout(() => {
            let filtered = documents.value;

            // Apply search filter
            if (query && query.trim()) {
                filtered = filtered.filter((document) =>
                    document.docunumber
                        ?.toString()
                        .toLowerCase()
                        .includes(query.toLowerCase())
                );
            }

            // Apply type filter
            if (typeFilter !== 'All Types') {
                filtered = filtered.filter(
                    (document) => document.type === typeFilter
                );
            }

            filteredData.value = filtered;
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
