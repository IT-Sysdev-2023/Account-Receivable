<template>
    <div v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60 overflow-y-auto scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full">
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ManagersKey :show="showManagerModal" @success="onManagerSuccess" @cancel="onManagerCancel" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ConfirmationDialog :show="showDialogWHT" :message="confirmationMessageWHT" @close="handleConfirmWHT" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <DocumentNumberList v-if="showDocumentNumberListModal" :customer_code="form.customer_code"
                :date="form.receipt_date" :paymentType="form.payment_type" :editable_wht="editwhtconfirm"
                @close="closeDocumentNumberList" @submit="handleSelectedInvoices" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <AccountCodeList v-if="showAccCodeModal" :show="showAccCodeModal" @close="showAccCodeModal = false"
                @submit="handleSelectedAccCode" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <CustomerCodeList v-if="showCusCodeModal" :show="showCusCodeModal" @close="showCusCodeModal = false"
                @submit="handleSelectedCusCode" />
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
            <SelectionTableTwo v-if="showCashInBankModal" :title="'SELECT CASH IN BANK'" :data="cashinbankResults"
                firstHeader="BANK CODE" secondHeader="BANK NAME" thirdHeader="ACCOUNT CODE"
                @close="showCashInBankModal = false" @submit="onSelectCashInBank" />
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
            <ConfirmationDialog :show="showODDialog || showCheckDialog"
                message="Transfer AR To Other Customer or Account?" @close="handleODConfirm" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <InformationDialog :show="showInfoDialog" :message="infoMessage" @close="showInfoDialog = false" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <PdfPreviewModal v-if="showPdfModal" :show="showPdfModal" apiEndpoint="preview-payment"
                :formData="pdfFormData" @closeSuccess="pdfPrintSuccess" @close="pdfPrintSuccess" />
        </Transition>
        <transition appear @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter"
            @before-leave="beforeLeave" @leave="leave">
            <!-- Modal Container -->
            <div
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-7xl rounded-2xl border border-[var(--color-border)]">
                <form v-if="isExpanded" ref="formElement" @submit.prevent="submit">
                    <!-- Show spinner while loading -->
                    <div v-if="modalLoading" class="flex justify-center items-center py-20">
                        <svg width="60" height="60" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
                            fill="var(--color-icon)">
                            <rect class="spinner_jCIR" x="1" y="6" width="2.8" height="12" />
                            <rect class="spinner_jCIR spinner_upm8" x="5.8" y="6" width="2.8" height="12" />
                            <rect class="spinner_jCIR spinner_2eL5" x="10.6" y="6" width="2.8" height="12" />
                            <rect class="spinner_jCIR spinner_Rp9l" x="15.4" y="6" width="2.8" height="12" />
                            <rect class="spinner_jCIR spinner_dy3W" x="20.2" y="6" width="2.8" height="12" />
                        </svg>
                    </div>
                    <div v-else class="p-6">
                        <!-- Header -->
                        <div class="px-8 pb-4">
                            <h2 class="text-2xl font-bold text-center">
                                ADD NEW PAYMENT
                            </h2>
                            <div
                                class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent">
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-6 px-4">
                            <!-- Right: Form Fields -->
                            <div class="w-full grid grid-cols-4 grid-rows-auto">
                                <div class="col-span-4 row-span-2">
                                    <div class="w-full grid sm:grid-cols-1 md:grid-cols-4 gap-x-[16px]">
                                        <div class="col-span-4">
                                            <div class="w-full grid grid-cols-3 gap-x-[16px]">
                                                <TextInput label="Payment Number" v-model="form.payment_no" type="text"
                                                    :readonly="true" :message="form.errors.payment_no
                                                        " />
                                                <div class="mb-2">
                                                    <label
                                                        class="block text-sm font-medium text-[var(--color-text-secondary)] mb-2">Receipt
                                                        Date</label>
                                                    <DatePicker v-model="form.receipt_date
                                                        " placeholder="Select Date" format="MM/DD/YYYY" :message="form.errors
                                                            .receipt_date
                                                            " />
                                                </div>
                                                <TextInput label="Transaction Date" v-model="form.transaction_date
                                                    " type="date" :readonly="true" :message="form.errors
                                                        .transaction_date
                                                        " />
                                            </div>
                                        </div>
                                        <TextInput label="Customer Code" type="text" v-model="form.customer_code"
                                            @click="onCustomerClick()" :readonly="!form.receipt_date"
                                            :message="form.errors.customer_code"
                                            :default-placeholder="'Click to Select'"
                                            :modifiedPlaceholder="'Select Date First'" selectable="yes" />
                                        <TextInput label="Customer Name" v-model="form.name" type="text"
                                            :readonly="true" :message="form.errors.name" class="col-span-2" />
                                        <DropdownInput label="Payment Type" v-model="form.payment_type" :options="journal_voucher
                                            ? [
                                                '5A - Cash',
                                                '5B - Journal Voucher',
                                                '5C - Online Deposit',
                                                '5D - Check',
                                                '5E - Creditable(WHT)',
                                            ]
                                            : [
                                                '5A - Cash',
                                                '5C - Online Deposit',
                                                '5D - Check',
                                                '5E - Creditable(WHT)',
                                            ]
                                            " :message="form.errors.payment_type" :disabled="!form.name"
                                            placeholder="Click to Select" disabledPlaceholder="Select Customer First" />
                                    </div>
                                </div>
                                <transition @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter"
                                    @before-leave="beforeLeave" @leave="leave">
                                    <div v-if="form.payment_type" class="col-span-4">
                                        <div class="col-span-4 row-span-1 row-start-3">
                                            <div class="w-full grid grid-cols-4 gap-x-[16px]">
                                                <TextInput label="Document Number" @click="
                                                    editable_wht &&
                                                        form.payment_type ===
                                                        '5E - Creditable(WHT)'
                                                        ? openWhtConfirmation()
                                                        : onDocuNumberClick()
                                                    " v-model="form.document_no" type="textarea" :message="form.errors.document_no
                                                        " :readonly="!form.payment_type
                                                            " :default-placeholder="'Click to Select'"
                                                    :modified-placeholder="'Select Type First'" selectable="yes"
                                                    rows="5" />

                                                <div :class="[
                                                    !manualSelectIdentifier &&
                                                        form.document_no
                                                        ? 'w-full col-span-3 grid grid-cols-3 gap-x-[16px]'
                                                        : 'w-full col-span-3 grid grid-cols-2 gap-x-[16px]',
                                                ]">
                                                    <TextInput label="Type" v-model="form.type" type="text" :message="form.errors.type
                                                        " readonly />
                                                    <PrefixTextInput v-if="
                                                        form.payment_type ===
                                                        '5B - Journal Voucher' ||
                                                        form.payment_type ===
                                                        '5D - Check' ||
                                                        form.payment_type ===
                                                        '5E - Creditable(WHT)'
                                                    " label="Reference Number" v-model="refNumberWithPrefix
                                                        " type="text" :message="form.errors
                                                                .reference_no
                                                                " :prefixType="form.payment_type ===
                                                                '5B - Journal Voucher'
                                                                ? 'JV'
                                                                : form.payment_type ===
                                                                    '5D - Check'
                                                                    ? 'CHK'
                                                                    : form.payment_type ===
                                                                        '5E - Creditable(WHT)'
                                                                        ? 'WHT'
                                                                        : ''
                                                                " @keydown="
                                                                handleKeyDownREF
                                                            " @click="handleClickREF" />
                                                    <PrefixTextInput v-if="
                                                        form.payment_type ===
                                                        '5A - Cash' ||
                                                        form.payment_type ===
                                                        '5C - Online Deposit'
                                                    " label="DS Number" v-model="dsNumberWithPrefix
                                                        " type="text" :message="form.errors.ds_no
                                                                " prefixType="DS" @keydown="
                                                                handleKeyDownDS
                                                            " @click="handleClickDS" />
                                                    <TextInput v-if="
                                                        !manualSelectIdentifier &&
                                                        form.document_no
                                                    " label="Advance Payment Balance" v-model="form.advanced_payment_balance
                                                        " type="text" :message="form.errors
                                                                .advanced_payment_balance
                                                                " readonly />
                                                    <div :class="[
                                                        !manualSelectIdentifier &&
                                                            form.document_no
                                                            ? 'w-full col-span-3 grid grid-cols-2 gap-x-[16px]'
                                                            : 'w-full col-span-2 grid grid-cols-2 gap-x-[16px]',
                                                    ]">
                                                        <TextInput label="Total Amount" v-model="form.total_amount
                                                            " type="text" :message="form.errors
                                                                .total_amount
                                                                " readonly />
                                                        <TextInput v-if="
                                                            manualSelectIdentifier
                                                        " label="Amount Paid" v-model="form.amount_paid
                                                            " type="text" :readonly="(form.payment_type ===
                                                                    '5E - Creditable(WHT)' &&
                                                                    !editable_wht) ||
                                                                    (form.payment_type !==
                                                                        '5E - Creditable(WHT)' &&
                                                                        (!form.total_amount ||
                                                                            manualSelectIdentifier))
                                                                    " :message="form.errors
                                                                    .amount_paid
                                                                    " :modified-placeholder="form.payment_type ===
                                                                    '5E - Creditable(WHT)'
                                                                    ? ''
                                                                    : 'Total Amount Required'
                                                                    " />
                                                        <TextInput v-else label="Amount Paid" v-model="form.amount_paid
                                                            " type="decimal" :readonly="(form.payment_type ===
                                                                '5E - Creditable(WHT)' &&
                                                                !editable_wht.value) ||
                                                                (form.payment_type !==
                                                                    '5E - Creditable(WHT)' &&
                                                                    (!form.total_amount ||
                                                                        manualSelectIdentifier))
                                                                " :message="form.errors
                                                                    .amount_paid
                                                                    " :modified-placeholder="'Total Amnt Required'" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-span-4 row-span-1 row-start-4">
                                            <div class="w-full grid grid-cols-4 gap-x-[16px]">
                                                <TextInput v-if="
                                                    form.payment_type ===
                                                    '5A - Cash' ||
                                                    (form.payment_type ===
                                                        '5C - Online Deposit' &&
                                                        !od_confirmation) ||
                                                    (form.payment_type ===
                                                        '5D - Check' &&
                                                        !check_confirmation)
                                                " label="Cash In Bank" type="text" v-model="form.cash_in_bank" @click="
                                                    openCashInBankSelection()
                                                    " :message="form.errors.cash_in_bank
                                                            " :default-placeholder="'Click to Select'" selectable="yes" />
                                                <TextInput v-if="
                                                    form.payment_type ===
                                                    '5B - Journal Voucher' ||
                                                    (form.payment_type ===
                                                        '5C - Online Deposit' &&
                                                        od_confirmation)
                                                " label="Account Code" v-model="form.acc_code"
                                                    @click="onAccCodeClick()" type="text" :message="form.errors.acc_code
                                                        " :default-placeholder="'Click to Select'" selectable="yes"
                                                    :readonly="form.cust_code !== ''
                                                        " />
                                                <TextInput v-if="
                                                    form.payment_type ===
                                                    '5B - Journal Voucher' ||
                                                    (form.payment_type ===
                                                        '5C - Online Deposit' &&
                                                        od_confirmation) ||
                                                    (form.payment_type ===
                                                        '5D - Check' &&
                                                        check_confirmation)
                                                " label="Customer Code" v-model="form.cust_code"
                                                    @click="onCusCodeClick()" type="text" :message="form.errors.cust_code
                                                        " :default-placeholder="'Click to Select'" selectable="yes"
                                                    :readonly="form.acc_code !== ''
                                                        " />

                                                <DropdownInput v-if="
                                                    form.payment_type ===
                                                    '5D - Check'
                                                " label="Check Type" v-model="form.check_type" :options="[
                                                    'Dated Check',
                                                    'Post Dated Check',
                                                ]" :message="form.errors.check_type
                                                        " placeholder="Click to Select" />

                                                <DropdownInput v-if="
                                                    form.payment_type ===
                                                    '5D - Check'
                                                " label="Aging Basis" v-model="form.aging_basis" :options="[
                                                    'Receipt Date',
                                                    'SI Date',
                                                ]" :message="form.errors.aging_basis
                                                        " placeholder="Click to Select" />

                                                <TextInput v-if="
                                                    form.payment_type ===
                                                    '5D - Check'
                                                " label="Aging Days" v-model="form.aging_days" type="number" :message="form.errors.aging_days
                                                    " :readonly="!form.aging_basis ||
                                                            (form.aging_basis ===
                                                                'Receipt Date' &&
                                                                !form.receipt_date) ||
                                                            (form.aging_basis ===
                                                                'SI Date' &&
                                                                !form.document_date)
                                                            " :modified-placeholder="form.aging_basis ===
                                                            'Receipt Date'
                                                            ? 'Receipt Date Required'
                                                            : form.aging_basis ===
                                                                'SI Date'
                                                                ? 'Document Number Required'
                                                                : 'Aging Basis Required'
                                                            " />

                                                <div v-if="
                                                    form.payment_type ===
                                                    '5E - Creditable(WHT)'
                                                " class="flex items-center gap-2 mt-8 mb-[26px]">
                                                    <div class="relative inline-block w-6 h-6">
                                                        <input type="checkbox" v-model="form.withBIR
                                                            "
                                                            class="peer w-6 h-6 appearance-none border-2 border-[var(--color-border)] rounded-md checked:bg-[var(--color-bg-avatar)] checked:border-transparent cursor-pointer" />
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                            class="absolute p-0.5 top-0 left-0 w-6 h-6 text-white hidden peer-checked:block pointer-events-none"
                                                            fill="white">
                                                            <path
                                                                d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z" />
                                                        </svg>
                                                    </div>
                                                    <label class="block font-semibold mb-1">
                                                        With BIR 2307 Provided
                                                    </label>
                                                </div>

                                                <DropdownInput v-if="
                                                    form.payment_type ===
                                                    '5E - Creditable(WHT)'
                                                " label="With Holding Tax" v-model="form.witholdingtax" :options="editwhtconfirm
                                                    ? [
                                                        '1%',
                                                        '2%',
                                                        '5%',
                                                        'Custom Amount',
                                                    ]
                                                    : ['1%', '2%', '5%']
                                                    " :message="form.errors
                                                            .witholdingtax
                                                            " :disabled="!form.document_no ||
                                                            editwhtconfirm
                                                            " placeholder="Click to Select"
                                                    disabledPlaceholder="Select Doc No First" />
                                            </div>
                                            <transition @before-enter="beforeEnter" @enter="enter"
                                                @after-enter="afterEnter" @before-leave="beforeLeave" @leave="leave">
                                                <div v-if="
                                                    form.payment_type ===
                                                    '5D - Check' &&
                                                    form.aging_days
                                                " class="w-full grid grid-cols-3 gap-x-[16px]">
                                                    <TextInput label="Account Name & Address" v-model="form.acc_name_address
                                                        " type="textarea" :message="form.errors
                                                            .acc_name_address
                                                            " class="col-span-3" />
                                                    <TextInput label="Referral Name" v-model="form.referral_name
                                                        " type="text" :message="form.errors
                                                            .referral_name
                                                            " />
                                                    <TextInput label="Account Number" v-model="form.acc_number
                                                        " type="text" :message="form.errors
                                                            .acc_number
                                                            " />
                                                    <TextInput label="Due Date" v-model="form.due_date" type="date"
                                                        :readonly="true" :message="form.errors.due_date
                                                            " />
                                                </div>
                                            </transition>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-4 pt-2 border-t gap-2 border-[var(--color-border)] flex justify-end">
                            <button type="button" @click="closeModal" class="closeButton group">
                                <div class="flex justify-center items-center gap-2">
                                    <span class="transition-transform duration-300 group-hover:rotate-180">
                                        <svg-icon type="mdi" :path="mdiClose" class="w-5 h-5" />
                                    </span>
                                    Close
                                </div>
                            </button>
                            <button type="submit" class="submitButton group" :disabled="form.processing">
                                <div class="flex justify-center items-center gap-2">
                                    <span class="transition-transform duration-300 group-hover:rotate-405">
                                        <svg-icon type="mdi" :path="mdiNavigationVariantOutline" class="w-5 h-5" />
                                    </span>
                                    <span v-if="form.processing">Submitting...</span>
                                    <span v-else>Submit</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ManagersKey from "../ManagersKey.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import axios from "axios";
import DocumentNumberList from "./DocumentNumberList.vue";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import AccountCodeList from "./AccountCodeList.vue";
import CustomerCodeList from "./CustomerCodeList.vue";
import InformationDialog from "../../Pages/Components/InformationDialog.vue";
import CustomerListModal from "./CustomerListModal.vue";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";
import SelectionTableTwo from "../../Pages/Components/SelectionTableTwo.vue";
import PrefixTextInput from "../../Pages/Components/PrefixTextInput.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import PdfPreviewModal from "../PdfPreviewModal.vue";
import DatePicker from "../../Pages/Components/DatePicker.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    payment_no: null,
    receipt_date: null,
    transaction_date: null,
    customer_code: null,
    name: null,
    payment_type: null,
    type: null,
    reference_no: null,
    ds_no: null,
    document_no: null,
    document_date: null,
    advanced_payment_balance: null,
    total_amount: null,
    amount_paid: null,
    acc_code: null,
    cust_code: null,
    cash_in_bank: null,
    withBIR: null,
    witholdingtax: null,
    check_type: null,
    aging_basis: null,
    aging_days: null,
    acc_name_address: null,
    referral_name: null,
    acc_number: null,
    due_date: null,
    selectedDocuments: [],
});

const { canPrint } = usePermissions();

const showManagerModal = ref(false);
const showAccCodeModal = ref(false);
const showCusCodeModal = ref(false);
const pendingOldDate = ref(null);
const modalLoading = ref(false);
const showDocumentNumberListModal = ref(false);
const showCustomerModal = ref(false);
const showCashInBankModal = ref(false);

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

function onDocuNumberClick() {
    showDocumentNumberListModal.value = true;
}

function onAccCodeClick() {
    showAccCodeModal.value = true;
}
function onCusCodeClick() {
    showCusCodeModal.value = true;
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const emit = defineEmits(["close", "closeSuccess", "update:form"]);

const closeModal = () => {
    emit("close");
};

////////// SHOW TOAST ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

const showToast = ref(false);
const toastMessage = ref("");
let toastTimeout = null;

const showWarningToast = (message) => {
    toastMessage.value = message;
    showToast.value = false;
    if (toastTimeout) clearTimeout(toastTimeout);

    setTimeout(() => {
        showToast.value = true;
    }, 0);

    toastTimeout = setTimeout(() => {
        showToast.value = false;
        toastTimeout = null;
    }, 3000);
};

/////// CUSTOMER CODE DROPDOWN ******************************************************************************************************
function onCustomerClick() {
    showCustomerModal.value = true;
}
const editable_wht = ref(null);
const journal_voucher = ref(null);
const handleSelectedCustomer = (selectedData) => {
    form.customer_code = selectedData.cus_code;
    form.name = selectedData.cus_name;
    form.advanced_payment_balance = formatCurrency(selectedData.adv_py_bal);
    editable_wht.value = selectedData.editable_wht === 1 ? true : false;
    journal_voucher.value = selectedData.journal_voucher === 1 ? true : false;

    showCustomerModal.value = false;
};

/////// CASH IN BANK DROPDOWN ******************************************************************************************************
const cashinbankResults = ref([]);

const fetchCashinBank = async () => {
    try {
        const response = await axios.get(route("getCashInBankList"));
        cashinbankResults.value = response.data.map((cib) => ({
            firstData: cib.bank_code,
            secondData: cib.bank_name,
            thirdData: cib.acc_code,
        }));
    } catch (error) {
        console.error("Error fetching customer code data:", error);
        cashinbankResults.value = [];
    }
};

const onSelectCashInBank = (selected) => {
    form.cash_in_bank = selected.secondData;
    showCashInBankModal.value = false;
};

const openCashInBankSelection = async () => {
    await fetchCashinBank();
    showCashInBankModal.value = true;
};

/////////////////////////DS NO PREFIX ///////////////////////////////////////////////////////////
const dsNumberWithPrefix = computed({
    get() {
        return form.ds_no?.startsWith("DS#")
            ? form.ds_no
            : `DS#${form.ds_no || ""}`;
    },
    set(value) {
        form.ds_no = value.startsWith("DS#") ? value : `DS#${value}`;
    },
});

const handleKeyDownDS = (e) => {
    if (e.ctrlKey || e.altKey || e.metaKey) {
        return;
    }

    const input = e.target;
    const currentPrefix = "DS#";
    const prefixLength = currentPrefix.length;
    const isAllSelected =
        input.selectionStart === 0 && input.selectionEnd === input.value.length;

    if (
        (e.key === "Backspace" || e.key === "Delete") &&
        input.selectionStart <= prefixLength &&
        input.selectionEnd <= prefixLength
    ) {
        e.preventDefault();
        return;
    }

    const allowedKeys = [
        "Backspace",
        "Delete",
        "ArrowLeft",
        "ArrowRight",
        "Tab",
        "Home",
        "End",
    ];

    if (allowedKeys.includes(e.key)) {
        return;
    }

    if (isAllSelected) {
        e.preventDefault();
        const newValue =
            currentPrefix +
            (e.key.match(/[0-9]/) ? e.key : form.ds_no.slice(3));
        input.value = newValue;
        form.ds_no = newValue;
        input.setSelectionRange(prefixLength + 1, prefixLength + 1);
        return;
    }

    if (input.selectionStart < prefixLength) {
        e.preventDefault();
        const currentValue = input.value;
        const newValue =
            currentValue.slice(0, prefixLength) +
            (e.key.match(/[0-9]/) ? e.key : "") +
            currentValue.slice(prefixLength);
        input.value = newValue;
        form.ds_no = newValue;
        const newCursorPos = prefixLength + 1;
        input.setSelectionRange(newCursorPos, newCursorPos);
        return;
    }

    if (!/[0-9]/.test(e.key)) {
        e.preventDefault();
    }
};

const handleClickDS = (e) => {
    const input = e.target;
    const prefix = "DS#";
    const prefixLength = prefix.length;

    if (input.selectionStart < prefixLength) {
        setTimeout(() => {
            if (input.selectionStart === input.selectionEnd) {
                input.setSelectionRange(prefixLength, prefixLength);
            }
        }, 0);
    }
};

/////////////////////////REF NO PREFIX ///////////////////////////////////////////////////////////
const refNumberWithPrefix = computed({
    get() {
        if (form.payment_type === "5B - Journal Voucher") {
            return form.reference_no?.startsWith("JV#")
                ? form.reference_no
                : `JV#${form.reference_no || ""}`;
        } else if (form.payment_type === "5D - Check") {
            return form.reference_no?.startsWith("CHK#")
                ? form.reference_no
                : `CHK#${form.reference_no || ""}`;
        } else if (form.payment_type === "5E - Creditable(WHT)") {
            return form.reference_no?.startsWith("WHT#")
                ? form.reference_no
                : `WHT#${form.reference_no || ""}`;
        }
    },
    set(value) {
        if (form.payment_type === "5B - Journal Voucher") {
            form.reference_no = value.startsWith("JV#") ? value : `JV#${value}`;
        } else if (form.payment_type === "5D - Check") {
            form.reference_no = value.startsWith("CHK#")
                ? value
                : `CHK#${value}`;
        } else if (form.payment_type === "5E - Creditable(WHT)") {
            form.reference_no = value.startsWith("WHT#")
                ? value
                : `WHT#${value}`;
        }
    },
});

const handleKeyDownREF = (e) => {
    if (e.ctrlKey || e.altKey || e.metaKey) {
        return;
    }

    const input = e.target;
    const currentPrefix =
        form.payment_type === "5B - Journal Voucher"
            ? "JV#"
            : form.payment_type === "5D - Check"
                ? "CHK#"
                : form.payment_type === "5E - Creditable(WHT)"
                    ? "WHT#"
                    : "";
    const prefixLength = currentPrefix.length;
    const isAllSelected =
        input.selectionStart === 0 && input.selectionEnd === input.value.length;

    if (
        (e.key === "Backspace" || e.key === "Delete") &&
        input.selectionStart <= prefixLength &&
        input.selectionEnd <= prefixLength
    ) {
        e.preventDefault();
        return;
    }

    const allowedKeys = [
        "Backspace",
        "Delete",
        "ArrowLeft",
        "ArrowRight",
        "Tab",
        "Home",
        "End",
    ];

    if (allowedKeys.includes(e.key)) {
        return;
    }

    if (isAllSelected) {
        e.preventDefault();
        const newValue =
            currentPrefix +
            (e.key.match(/[0-9]/)
                ? e.key
                : form.payment_type === "5B - Journal Voucher"
                    ? form.reference_no.slice(3)
                    : form.payment_type === "5D - Check"
                        ? form.reference_no.slice(4)
                        : form.payment_type === "5E - Creditable(WHT)"
                            ? form.reference_no.slice(4)
                            : "");
        input.value = newValue;
        form.reference_no = newValue;
        input.setSelectionRange(prefixLength + 1, prefixLength + 1);
        return;
    }

    if (input.selectionStart < prefixLength) {
        e.preventDefault();
        const currentValue = input.value;
        const newValue =
            currentValue.slice(0, prefixLength) +
            (e.key.match(/[0-9]/) ? e.key : "") +
            currentValue.slice(prefixLength);
        input.value = newValue;
        form.reference_no = newValue;
        const newCursorPos = prefixLength + 1;
        input.setSelectionRange(newCursorPos, newCursorPos);
        return;
    }

    if (!/[0-9]/.test(e.key)) {
        e.preventDefault();
    }
};

const handleClickREF = (e) => {
    const input = e.target;
    const prefix =
        form.payment_type === "5B - Journal Voucher"
            ? "JV#"
            : form.payment_type === "5D - Check"
                ? "CHK#"
                : form.payment_type === "5E - Creditable(WHT)"
                    ? "WHT#"
                    : "";
    const prefixLength = prefix.length;

    if (input.selectionStart < prefixLength) {
        setTimeout(() => {
            if (input.selectionStart === input.selectionEnd) {
                input.setSelectionRange(prefixLength, prefixLength);
            }
        }, 0);
    }
};

/////////////////////////DOCUMENT NO FETCH DATA FROM TABLE//////////////////////////////////////
const ledgerType = ref(null);
const floatingAmount = ref(0);
const manualSelectIdentifier = ref(false);
const handleSelectedInvoices = (selectedData) => {
    showDocumentNumberListModal.value = false;
    if (!selectedData.totalAmountPaid) {
        manualSelectIdentifier.value = false;
        form.document_no = selectedData.invoiceNumber;
        form.total_amount = formatCurrency(selectedData.totalAmount);
        form.amount_paid = "";
        form.document_date = selectedData.date;
        floatingAmount.value = selectedData.floatingAmount;
        form.type = selectedData.type;
        form.selectedDocuments = [];
    } else {
        form.document_no = selectedData.invoiceNumber;
        form.total_amount = formatCurrency(selectedData.totalAmount);
        if (
            form.payment_type === "5E - Creditable(WHT)" &&
            !selectedData.editable_wht_mode
        ) {
            form.amount_paid = "";
            form.witholdingtax = "";
        } else if (
            form.payment_type === "5E - Creditable(WHT)" &&
            selectedData.editable_wht_mode
        ) {
            form.amount_paid = formatCurrency(selectedData.totalAmountPaid);
            form.witholdingtax = "Custom Amount";
        } else {
            form.amount_paid = formatCurrency(selectedData.totalAmountPaid);
        }
        form.document_date = selectedData.date;
        floatingAmount.value = selectedData.floatingAmount;
        form.type = selectedData.type;
        form.selectedDocuments = selectedData.selectedDocuments;
        form.advanced_payment_balance = "";
        manualSelectIdentifier.value = true;
    }

    // ledgerType.value = form.type;
};

const closeDocumentNumberList = () => {
    showDocumentNumberListModal.value = false;
    editwhtconfirm.value = false;
};

/////////////////////////ACCOUNT CODE FETCH DATA FROM TABLE//////////////////////////////////////
const handleSelectedAccCode = (selectedData) => {
    form.acc_code = selectedData.listaccCode;
    showAccCodeModal.value = false;
};

/////////////////////////CUSTOMER CODE FETCH DATA FROM TABLE//////////////////////////////////////
const handleSelectedCusCode = (selectedData) => {
    form.cust_code = selectedData.listaccCode;

    showCusCodeModal.value = false;
};

//#region /////// PREVIEW PDF/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Preview PDF
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const previewInvoice = async () => {
    try {
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

////////SHOW DIALOG FOR PRINT///////////////////////////
const showDialog = ref(false);
const handleConfirm = async (confirmed) => {
    showDialog.value = false;
    if (confirmed) {
        previewInvoice();
    } else {
        emit("closeSuccess");
    }
};

////////SHOW INFO DIALOG///////////////////////////
const showInfoDialog = ref(false);
const infoMessage = ref(null);

////////SHOW DIALOG FOR WHT EDIT CONFIRMATION///////////////////////////
const showDialogWHT = ref(false);
const confirmationMessageWHT = ref(null);
const editwhtconfirm = ref(false);
const openWhtConfirmation = () => {
    confirmationMessageWHT.value =
        "This customer appears to have the ability to modify the withholding tax (WHT) amount. Proceed to editable mode?";
    showDialogWHT.value = true;
};
const handleConfirmWHT = async (confirmed) => {
    showDialogWHT.value = false;
    if (confirmed) {
        editwhtconfirm.value = true;
        onDocuNumberClick();
    } else {
        editwhtconfirm.value = false;
        onDocuNumberClick();
    }
};
//#endregion

///////////////////////////////WATCH///////////////////////////////////////////////////////
//////////////////////////Online Deposit Confirm/////////////////////////////////////////////////
const showODDialog = ref(false);
const od_confirmation = ref(false);

const showCheckDialog = ref(false);
const check_confirmation = ref(false);

watch(
    () => form.payment_type,
    (newValue) => {
        if (newValue === "5C - Online Deposit") {
            showODDialog.value = true;
        } else if (newValue === "5D - Check") {
            showCheckDialog.value = true;
        } else {
            od_confirmation.value = false;
            check_confirmation.value = false;
        }
    }
);

const handleODConfirm = async (confirmed) => {
    if (showODDialog.value) {
        showODDialog.value = false;
        if (confirmed) {
            od_confirmation.value = true;
        } else {
            od_confirmation.value = false;
        }
    } else if (showCheckDialog.value) {
        showCheckDialog.value = false;
        if (confirmed) {
            check_confirmation.value = true;
        } else {
            check_confirmation.value = false;
        }
    }
};

///////////////////////////COMPUTE TAX PERCENTAGE//////////////////////////////////////////////////////////////////
watch(
    () => form.witholdingtax,
    (newTax) => {
        if (newTax !== "Custom Amount") {
            if (
                form.payment_type !== "5E - Creditable(WHT)" ||
                !newTax ||
                !form.total_amount
            )
                return;

            // Extract the numeric value (e.g., "1%" → 1)
            const taxPercentage = parseFloat(newTax.replace("%", ""));

            const amountInCents = Math.round(
                Number(
                    String(form.total_amount)
                        .replace(/[^\d.-]/g, "")
                        .replace(/,/g, "")
                ) * 100
            );

            // Calculate with proper decimal handling
            form.amount_paid = (
                (amountInCents * (taxPercentage / 100)) /
                100
            ).toFixed(2);
        }
    },
    { immediate: true } // Run on initial selection
);

//////////////////////////COMPUTE DUE DATE BASE ON AGING BASIS AND AGING DAYS//////////////////////////////////////
watch(
    () => form.aging_days,
    (newAgingDays) => {
        if (form.aging_basis) {
            calculateDueDate();
        }
    }
);

function calculateDueDate() {
    // Clear if no aging days
    if (!form.aging_days) {
        form.due_date = "";
        return;
    }

    // Determine base date based on aging basis
    const baseDateStr =
        form.aging_basis === "Receipt Date"
            ? form.receipt_date
            : form.document_date;

    if (!baseDateStr) {
        form.due_date = "";
        return;
    }

    const baseDate = new Date(baseDateStr);
    if (isNaN(baseDate.getTime())) {
        form.due_date = "";
        return;
    }

    // Calculate due date
    const dueDate = new Date(baseDate);
    dueDate.setDate(dueDate.getDate() + parseInt(form.aging_days));

    // Format as YYYY-MM-DD
    const year = dueDate.getFullYear();
    const month = String(dueDate.getMonth() + 1).padStart(2, "0");
    const day = String(dueDate.getDate()).padStart(2, "0");

    form.due_date = `${year}-${month}-${day}`;
}
///////////////////////////////////////////////////////////////////////////////////////////////

watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            form.payment_no = "********";
            form.payment_type = "";
            form.type = "";
            form.check_type = "";
            form.aging_basis = "";
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

// watch(
//     () => form.receipt_date,
//     (newDate, oldDate) => {
//         if (newDate && newDate !== oldDate) {
//             const diffDays = daysBetween(new Date(newDate), new Date());
//             if (diffDays > 3) openManagerModal(newDate);
//         }
//     }
// );

watch(
    () => form.receipt_date,
    (newDate, oldDate) => {
        if (newDate && newDate !== oldDate) {
            form.aging_days = "";
        }
    }
);

watch(
    () => form.customer_code,
    async (newVal, oldVal) => {
        if (newVal === "" || newVal !== oldVal) {
            form.payment_type = "";
            form.type = "";
            form.reference_no = "";
            form.ds_no = "";
            form.document_no = "";
            form.document_date = "";
            form.total_amount = "";
            form.amount_paid = "";
            form.acc_code = "";
            form.cust_code = "";
            form.cash_in_bank = "";
            form.withBIR = "";
            form.witholdingtax = "";
            form.check_type = "";
            form.aging_basis = "";
            form.aging_days = "";
            form.acc_name_address = "";
            form.referral_name = "";
            form.acc_number = "";
            form.due_date = "";
        }
    }
);

watch(
    () => form.payment_type,
    async (newVal, oldVal) => {
        if (newVal && newVal !== oldVal) {
            form.type = "";
            form.reference_no = "";
            form.ds_no = "";
            form.document_no = "";
            form.document_date = "";
            form.total_amount = "";
            form.amount_paid = "";
            form.acc_code = "";
            form.cust_code = "";
            form.cash_in_bank = "";
            if (form.payment_type === "5E - Creditable(WHT)") {
                form.withBIR = false;
            } else {
                form.withBIR = "";
            }

            form.witholdingtax = "";
            form.check_type = "";
            form.aging_basis = "";
            form.aging_days = "";
            form.acc_name_address = "";
            form.referral_name = "";
            form.acc_number = "";
            form.due_date = "";
        }
    }
);

watch(
    () => form.document_no,
    async (newVal, oldVal) => {
        if (newVal && newVal !== oldVal) {
            form.reference_no = "";
            form.ds_no = "";
            form.acc_code = "";
            form.cust_code = "";
            form.cash_in_bank = "";
            if (form.payment_type === "5E - Creditable(WHT)") {
                form.withBIR = false;
            } else {
                form.withBIR = "";
            }
            // form.witholdingtax = "";
            form.check_type = "";
            form.aging_basis = "";
            form.aging_days = "";
            form.acc_name_address = "";
            form.referral_name = "";
            form.acc_number = "";
            form.due_date = "";
        }
    }
);

watch(
    () => form.aging_basis,
    async (newVal, oldVal) => {
        if (newVal && newVal !== oldVal) {
            form.aging_days = "";
        }
    }
);

//////////////////////////SUBMIT////////////////////
const submit = () => {
    if (manualSelectIdentifier.value && typeof form.amount_paid === "string") {
        const cleaned = form.amount_paid.replace(/[^\d.]/g, "");
        form.amount_paid = cleaned ? parseFloat(cleaned) : 0;
    }

    const submissionData = {
        ...form.data(),
        _od_confirmation: od_confirmation.value,
        _check_confirmation: check_confirmation.value,
        _cl_type: ledgerType.value,
    };

    if (!form.document_no === "Oldest to Newest Applied") {
        if (floatingAmount.value) {
            const cleanTotalAmount = parseFloat(
                form.total_amount.replace(/[^\d.]/g, "")
            );
            const cleanFloatingAmount = parseFloat(floatingAmount.value);

            const remainingFloatingBalance =
                cleanTotalAmount - cleanFloatingAmount;

            if (form.amount_paid > remainingFloatingBalance) {
                infoMessage.value = `Amount should not exceed the available balance of ${formatCurrency(
                    remainingFloatingBalance
                )}`;
                return (showInfoDialog.value = true);
            }
        }
    }

    Object.keys(form.errors).forEach((key) => {
        form.errors[key] = "";
    });

    form.transform((data) => submissionData).post(route("addPayment"), {
        onSuccess: () => {
            axios.get(route("payment.latest.paymentNumber")).then((res) => {
                form.payment_no = res.data.payment_number;
                if (canPrint("0203-PAYT")) {
                    showDialog.value = true;
                } else {
                    emit("closeSuccess");
                }
            });
        },
        onError: (errors) => {
            handleSubmissionErrors(errors);
        },
    });
};

const handleSubmissionErrors = (errors) => {
    const errorCount = Object.keys(errors).length;

    if (errorCount === 0) return;

    if (errorCount === 1) {
        showWarningToast(Object.values(errors)[0]);
        return;
    }

    if (
        errors.amount_paid?.includes(
            "Amount Should Not Be Greater Than Available Balance"
        )
    ) {
        showWarningToast("Amount exceeds available balance");
    } else {
        showWarningToast("Please fill out all required fields");
    }

    // console.log("Form submission errors:", errors);
};

//#region  ///////////////////////////////////ANIMATION////////////////////////////////////////
///////////////////////////////////////////////////FORM ANIMATION////////////////////////////
const formElement = ref(null);
const isExpanded = ref(true); // Control this with your v-if condition

// Handle dynamic content changes
watch(
    () => form.payment_type, // Watch whatever causes your form to expand
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

//#endregion
</script>

<style scoped>
form {
    transition: box-shadow 300ms ease, border-radius 300ms ease;
}

/* Fallback for height transitions */
.v-enter-active,
.v-leave-active {
    transition: height 300ms ease-in-out;
}
</style>
