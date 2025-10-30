<template>
    <div v-if="show" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4">
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <ManagersKey :show="showManagerModal" @success="onManagerSuccess" @cancel="onManagerCancel" />
        </Transition>
        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <DocumentNumberListADJ v-if="showDocumentNumberListModal" :customer_code="form.customer_code"
                :apply_to="form.apply_to" @close="showDocumentNumberListModal = false"
                @submit="handleSelectedInvoices" />
        </Transition>

        <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <CustomerListModal v-if="showCustomerModal" :show="showCustomerModal" @close="showCustomerModal = false"
                @submit="handleSelectedCustomer" />
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
        <form @submit.prevent="submit"
            class="bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] w-full max-w-6xl rounded-2xl border border-[var(--color-border)]">
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
            <div v-else class="px-8 py-6">
                <!-- Header -->
                <div class="px-8 pb-4">
                    <h2 class="text-2xl font-bold text-center">
                        ADD NEW ADJUSTMENT
                    </h2>
                    <div class="mt-2 h-0.5 bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent">
                    </div>
                </div>

                <div class="flex flex-col md:flex-col gap-4 px-4">
                    <!-- Right: Form Fields -->
                    <div class="w-full grid sm:grid-cols-1 md:grid-cols-3 gap-4">
                        <TextInput label="Adjustment Number" v-model="form.adjustment_no" type="text" readonly
                            :message="form.errors.adjustment_no" />
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
                    </div>
                    <div class="w-full grid sm:grid-cols-1 md:grid-cols-4 gap-4">
                        <DropdownInput label="Type" v-model="form.type" :options="['Positive', 'Negative']"
                            :message="form.errors.type" :disabled="!form.name" placeholder="Click to Select"
                            disabledPlaceholder="Select Customer First" />
                        <DropdownInput label="Apply To" v-model="form.apply_to"
                            :options="['Sales Invoice', 'Other Income']" :message="form.errors.apply_to"
                            :disabled="!form.type" placeholder="Click to Select"
                            disabledPlaceholder="Select Type First" />
                        <TextInput label="Document Number" @click="onDocuNumberClick()" v-model="form.invoice_no"
                            type="text" :message="form.errors.invoice_no" :readonly="!form.apply_to"
                            :default-placeholder="'Click to Select'" :modified-placeholder="'Select Apply To First'"
                            selectable="yes" />
                        <TextInput label="Balance" v-model="bal" type="text" readonly :message="form.errors.balance" />
                        <DropdownInput label="Adjustment Reason" v-model="form.adjustment_reason"
                            :options="adjustmentreasonOptions" :message="form.errors.adjustment_reason"
                            :disabled="!form.invoice_no" placeholder="Click to Select"
                            disabledPlaceholder="Select Document No First" />
                        <TextInput label="Particular" v-model="form.particulars" type="text"
                            :message="form.errors.particulars" class="col-span-2" />
                        <TextInput label="Amount" v-model="form.amount" type="decimal" :message="form.errors.amount"
                            :readonly="!form.balance" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 pt-2 border-t border-[var(--color-border)] mt-4">
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
} from "vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import TextInput from "../../Pages/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import ManagersKey from "../ManagersKey.vue";
import ToastAlertWarning from "../../Pages/Components/ToastAlertWarning.vue";
import axios from "axios";
import ConfirmationDialog from "../../Pages/Components/ConfirmationDialog.vue";
import DocumentNumberListADJ from "./DocumentNumberListADJ.vue";
import CustomerListModal from "./CustomerListModal.vue";
import DropdownInput from "../../Pages/Components/DropdownInput.vue";
import { mdiClose, mdiNavigationVariantOutline } from "@mdi/js";
import PdfPreviewModal from "../PdfPreviewModal.vue";
import DatePicker from "../../Pages/Components/DatePicker.vue";
import usePermissions from "../../Pages/Composables/usePermissions";

const props = defineProps({
    show: Boolean,
});

const form = useForm({
    adjustment_no: null,
    receipt_date: null,
    transaction_date: null,
    customer_code: null,
    name: null,
    type: null,
    apply_to: null,
    invoice_no: null,
    balance: null,
    adjustment_reason: null,
    particulars: null,
    amount: null,
});

const { canPrint } = usePermissions();

const showManagerModal = ref(false);
const pendingOldDate = ref(null);
const adjustmentreasonOptions = ref([]);
const modalLoading = ref(false);
const showDocumentNumberListModal = ref(false);
const showCustomerModal = ref(false);

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

const emit = defineEmits(["close", "closeSuccess"]);

const closeModal = () => {
    emit("close");
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

//#region ////////////////TOAST///////////////////////////////////////////////////////////////////////////////////////////////////////
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
//#endregion

/////// CUSTOMER CODE DROPDOWN /////////////////////////////////////////////////////////////////////////////////////////////
function onCustomerClick() {
    showCustomerModal.value = true;
}
const handleSelectedCustomer = (selectedData) => {
    form.customer_code = selectedData.cus_code;
    form.name = selectedData.cus_name;

    showCustomerModal.value = false;
};

/////////////////////////DOCUMENT NO FETCH DATA FROM TABLE//////////////////////////////////////
const ledgerType = ref(null);
const bal = ref(null);
const handleSelectedInvoices = (selectedData) => {
    form.invoice_no = selectedData.invoiceNumber;
    bal.value = formatCurrency(selectedData.totalAmount);
    form.balance = selectedData.totalAmount;
    ledgerType.value = selectedData.type;
};

//#region /////// PREVIEW PDF/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Preview PDF
const showPdfModal = ref(false);
const pdfFormData = ref(null);
const apiRoute = ref(null);
const previewInvoice = async () => {
    try {
        apiRoute.value = "preview-adjustment";
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
//#endregion

//#region /////// WATCH //////////////////////////////////////////////////////////////////////////////////////////////////////////////
watch(
    () => props.show,
    async (visible, oldVisible) => {
        if (visible && !oldVisible) {
            modalLoading.value = true;
            form.type = "";
            form.apply_to = "";
            form.adjustment_reason = "";
            form.adjustment_no = "********";

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
    () => form.apply_to,
    async (newVal, oldVal) => {
        if (newVal && newVal !== oldVal) {
            form.invoice_no = "";
            form.balance = "";
            form.adjustment_reason = "";
            form.particulars = "";
            form.amount = "";

            if (form.apply_to === "Sales Invoice") {
                try {
                    const response = await axios.get(
                        route("getAdjustmentReasonSetup"),
                        {
                            params: {
                                apply_to: form.apply_to,
                            },
                        }
                    );
                    adjustmentreasonOptions.value = response.data;
                } catch (error) {
                    console.error(
                        "Failed to fetch adjustmen reason setup:",
                        error
                    );
                }
            } else if (form.apply_to === "Other Income") {
                try {
                    const response = await axios.get(
                        route("getAdjustmentReasonSetup"),
                        {
                            params: {
                                apply_to: form.apply_to,
                            },
                        }
                    );
                    adjustmentreasonOptions.value = response.data;
                } catch (error) {
                    console.error(
                        "Failed to fetch adjustmen reason setup:",
                        error
                    );
                }
            }
        }
    }
);

watch(
    () => form.customer_code,
    async (newVal, oldVal) => {
        if (newVal === "" || newVal !== oldVal) {
            form.type = "";
            form.apply_to = "";
            form.invoice_no = "";
            form.balance = "";
            form.adjustment_reason = "";
            form.particulars = "";
            form.amount = "";
        }
    }
);

watch(
    () => form.invoice_no,
    async (newVal, oldVal) => {
        if (newVal && newVal !== oldVal) {
            form.adjustment_reason = "";
            form.particulars = "";
            form.amount = "";
        }
    }
);

//#endregion

//////////////////////////SUBMIT////////////////////
const submit = () => {
    Object.keys(form.errors).forEach((key) => {
        form.errors[key] = "";
    });
    const submissionData = {
        ...form.data(),
        _cl_type: ledgerType.value,
    };

    form.transform((data) => submissionData).post(route("addAdjustment"), {
        onSuccess: () => {
            axios
                .get(route("adjustment.latest.adjustmentNumber"))
                .then((res) => {
                    form.adjustment_no = res.data.adjustment_number;
                    if (canPrint("0202-ADT")) {
                        showDialog.value = true;
                    } else {
                        emit("closeSuccess");
                    }
                });
        },
        onError: (errors) => {
            if (Object.keys(errors).length === 1) {
                const firstError = Object.values(errors)[0]; // get the first error message
                showWarningToast(firstError);
            } else if (Object.keys(errors).length !== 1) {
                showWarningToast("Please Fill Up Necessary Fields");
            }
            // console.log(errors); // helpful for debugging
        },
    });
};
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
</style>
