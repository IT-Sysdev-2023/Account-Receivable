<template>
    <div v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-black/60 overflow-y-auto scrollbar-thin scrollbar-thumb-[var(--color-scrollbar-track)] scrollbar-track-[var(--color-scrollbar-track)] scrollbar-stable [scrollbar-gutter:stable] scrollbar-thumb-rounded-full">
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ConfirmationDialog :show="showDialog" message="Do you want to print the Document?"
                @close="handleConfirm" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ManagersKey :show="showManagerModal" @success="onManagerSuccess" @cancel="onManagerCancel" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <PdfPreviewModal v-if="showPdfModal" :show="showPdfModal" apiEndpoint="preview-payment"
                :formData="pdfFormData" @closeSuccess="pdfPrintSuccess" @close="showPdfModal = false" />
        </Transition>

        <transition appear @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter"
            @before-leave="beforeLeave" @leave="leave">
            <!-- Modal Container -->
            <div
                class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-7xl rounded-2xl border border-[var(--color-border)]">
                <div v-if="isExpanded" ref="formElement">
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
                                VIEW PAYMENT
                            </h2>
                            <div
                                class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent">
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-6 px-4">
                            <div class="w-full grid grid-cols-4 grid-rows-auto">
                                <div class="col-span-4 row-span-2">
                                    <div class="w-full grid sm:grid-cols-1 md:grid-cols-4 gap-x-[16px]">
                                        <div class="col-span-4">
                                            <div class="w-full grid grid-cols-3 gap-x-[16px]">
                                                <TextInput label="Payment Number" v-model="form.payment_no" type="text"
                                                    :message="form.errors.payment_no
                                                        " readonly />
                                                <TextInput label="Receipt Date" v-model="form.receipt_date" type="date"
                                                    :message="form.errors.receipt_date
                                                        " readonly />
                                                <TextInput label="Transaction Date" v-model="form.transaction_date
                                                    " type="date" :message="form.errors
                                                            .transaction_date
                                                        " readonly />
                                            </div>
                                        </div>
                                        <TextInput label="Customer Code" v-model="form.customer_code" type="text"
                                            :message="form.errors.customer_code" readonly />

                                        <TextInput label="Customer Name" v-model="form.name" type="text"
                                            :message="form.errors.name" readonly class="col-span-2" />
                                        <TextInput label="Payment Type" v-model="form.payment_type" type="select"
                                            :options="[
                                                '5A - Cash',
                                                '5B - Journal Voucher',
                                                '5C - Online Deposit',
                                                '5D - Check',
                                                '5E - Creditable(WHT)',
                                            ]" :message="form.errors.payment_type" readonly />
                                    </div>
                                </div>
                                <transition @before-enter="beforeEnter" @enter="enter" @after-enter="afterEnter"
                                    @before-leave="beforeLeave" @leave="leave">
                                    <div v-if="form.payment_type" class="col-span-4">
                                        <div class="col-span-4 row-span-1 row-start-3">
                                            <div class="w-full grid grid-cols-4 gap-x-[16px]">
                                                <TextInput label="Document Number" v-model="form.document_no"
                                                    type="textarea" rows="5" :message="form.errors.document_no
                                                        " readonly />
                                                <div class="w-full col-span-3 grid grid-cols-2 gap-x-[16px]">
                                                    <TextInput label="Type" v-model="form.type" type="text" :message="form.errors.type
                                                        " readonly />
                                                    <TextInput v-if="form.reference_no" label="Reference Number"
                                                        v-model="form.reference_no
                                                            " type="text" :message="form.errors
                                                                .reference_no
                                                            " readonly />
                                                    <TextInput v-if="form.ds_no" label="DS Number" v-model="form.ds_no"
                                                        type="text" :message="form.errors.ds_no
                                                            " readonly />

                                                    <TextInput label="Total Amount" v-model="form.total_amount
                                                        " type="text" :message="form.errors
                                                                .total_amount
                                                            " readonly />
                                                    <TextInput label="Amount Paid" v-model="form.amount_paid
                                                        " type="text" readonly :message="form.errors
                                                                .amount_paid
                                                            " />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-span-4 row-span-1 row-start-4">
                                            <div class="w-full grid grid-cols-4 gap-x-[16px]">
                                                <TextInput v-if="form.cash_in_bank" label="Cash In Bank" type="text"
                                                    v-model="form.cash_in_bank" :message="form.errors.cash_in_bank
                                                        " readonly />
                                                <TextInput v-if="form.acc_code" label="Account Code"
                                                    v-model="form.acc_code" type="text" :message="form.errors.acc_code
                                                        " readonly />
                                                <TextInput v-if="form.cust_code" label="Customer Code"
                                                    v-model="form.cust_code" type="text" :message="form.errors.cust_code
                                                        " readonly />

                                                <TextInput v-if="form.check_type" label="Check Type"
                                                    v-model="form.check_type" type="select" :options="[
                                                        'Dated Check',
                                                        'Post Dated Check',
                                                    ]" :message="form.errors.check_type
                                                        " readonly />

                                                <TextInput v-if="form.aging_basis" label="Aging Basis"
                                                    v-model="form.aging_basis" type="select" :options="[
                                                        'Receipt Date',
                                                        'SI Date',
                                                    ]" :message="form.errors.aging_basis
                                                        " readonly />

                                                <TextInput v-if="form.aging_days" label="Aging Days"
                                                    v-model="form.aging_days" type="number" :message="form.errors.aging_days
                                                        " readonly />

                                                <div v-if="
                                                    form.payment_type ===
                                                    '5E - Creditable(WHT)'
                                                " class="flex items-center gap-2 mt-8 mb-[26px]">
                                                    <div class="relative inline-block w-6 h-6">
                                                        <input type="checkbox" v-model="form.withBIR
                                                            " readonly="true" disabled="true"
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

                                                <TextInput v-if="
                                                    form.payment_type ===
                                                    '5E - Creditable(WHT)'
                                                " label="With Holding Tax" v-model="form.witholdingtax"
                                                    type="select" :options="[
                                                        '1%',
                                                        '2%',
                                                        '5%',
                                                    ]" :message="form.errors
                                                            .witholdingtax
                                                        " readonly />
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
                                                            " readonly class="col-span-3" />
                                                    <TextInput label="Referral Name" v-model="form.referral_name
                                                        " type="text" :message="form.errors
                                                                .referral_name
                                                            " readonly />
                                                    <TextInput label="Account Number" v-model="form.acc_number
                                                        " type="text" :message="form.errors
                                                                .acc_number
                                                            " readonly />
                                                    <TextInput label="Due Date" v-model="form.due_date" type="date"
                                                        :readonly="true" :message="form.errors.due_date
                                                            " readonly />
                                                </div>
                                            </transition>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-4 pt-2 border-t gap-2 border-[var(--color-border)] flex justify-between">
                            <div class="flex items-center">
                                <p class="text-xs">
                                    Transacted By :
                                    {{ props.selected.created_by }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="closeModal" class="closeButton group">
                                    <div class="flex justify-center items-center gap-2">
                                        <span class="transition-transform duration-300 group-hover:rotate-180">
                                            <svg-icon type="mdi" :path="mdiClose" class="w-5 h-5" />
                                        </span>
                                        Close
                                    </div>
                                </button>
                                <button v-if="canReprint('0203-PAYT')" type="submit" @click="openManagerModal()"
                                    class="submitButton group" :disabled="!props.selected.ds_no &&
                                        !props.selected.reference_no
                                        ">
                                    <div class="flex justify-center items-center gap-2">
                                        <span class="transition-transform duration-300 group-hover:rotate-360">
                                            <svg-icon type="mdi" :path="mdiPrinterOutline" class="w-5 h-5" />
                                        </span>
                                        <span>Reprint</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from "vue";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import ManagersKey from "../ManagersKey.vue";
import { mdiClose, mdiPrinterOutline } from "@mdi/js";
import PdfPreviewModal from "../PdfPreviewModal.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
    selected: Object,
});

const { canReprint } = usePermissions();

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
});

const pendingOldDate = ref(null);
const modalLoading = ref(false);

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

const showManagerModal = ref(false);
const person_authored = ref(null);
function openManagerModal() {
    showDialog.value = true;
}

function onManagerSuccess(selectedData) {
    showManagerModal.value = false;
    person_authored.value = selectedData.person_authored;
    showDialog.value = true;
}

function onManagerCancel() {
    showManagerModal.value = false;
}

//#region /////// PREVIEW PDF/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Preview PDF
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const previewInvoice = async () => {
    try {
        const submissionData = {
            ...form.data(),
            _reprint_confirmation: true,
            _person_authored: person_authored.value,
        };

        pdfFormData.value = submissionData;
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
    }
};
//#endregion

///////////////////////////////WATCH///////////////////////////////////////////////////////
watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            form.payment_no = props.selected.payment_no;
            form.receipt_date = props.selected.receipt_date;
            form.transaction_date = props.selected.transaction_date;
            form.customer_code = props.selected.customer_code;
            form.name = props.selected.name;
            form.payment_type = props.selected.payment_type;
            form.type = props.selected.type;
            form.reference_no = props.selected.reference_no;
            form.ds_no = props.selected.ds_no;
            form.document_no = props.selected.document_no;
            form.document_date = props.selected.document_date;
            form.total_amount = formatCurrency(props.selected.total_amount);
            form.amount_paid = formatCurrency(props.selected.amount_paid);
            form.acc_code = props.selected.acc_code;
            form.cust_code = props.selected.cust_code;
            form.cash_in_bank = props.selected.cash_in_bank;
            if (
                props.selected.withBIR === 1 ||
                props.selected.withBIR === "1"
            ) {
                form.withBIR = true;
            } else {
                form.withBIR = false;
            }
            form.witholdingtax = props.selected.witholdingtax;
            form.check_type = props.selected.check_type;
            form.aging_basis = props.selected.aging_basis;
            form.aging_days = props.selected.aging_days;
            form.acc_name_address = props.selected.acc_name_address;
            form.referral_name = props.selected.referral_name;
            form.acc_number = props.selected.acc_number;
            form.due_date = props.selected.due_date;
            modalLoading.value = false;
        }
    },
    { immediate: true }
);

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
