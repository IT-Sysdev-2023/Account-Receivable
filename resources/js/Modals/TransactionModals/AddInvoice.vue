<template>
    <div v-if="show"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4 overflow-y-auto scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full">
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ManagersKey :show="showManagerModal" @success="onManagerSuccess" @cancel="onManagerCancel" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <CashPaymentModal :show="showCashModal" :formData="form" @closeSuccess="handlePaymentNo"
                @close="closeCashPaymentModal" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <CustomerListModal v-if="showCustomerModal" :show="showCustomerModal" @close="showCustomerModal = false"
                @submit="handleSelectedCustomer" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <SelectionTableTwo v-if="showItemCodeModal" :title="'Select Item'" :data="itemOptions" firstHeader="CODE"
                secondHeader="NAME" thirdHeader="PHOTO" @close="showItemCodeModal = false" @submit="onSelectItemCode" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <SelectionTableTwo v-if="showPackingModal" :title="'Select Packing'" :data="packingOptions"
                firstHeader="PACK" secondHeader="PRICE" @close="showPackingModal = false" @submit="onSelectPacking" />
        </Transition>

        <ToastAlertWarning :show="showToast" :message="toastMessage" />

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ConfirmationDialog :show="showDialog" message="Do you want to print the Document?"
                @close="handleConfirm" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <PdfPreviewModal v-if="showPdfModal" :show="showPdfModal" :apiEndpoint="apiRoute" :formData="pdfFormData"
                @closeSuccess="pdfPrintSuccess" @close="pdfPrintSuccess" />
        </Transition>

        <transition @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter" @before-leave="beforeLeave"
            @leave="leave">
            <form v-if="isExpanded" ref="formElement" @submit.prevent="submit"
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-7xl rounded-2xl border border-[var(--color-border)]">
                <!-- Show spinner while loading -->
                <div v-if="modalLoading" class="flex justify-center items-center py-20">
                    <svg width="40" height="40" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                        fill="var(--color-icon)">
                        <rect class="spinner_jCIR" x="1" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_upm8" x="5.8" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_2eL5" x="10.6" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_Rp9l" x="15.4" y="6" width="2.8" height="12" />
                        <rect class="spinner_jCIR spinner_dy3W" x="20.2" y="6" width="2.8" height="12" />
                    </svg>
                </div>
                <div v-else class="px-8 py-6">
                    <!-- Header -->
                    <div class="px-8 pb-4">
                        <h2 class="text-2xl font-bold text-center">
                            ADD NEW TEMPORARY CHARGE INVOICE
                        </h2>
                        <div
                            class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent">
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6 px-4">
                        <!-- Right: Form Fields -->
                        <div class="w-full grid grid-cols-1 md:grid-cols-4 gap-4">
                            <TextInput label="Invoice Number" v-model="form.invoice_no" type="text" readonly
                                :message="form.errors.invoice_no" />
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Receipt
                                    Date</label>
                                <DatePicker v-model="form.receipt_date" placeholder="Select Date" format="MM/DD/YYYY"
                                    :message="form.errors.receipt_date" />
                            </div>
                            <TextInput label="Transaction Date" v-model="form.transaction_date" type="date" readonly
                                :message="form.errors.transaction_date" />

                            <TextInput label="Customer Code" type="text" v-model="form.customer_code"
                                @click="onCustomerClick()" :message="form.errors.customer_code"
                                :default-placeholder="'Click to Select'" selectable="yes" />

                            <TextInput label="Customer Name" v-model="form.name" type="text" readonly
                                :message="form.errors.name" class="col-span-2" />
                            <TextInput label="Price Group" v-model="form.price_group" type="text" readonly
                                :message="form.errors.price_group" />
                            <DropdownInput label="Payment Mode" v-model="form.payment_mode"
                                :options="['Account Receivables', 'Cash']" :message="form.errors.payment_mode"
                                :disabled="!form.name ||
                                    form.customer_code === 'TAG-00972'
                                    " placeholder="Click to Select" disabledPlaceholder="Select Customer First" />
                            <DropdownInput label="Charge Invoice Type" v-model="form.chargeinvoice_type"
                                :options="typeOptions" :message="form.errors.chargeinvoice_type"
                                :disabled="!form.payment_mode" placeholder="Click to Select"
                                disabledPlaceholder="Select Payment Mode First" />
                            <TextInput label="Particular" v-model="form.particular" type="text"
                                :message="form.errors.particular" />
                            <TextInput label="Reference Number" v-model="form.reference_no" type="text"
                                :message="form.errors.reference_no" />
                            <TextInput label="Total Amount" v-model="form.total_amount" type="text" readonly
                                :message="form.errors.total_amount" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button v-if="rows[0]?.saved" type="button" @click="addRow()"
                            class="px-2 py-1 rounded-md font-medium transition-all duration-300 flex items-center justify-center gap-2 relative overflow-hidden bg-[var(--color-primary)] text-white hover:bg-transparent hover:text-[var(--color-primary)] hover:ring-1 hover:ring-[var(--color-primary)] disabled:opacity-70 disabled:cursor-not-allowed group">
                            <div class="relative flex items-center justify-center gap-1 text-xs">
                                <span class="transition-transform duration-300 group-hover:rotate-180">
                                    <svg-icon type="mdi" :path="mdiPlus" class="w-3 h-3" />
                                </span>

                                Add Row
                            </div>
                        </button>
                    </div>

                    <transition @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter"
                        @before-leave="beforeLeave" @leave="leave">
                        <div v-if="form.chargeinvoice_type">
                            <div class="rounded-xl bg-[var(--color-primary)]/20 backdrop-blur-sm mt-2">
                                <div class="sticky top-0 z-10 px-2">
                                    <!-- ref="tableWrapper" -->
                                    <table class="w-full text-[var(--color-text-primary)] text-sm">
                                        <thead>
                                            <tr
                                                class="text-sm uppercase tracking-wider text-[var(--color-text-primary)]">
                                                <th class="px-2 py-3 text-left w-[14%]">
                                                    Item Code
                                                </th>
                                                <th class="px-2 py-3 text-left w-[22%]">
                                                    Item Name
                                                </th>
                                                <th class="px-2 py-3 text-left w-[15%]">
                                                    Packing
                                                </th>
                                                <th class="px-2 py-3 text-center w-[13%]">
                                                    Quantity
                                                </th>
                                                <th class="px-2 py-3 text-center w-[13%]">
                                                    Price
                                                </th>
                                                <th class="px-2 py-3 text-center w-[13%]">
                                                    Amount
                                                </th>
                                                <th class="px-2 py-3 text-left w-[10%]">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="relative">
                                    <div
                                        class="overflow-y-auto max-h-[200px] scrollbar-thin scrollbar-stable [scrollbar-gutter:stable] pl-2 pb-2">
                                        <table class="w-full text-[var(--color-text-primary)] text-sm">
                                            <tbody class="rounded-xl">
                                                <tr v-for="(row, index) in rows" :key="index" :class="[
                                                    'transition-all duration-300 ease-in-out rounded-md text-sm',
                                                    editingIndex === index
                                                        ? 'bg-[var(--color-primary)]/20 border border-[var(--color-border)]'
                                                        : '',
                                                    index ===
                                                        rows.length - 1 &&
                                                        index !== 0
                                                        ? 'animate-fade-in'
                                                        : '',
                                                ]">
                                                    <!-- Item Code -->
                                                    <td class="px-2 py-1 w-[14%]">
                                                        <div class="relative">
                                                            <input v-if="
                                                                editingIndex ===
                                                                index
                                                            " v-model="row.item_code
                                                                    " type="text" @click="
                                                                    openItemCodeSelection(
                                                                        index
                                                                    )
                                                                    " readonly :class="[
                                                                    'cursor-pointer',
                                                                    row.item_code
                                                                        ? 'border-[var(--color-border)]'
                                                                        : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                                ]" placeholder="Click To Select" />
                                                            <span v-else>{{
                                                                row.item_code
                                                                }}</span>

                                                            <!-- Clear button -->
                                                            <button type="button" v-if="
                                                                editingIndex ===
                                                                index &&
                                                                row.item_code &&
                                                                !row.saved
                                                            " @click.stop="
                                                                    clearItemCode(
                                                                        index
                                                                    )
                                                                    "
                                                                class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)]"
                                                                title="Clear">
                                                                <svg-icon type="mdi" :path="mdiClose
                                                                    " class="w-4 h-4 hover:text-red-500" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <!-- Item Name -->
                                                    <td class="px-2 py-1 w-[22%]">
                                                        <input v-if="
                                                            editingIndex ===
                                                            index
                                                        " v-model="row.item_name
                                                                " type="text" readonly="true" disabled="true" :class="[
                                                                row.item_name
                                                                    ? 'border-[var(--color-border)]'
                                                                    : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                            ]" />
                                                        <span v-else>{{
                                                            row.item_name
                                                            }}</span>
                                                    </td>
                                                    <!-- Packing -->
                                                    <td class="px-2 py-1 w-[15%]">
                                                        <div class="relative">
                                                            <input v-if="
                                                                editingIndex ===
                                                                index
                                                            " v-model="row.packing
                                                                    " type="text" @click="
                                                                    openPackingSelection(
                                                                        index
                                                                    )
                                                                    " readonly :disabled="!row
                                                                        .item_code[
                                                                    index
                                                                    ]
                                                                    " :class="[
                                                                    'cursor-pointer',
                                                                    row.packing
                                                                        ? 'border-[var(--color-border)]'
                                                                        : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                                ]" :placeholder="[
                                                                    !row
                                                                        .item_code[
                                                                        index
                                                                    ]
                                                                        ? 'Item Code Required'
                                                                        : 'Click To Select',
                                                                ]" />
                                                            <span v-else>{{
                                                                row.packing
                                                                }}</span>

                                                            <!-- Clear button -->
                                                            <button type="button" v-if="
                                                                editingIndex ===
                                                                index &&
                                                                row.packing &&
                                                                !row.saved
                                                            " @click.stop="
                                                                    clearPacking(
                                                                        index
                                                                    )
                                                                    "
                                                                class="absolute top-1/2 right-2 transform -translate-y-1/2 text-[var(--color-text-primary)]"
                                                                title="Clear">
                                                                <svg-icon type="mdi" :path="mdiClose
                                                                    " class="w-4 h-4 hover:text-red-500" />
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <!-- Quantity -->
                                                    <td class="px-2 py-1 text-center w-[13%]">
                                                        <input v-if="
                                                            editingIndex ===
                                                            index
                                                        " :ref="(el) =>
                                                                    setQuantityInputRef(
                                                                        el,
                                                                        index
                                                                    )
                                                                " v-model="row.quantity
                                                                " @input="
                                                                updateAmount(
                                                                    index
                                                                )
                                                                " type="number" inputmode="numeric" min="0" step="1"
                                                            :readonly="!row.packing
                                                                " :placeholder="row.packing
                                                                    ? '0'
                                                                    : ''
                                                                " :class="[
                                                                row.quantity
                                                                    ? 'border-[var(--color-border)]'
                                                                    : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                            ]" />
                                                        <span v-else>{{
                                                            row.quantity
                                                            }}</span>
                                                    </td>

                                                    <!-- Price -->
                                                    <td class="px-2 py-1 text-center w-[13%]">
                                                        <input v-if="
                                                            editingIndex ===
                                                            index
                                                        " v-model="row.price" type="number" readonly="true"
                                                            disabled="true" inputmode="decimal" min="0" step="0.01"
                                                            :class="[
                                                                row.price
                                                                    ? 'border-[var(--color-border)]'
                                                                    : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                            ]" />
                                                        <span v-else>{{
                                                            row.price
                                                            }}</span>
                                                    </td>

                                                    <!-- Amount -->
                                                    <td class="px-2 py-1 text-center w-[13%]">
                                                        <input v-if="
                                                            editingIndex ===
                                                            index
                                                        " v-model="row.amount" type="text" readonly="true"
                                                            disabled="true" :class="[
                                                                row.amount
                                                                    ? 'border-[var(--color-border)]'
                                                                    : '!border-red-400 !ring-2 !ring-red-500/50 bg-red-900/10',
                                                            ]" :placeholder="row.packing
                                                                    ? formatCurrency(
                                                                        0.0
                                                                    )
                                                                    : ''
                                                                " />
                                                        <span v-else>{{
                                                            row.amount
                                                            }}</span>
                                                    </td>

                                                    <!-- Actions -->
                                                    <td class="px-2 py-1 w-[10%]">
                                                        <div class="flex items-center gap-2 w-full">
                                                            <!-- Save -->
                                                            <button v-if="
                                                                editingIndex ===
                                                                index &&
                                                                !row.saved
                                                            " @click.prevent="
                                                                    saveRow(
                                                                        index
                                                                    )
                                                                    " title="Save"
                                                                class="bg-blue-600 hover:bg-blue-700 cursor-pointer text-white p-1.5 rounded-full transition group">
                                                                <div class="flex justify-center items-center gap-2">
                                                                    <span
                                                                        class="transition-transform duration-300 group-hover:rotate-360">
                                                                        <svg-icon type="mdi" :path="mdiCheck
                                                                            " class="w-4 h-4" />
                                                                    </span>
                                                                </div>
                                                            </button>

                                                            <!-- Edit -->
                                                            <button v-else @click.prevent="
                                                                editRow(
                                                                    index
                                                                )
                                                                " title="Edit"
                                                                class="bg-green-600 hover:bg-green-700 cursor-pointer text-white p-1.5 rounded-full transition group">
                                                                <div class="flex justify-center items-center gap-2">
                                                                    <span
                                                                        class="transition-transform duration-300 group-hover:rotate-360">
                                                                        <svg-icon type="mdi" :path="mdiPencil
                                                                            " class="w-4 h-4" />
                                                                    </span>
                                                                </div>
                                                            </button>

                                                            <!-- Delete -->
                                                            <!-- v-if="
                                                                    (editingIndex !==
                                                                        index &&
                                                                        index !==
                                                                            rows.length -
                                                                                1) ||
                                                                    row.saved
                                                                " -->
                                                            <button @click.prevent="
                                                                deleteRow(
                                                                    index
                                                                )
                                                                " title="Delete"
                                                                class="bg-red-600 hover:bg-red-700 cursor-pointer text-white p-1.5 rounded-full transition group">
                                                                <div class="flex justify-center items-center gap-2">
                                                                    <span
                                                                        class="transition-transform duration-300 group-hover:rotate-180">
                                                                        <svg-icon type="mdi" :path="mdiClose
                                                                            " class="w-4 h-4" />
                                                                    </span>
                                                                </div>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-2 pt-2 border-t border-[var(--color-border)] mt-4">
                        <button type="button" @click="closeModal" class="closeButton group">
                            <div class="flex justify-center items-center gap-2">
                                <span class="transition-transform duration-300 group-hover:rotate-180">
                                    <svg-icon type="mdi" :path="mdiClose" class="w-5 h-5" />
                                </span>
                                Close
                            </div>
                        </button>
                        <button type="submit" class="submitButton group" :disabled="form.processing || submitDisable">
                            <div class="flex justify-center items-center gap-2">
                                <span class="transition-transform duration-300 group-hover:rotate-405">
                                    <svg-icon type="mdi" :path="mdiNavigationVariantOutline" class="w-5 h-5" />
                                </span>
                                <span v-if="form.processing || submitDisable">Submitting...</span>
                                <span v-else>Submit</span>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </transition>
    </div>
</template>

<script setup>
import {
    computed,
    ref,
    watch,
    onMounted,
    onUnmounted,
    onBeforeUnmount,
    nextTick,
    readonly,
} from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ManagersKey from "../ManagersKey.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import axios from "axios";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import CashPaymentModal from "./CashPaymentModal.vue";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";
import {
    mdiCheck,
    mdiClose,
    mdiNavigationVariantOutline,
    mdiPencil,
    mdiPlus,
} from "@mdi/js";
import CustomerListModal from "./CustomerListModal.vue";
import SelectionTableTwo from "../../Pages/Components/SelectionTableTwo.vue";
import PdfPreviewModal from "../PdfPreviewModal.vue";
import DatePicker from "../../Pages/Components/DatePicker.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    invoice_no: null,
    receipt_date: null,
    transaction_date: null,
    customer_code: null,
    name: null,
    price_group: null,
    payment_mode: null,
    chargeinvoice_type: null,
    particular: null,
    reference_no: null,
    total_amount: null,
    payment_no: null,
    invoices: [],
});

const rows = ref([
    {
        item_code: "",
        item_name: "",
        packing: "",
        quantity: "",
        price: "",
        amount: "",
        saved: false,
    },
]);

const { canPrint } = usePermissions();

const showManagerModal = ref(false);
const pendingOldDate = ref(null);
const typeOptions = ref([]);
const modalLoading = ref(false);
const showCashModal = ref(false);
const showItemCodeModal = ref(false);
const selectedRowIndex = ref(null);
const butsItemCode = ref([]);
const itemOptions = ref([]);
const butsPacking = ref([]);
const packingOptions = ref([]);
const quantityInputs = ref([]);
const showPackingModal = ref(false);
const showDialog = ref(false);
const showCustomerModal = ref(false);
const currentItemCode = ref(null);
const currentPacking = ref(null);

const submitDisable = ref(false);

butsPacking.value = rows.value.map(() => ref(false));
butsItemCode.value = rows.value.map(() => ref(false));

const editingIndex = ref(rows.value.length - 1); // default to last row

form.transaction_date = new Date().toISOString().split("T")[0];

function daysBetween(date1, date2) {
    const diffTime = Math.abs(date2 - date1);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

function openManagerModal(date) {
    pendingOldDate.value = date;
    showManagerModal.value = true;
}

function onManagerSuccess() {
    showManagerModal.value = false;
    pendingOldDate.value = null;
}

function onManagerCancel() {
    form.receipt_date = new Date().toISOString().split("T")[0];
    showManagerModal.value = false;
    pendingOldDate.value = null;
}

function onCashPaymentSuccess() {
    showCashModal.value = false;

    form.invoices = rows.value.filter(
        (row) =>
            row.item_code.trim() !== "" &&
            row.item_name.trim() !== "" &&
            row.packing.trim() !== "" &&
            row.quantity &&
            row.price &&
            row.amount
    );

    form.post(route("addInvoice"), {
        onSuccess: () => {
            axios.get(route("invoice.latest.invoiceNumber")).then((res) => {
                form.invoice_no = res.data.invoice_number;
                showDialog.value = true;
            });
        },
        onError: (errors) => {
            handleFormErrors(errors);
        },
    });
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

//////////////////////////////////////// SHOW TOAST /////////////////////////////////////////////////////////////////////////////////////////
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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
const addRow = () => {
    rows.value.push({
        item_code: "",
        item_name: "",
        packing: "",
        quantity: "",
        price: "",
        amount: "",
        saved: false,
        _new: true,
    });
    editingIndex.value = rows.value.length - 1;

    setTimeout(() => {
        delete rows.value[rows.value.length - 1]._new;
    }, 400);
};

const editRow = (index) => {
    editingIndex.value = index;
    rows.value[index].saved = false;
};

const saveRow = (index) => {
    const currentRow = rows.value[index];

    // Validation: if any field is empty, don't proceed
    if (
        currentRow.item_code.trim() === "" ||
        currentRow.item_name.trim() === "" ||
        currentRow.packing.trim() === "" ||
        currentRow.quantity === "" ||
        currentRow.price === "" ||
        currentRow.amount === ""
    ) {
        showWarningToast("Please fill in all table fields before saving");
        return;
    }

    // After saving, mark the row as saved
    currentRow.saved = true;

    // Exit editing mode after validation
    // editingIndex.value = rows.value.length - 1;
    const indexSaved = rows.value.findIndex((row) => row.saved === false);
    editingIndex.value = indexSaved !== -1 ? indexSaved : rows.value.length;

    // If it's the last row, push a new empty row
    // if (index === rows.value.length - 1) {
    //     rows.value.push({
    //         item_code: "",
    //         item_name: "",
    //         packing: "",
    //         quantity: "",
    //         price: "",
    //         amount: "",
    //         saved: false,
    //         _new: true,
    //     });
    //     editingIndex.value = rows.value.length - 1;

    //     setTimeout(() => {
    //         delete rows.value[rows.value.length - 1]._new;
    //     }, 400);
    // }
};

const deleteRow = (index) => {
    // if (index !== rows.value.length - 1) {
    //     rows.value.splice(index, 1);
    // }
    rows.value.splice(index, 1);
    // editingIndex.value = rows.value.length - 1;
    const indexSaved = rows.value.findIndex((row) => row.saved === false);
    editingIndex.value = indexSaved !== -1 ? indexSaved : rows.value.length;
    computeTotalAmount();
    if (rows.value.length === 0) {
        addRow();
    }
};

const deleteAllRows = () => {
    rows.value = [
        {
            item_code: "",
            item_name: "",
            packing: "",
            quantity: null,
            price: null,
            amount: null,
            saved: false,
        },
    ];
    form.total_amount = null;
    editingIndex.value = 0; // reset editingIndex if you are using it
};

/////// CUSTOMER CODE
function onCustomerClick() {
    showCustomerModal.value = true;
}
const handleSelectedCustomer = async (selectedData) => {
    form.customer_code = selectedData.cus_code;
    form.name = selectedData.cus_name;
    form.price_group = selectedData.price_group;
    if (form.customer_code === "TAG-00972") {
        await nextTick();
        form.payment_mode = "Cash";
    }

    showCustomerModal.value = false;
};

//item code
const fetchItemCode = async () => {
    try {
        const response = await axios.get(route("getItemList"), {
            params: {
                type: form.chargeinvoice_type,
            },
        });
        itemOptions.value = response.data.map((item) => ({
            firstData: item.code,
            secondData: item.name,
            thirdData: item.item_photo,
        }));
    } catch (error) {
        console.error("Error fetching Item List :", error);
        itemOptions.value = [];
    }
};
const onSelectItemCode = (selected) => {
    if (selectedRowIndex.value !== null) {
        rows.value[selectedRowIndex.value].item_code = selected.firstData;
        rows.value[selectedRowIndex.value].item_name = selected.secondData;
        butsItemCode.value[selectedRowIndex.value] = true;
        showItemCodeModal.value = false;
        selectedRowIndex.value = null;
    }
};

const clearItemCode = (index) => {
    rows.value[index].item_code = ""; // Clear the groupcode value
    rows.value[index].item_name = ""; // Clear the  value
    rows.value[index].packing = ""; // Clear the  value
    rows.value[index].price = ""; // Clear the  value
    rows.value[index].quantity = "";
    rows.value[index].amount = "";
    butsItemCode.value[index] = false;
};

const openItemCodeSelection = async (index) => {
    selectedRowIndex.value = index;
    itemOptions.value = [];

    if (rows.value.length === 1) {
        await fetchItemCode();
    } else {
        const hasAnyItemCode = rows.value.some((row) => row.item_code?.trim());

        if (hasAnyItemCode) {
            const firstValidRow = rows.value.find((row) =>
                row.item_code?.trim()
            );
            itemOptions.value = [
                {
                    firstData: firstValidRow.item_code,
                    secondData: firstValidRow.item_name,
                    thirdData: firstValidRow.item_photo,
                },
            ];
        } else {
            await fetchItemCode();
        }
    }

    showItemCodeModal.value = true;
};

///packing\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

const fetchPacking = async (index) => {
    try {
        const itemCode = rows.value[index].item_code;
        const response = await axios.get(route("getPackingList"), {
            params: {
                itmcode: itemCode,
                price_group: form.price_group,
            },
        });
        packingOptions.value = response.data.map((pack) => ({
            firstData: pack.packing,
            secondData: pack.price,
        }));
    } catch (error) {
        console.error("Error fetching Item List :", error);
        packingOptions.value = [];
    }
};

const onSelectPacking = async (selected) => {
    if (selectedRowIndex.value !== null) {
        const index = selectedRowIndex.value;

        // Update the row data
        rows.value[index].packing = selected.firstData;
        rows.value[index].price = selected.secondData;
        butsPacking.value[index] = true;
        showPackingModal.value = false;

        // Wait for Vue to update the DOM
        await nextTick();

        // Small delay to ensure all updates are complete
        setTimeout(() => {
            if (editingIndex.value === index) {
                const input = quantityInputs.value[index];
                if (input) {
                    input.focus();
                    input.select();
                }
            }
        }, 50);

        selectedRowIndex.value = null;
    }
};

const clearPacking = (index) => {
    rows.value[index].packing = ""; // Clear the  value
    rows.value[index].price = ""; // Clear the  value
    rows.value[index].quantity = "";
    rows.value[index].amount = "";
    butsPacking.value[index] = false;
};

const openPackingSelection = async (index) => {
    selectedRowIndex.value = index;

    if (!rows.value[index].packing?.trim()) {
        await fetchPacking(index);
    }

    showPackingModal.value = true;
};

const setQuantityInputRef = (el, index) => {
    if (el) {
        quantityInputs.value[index] = el;
    }
};

///////quantity time price equals amount
const updateAmount = (index) => {
    const row = rows.value[index];
    const quantity = parseFloat(row.quantity) || 0;
    const price = parseFloat(row.price) || 0;
    const tempAmount = quantity * price;
    row.amount = formatCurrency(tempAmount);
    computeTotalAmount();
};

//running total amount every rows
const computeTotalAmount = () => {
    const total = rows.value
        .reduce((sum, row) => {
            const cleanedAmount = String(row.amount || "")
                .replace(/[^\d.-]/g, "")
                .replace(/,/g, "");
            return sum + (parseFloat(cleanedAmount) || 0);
        }, 0)
        .toFixed(2);

    const rtotal = total === "0.00" ? "" : parseFloat(total);
    form.total_amount = rtotal === "" ? "" : formatCurrency(rtotal);
};

///////////////////////////////////Handle PAYMENT NO FROM CASH PAYMENT MODE MODAL
const handlePaymentNo = (paymentNo) => {
    form.payment_no = paymentNo.payment_no;
    showCashModal.value = false;

    form.invoices = rows.value.filter(
        (row) =>
            row.item_code.trim() !== "" &&
            row.item_name.trim() !== "" &&
            row.packing.trim() !== "" &&
            row.quantity &&
            row.price &&
            row.amount
    );

    form.post(route("addInvoice"), {
        onSuccess: () => {
            axios.get(route("invoice.latest.invoiceNumber")).then((res) => {
                form.invoice_no = res.data.invoice_number;
                showDialog.value = true;
            });
        },
        onError: (errors) => {
            handleFormErrors(errors);
        },
    });
};

const closeCashPaymentModal = () => {
    showCashModal.value = false;
    submitDisable.value = false;
};

////////PREVIEW PDF
// Function to prepare invoices before preview
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const apiRoute = ref(null);
const prepareInvoices = () => {
    form.invoices = rows.value.filter(
        (row) =>
            row.item_code.trim() !== "" &&
            row.item_name.trim() !== "" &&
            row.packing.trim() !== "" &&
            row.quantity &&
            row.price &&
            row.amount
    );
};
// Preview Invoice PDF
const previewInvoice = async () => {
    try {
        prepareInvoices(); // Make sure invoices are set correctly

        if (form.payment_mode === "Cash") {
            apiRoute.value = "preview-cash-invoice";
        } else {
            apiRoute.value = "preview-invoice";
        }

        pdfFormData.value = form;
        showPdfModal.value = true;
    } catch (error) {
        console.error("Error previewing invoice:", error);
    }
};

const pdfPrintSuccess = () => {
    showPdfModal.value = false;
    emit("closeSuccess");
};

const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        previewInvoice();
    } else {
        emit("closeSuccess");
    }
    submitDisable.value = false;
};

///////////////////////////WATCH/////////////////////////
watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            form.payment_mode = "";
            form.chargeinvoice_type = "";
            form.invoice_no = "********";
            try {
                const response = await axios.get(route("ci-types"));
                typeOptions.value = response.data;
                modalLoading.value = false;
            } catch (error) {
                console.error("Failed to fetch ci_types:", error);
            }
        }
    },
    { immediate: true }
);

watch(
    () => form.receipt_date,
    async (newReceiptDate, oldReceiptDate) => {
        if (newReceiptDate !== oldReceiptDate && newReceiptDate) {
            // When receipt date changes
            const selectedDate = new Date(newReceiptDate);
            const today = new Date();
            const diffDays = daysBetween(selectedDate, today);

            if (diffDays > 3) {
                openManagerModal(newReceiptDate);
            }
        }
    }
);

watch(
    () => form.chargeinvoice_type,
    async (newChargeInvoiceType, oldChargeInvoiceType) => {
        if (
            newChargeInvoiceType !== oldChargeInvoiceType &&
            newChargeInvoiceType
        ) {
            // When chargeinvoice_type changes
            deleteAllRows();
        }
    }
);

watch(
    () => form.customer_code,
    async (newVal, oldVal) => {
        if (newVal === "" || newVal !== oldVal) {
            form.payment_mode = "";
            form.chargeinvoice_type = "";
            form.particular = "";
            form.reference_no = "";
            form.total_amount = "";
        }
    }
);

//////////////////////////SUBMIT////////////////////
const submit = () => {
    const invalidRowsCount = rows.value.filter((row) => {
        return !row.saved && row.item_code?.trim();
    }).length;

    const invalidRowsCountTwo = rows.value.filter((row) => {
        return !row.saved && !row.item_code?.trim();
    }).length;

    if (invalidRowsCount > 0 || invalidRowsCountTwo > 1) {
        return showWarningToast(
            "Please complete or clear unsaved items before submitting"
        );
    }

    form.invoices = rows.value.filter(
        (row) =>
            row.item_code.trim() !== "" &&
            row.item_name.trim() !== "" &&
            row.packing.trim() !== "" &&
            row.quantity &&
            row.price &&
            row.amount
    );
    Object.keys(form.errors).forEach((key) => {
        form.errors[key] = "";
    });
    if (form.payment_mode === "Cash") {
        form.post(route("validateInvoiceCashPayment"), {
            onSuccess: () => {
                if (canPrint("0201-CIT")) {
                    showCashModal.value = true;
                    submitDisable.value = true;
                } else {
                    emit("closeSuccess");
                }
            },
            onError: (errors) => {
                handleFormErrors(errors);
            },
        });
    } else {
        form.post(route("addInvoice"), {
            onSuccess: () => {
                axios.get(route("invoice.latest.invoiceNumber")).then((res) => {
                    form.invoice_no = res.data.invoice_number;
                    if (canPrint("0201-CIT")) {
                        showDialog.value = true;
                    } else {
                        emit("closeSuccess");
                    }
                });
            },
            onError: (errors) => {
                handleFormErrors(errors);
            },
        });
    }
};

function handleFormErrors(errors) {
    if (Object.keys(errors).length === 1) {
        const firstError = Object.values(errors)[0];
        showWarningToast(firstError);
    } else if (Object.keys(errors).length !== 1) {
        if (Object.keys(errors).length === 2) {
            if (errors.total_amount === "Total Amount Required") {
                showWarningToast("Please Add Item");
            } else {
                showWarningToast("Please Fill Up Necessary Fields");
            }
        } else {
            showWarningToast("Please Fill Up Necessary Fields");
        }
    }
    // console.log(errors);
}

//#region ///////////////////////////////////ANIMATION////////////////////////////////////////
///////////////////////////////////////////////////FORM ANIMATION////////////////////////////
const formElement = ref(null);
const isExpanded = ref(true); // Control this with your v-if condition

// Handle dynamic content changes
watch(
    () => rows.value.length, // Watch whatever causes your form to expand
    async () => {
        if (!formElement.value || !isExpanded.value) return;

        // Start transition
        formElement.value.style.transition = "height 300ms ease-in-out";
        formElement.value.style.overflow = "hidden";

        // Set current height
        const startHeight = formElement.value.scrollHeight;
        formElement.value.style.height = `${startHeight}px`;

        await nextTick();

        // Get new height after content change
        const endHeight = formElement.value.scrollHeight;

        // Only animate if height actually changed
        if (startHeight !== endHeight) {
            formElement.value.style.height = `${endHeight}px`;

            // Clean up after animation completes
            const onTransitionEnd = () => {
                formElement.value.style.height = "";
                formElement.value.style.overflow = "";
                formElement.value.style.transition = "";
                formElement.value.removeEventListener(
                    "transitionend",
                    onTransitionEnd
                );
            };

            formElement.value.addEventListener(
                "transitionend",
                onTransitionEnd
            );
        } else {
            // No height change needed
            formElement.value.style.height = "";
            formElement.value.style.overflow = "";
            formElement.value.style.transition = "";
        }
    },
    { deep: true }
);

// Initial expand animation
const beforeEnter = (el) => {
    el.style.height = "0";
    el.style.overflow = "hidden";
};

const enter = (el) => {
    el.style.height = `${el.scrollHeight}px`;
};

const afterEnter = (el) => {
    el.style.height = "";
    el.style.overflow = "";
};

// Collapse animation
const beforeLeave = (el) => {
    el.style.height = `${el.scrollHeight}px`;
    el.style.overflow = "hidden";
};

const leave = (el) => {
    requestAnimationFrame(() => {
        el.style.height = "0";
    });
};
/////////////////////////TABLE ANIMATION/////////////////////////////////////
//#endregion
</script>

<style scoped>
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}

form {
    transition: box-shadow 300ms ease, border-radius 300ms ease;
}

/* Fallback for height transitions */
.v-enter-active,
.v-leave-active {
    transition: height 300ms ease-in-out;
}
</style>
